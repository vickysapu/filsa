<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Church;
use App\Models\FinancialTransaction;
use App\Models\FinancialReport;
use App\Models\FinancialReportDetail;
use App\Models\FinancialReportForward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;

class FinancialReportController extends Controller
{
    private function getReportCategories()
    {
        return [
            'PP' => ['name' => 'PERSEPULUHAN (PP)', 'group' => 'income'],
            'IURAN_PEJABAT' => ['name' => 'IURAN', 'group' => 'income'],
            'DANA_MISI' => ['name' => 'DANA MISI', 'group' => 'income'],
            'BUNGA_BANK' => ['name' => 'BUNGA', 'group' => 'income'],
            'PENERIMAAN_LAIN' => ['name' => 'LAIN-LAIN (DEBIT)', 'group' => 'income'],
            'BPJS' => ['name' => 'BPJS', 'group' => 'expense'],
            'IURAN_MDF' => ['name' => 'IURAN MDF', 'group' => 'expense'],
            'TAHAKA' => ['name' => 'TAHAKA', 'group' => 'expense'],
            'PAJAK_BUNGA' => ['name' => 'PAJAK BUNGA', 'group' => 'expense'],
            'ADMIN_BANK' => ['name' => 'ADMIN BANK', 'group' => 'expense'],
            'PENGELUARAN_LAIN' => ['name' => 'LAIN-LAIN (KREDIT)', 'group' => 'expense'],
        ];
    }

    private function getReportGroups()
    {
        return [
            'income' => [
                'title' => 'DEBIT (PEMASUKAN)',
                'cats' => ['PP', 'IURAN_PEJABAT', 'DANA_MISI', 'BUNGA_BANK', 'PENERIMAAN_LAIN'],
            ],
            'expense' => [
                'title' => 'KREDIT (PENGELUARAN)',
                'cats' => ['BPJS', 'IURAN_MDF', 'TAHAKA', 'PAJAK_BUNGA', 'ADMIN_BANK', 'PENGELUARAN_LAIN'],
            ],
        ];
    }

    private function getMdCategories(): array
    {
        return $this->getReportCategories();
    }

    private function calculateOpeningBalance($branchId, $reportType, $year, $month)
    {
        $query = FinancialReport::where('branch_id', $branchId)
            ->where('status', 'approved')
            ->where(function ($q) use ($year, $month, $reportType) {
                if ($reportType === 'annual') {
                    $q->where('report_year', '<', $year);
                } else {
                    $q->where('report_year', '<', $year)->orWhere(function ($q2) use ($year, $month) {
                        $q2->where('report_year', $year)->where('report_month', '<', $month);
                    });
                }
            });

        $lastReport = $query->orderBy('report_year', 'desc')->orderBy('report_month', 'desc')->first();

        return $lastReport ? (float) $lastReport->closing_balance : 0.0;
    }

    public function index(Request $request)
    {
        $userRole = Auth::user()?->roles->first()->name ?? '';
        $isMP = strtolower($userRole) === 'mp';
        $isMD = Str::startsWith(strtolower($userRole), 'md_');
        $myBranchId = Auth::user()?->roles->first()?->branch_id;

        $query = FinancialReport::with(['branch', 'submittedToBranch', 'approver', 'details.church'])
            ->orderBy('report_year', 'desc')
            ->orderBy('report_month', 'desc')
            ->orderBy('created_at', 'desc');

        if (!$isMP) {
            if ($isMD) {
                $childBranchIds = Branch::where('id', $myBranchId)->pluck('id')->toArray();
                $query->whereIn('branch_id', array_merge([$myBranchId], $childBranchIds));
            } else {
                $query->where('branch_id', $myBranchId);
            }
        }

        if ($request->type) {
            $query->where('report_type', $request->type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->year) {
            $query->where('report_year', $request->year);
        }
        if ($request->month) {
            $query->where('report_month', $request->month);
        }

        $pendingCount = FinancialReport::where('submitted_to', $myBranchId)->where('status', 'submitted')->count();
        $reports = $query->get();
        $groupedData = [];

        if ($isMP) {
            foreach ($reports as $report) {
                $mdId = $report->branch_id;
                $mdName = $report->branch->name ?? '-';
                $month = (int) $report->report_month;

                if ($report->report_type === 'monthly') {
                    $safeMonth = str_pad(max(1, min(12, $month)), 2, '0', STR_PAD_LEFT);
                    $periodKey = $report->report_year . '-' . $safeMonth;
                    $periodLabel = \Carbon\Carbon::create($report->report_year, $safeMonth)->format('F Y');
                } else {
                    $periodKey = $report->report_year . '-annual';
                    $periodLabel = 'Tahunan ' . $report->report_year;
                }

                if (!isset($groupedData[$mdId])) {
                    $groupedData[$mdId] = ['md_name' => $mdName, 'md_id' => $mdId, 'total_income' => 0, 'total_expense' => 0, 'total_balance' => 0, 'periods' => []];
                }
                if (!isset($groupedData[$mdId]['periods'][$periodKey])) {
                    $groupedData[$mdId]['periods'][$periodKey] = ['label' => $periodLabel, 'reports' => [], 'total_income' => 0, 'total_expense' => 0, 'total_balance' => 0, 'missing_count' => 0];
                }

                $groupedData[$mdId]['periods'][$periodKey]['reports'][] = $report;
                $groupedData[$mdId]['periods'][$periodKey]['total_income'] += (float) $report->total_income;
                $groupedData[$mdId]['periods'][$periodKey]['total_expense'] += (float) $report->total_expense;
                $groupedData[$mdId]['periods'][$periodKey]['total_balance'] += (float) $report->closing_balance;

                $groupedData[$mdId]['total_income'] += (float) $report->total_income;
                $groupedData[$mdId]['total_expense'] += (float) $report->total_expense;
                $groupedData[$mdId]['total_balance'] += (float) $report->closing_balance;
            }
        } else {
            foreach ($reports as $report) {
                $yearKey = $report->report_year;
                $month = (int) $report->report_month;

                if ($report->report_type === 'monthly') {
                    $safeMonth = str_pad(max(1, min(12, $month)), 2, '0', STR_PAD_LEFT);
                    $periodKey = $yearKey . '-' . $safeMonth;
                    $periodLabel = \Carbon\Carbon::create($yearKey, $safeMonth)->format('F Y');
                } else {
                    $periodKey = $yearKey . '-annual';
                    $periodLabel = 'Tahunan ' . $yearKey;
                }

                if (!isset($groupedData[$yearKey])) {
                    $groupedData[$yearKey] = [
                        'label' => 'Tahun ' . $yearKey,
                        'total_income' => 0,
                        'total_expense' => 0,
                        'total_balance' => 0,
                        'periods' => [],
                    ];
                }

                if (!isset($groupedData[$yearKey]['periods'][$periodKey])) {
                    $groupedData[$yearKey]['periods'][$periodKey] = [
                        'label' => $periodLabel,
                        'reports' => [],
                        'total_income' => 0,
                        'total_expense' => 0,
                        'total_balance' => 0,
                        'missing_count' => 0,
                    ];
                }

                $groupedData[$yearKey]['periods'][$periodKey]['reports'][] = $report;
                $groupedData[$yearKey]['periods'][$periodKey]['total_income'] += (float) $report->total_income;
                $groupedData[$yearKey]['periods'][$periodKey]['total_expense'] += (float) $report->total_expense;
                $groupedData[$yearKey]['periods'][$periodKey]['total_balance'] += (float) $report->closing_balance;

                $groupedData[$yearKey]['total_income'] += (float) $report->total_income;
                $groupedData[$yearKey]['total_expense'] += (float) $report->total_expense;
                $groupedData[$yearKey]['total_balance'] += (float) $report->closing_balance;
            }

            foreach ($groupedData as &$yearGroup) {
                foreach ($yearGroup['periods'] as &$period) {
                    $mCount = 0;
                    foreach ($period['reports'] as &$report) {
                        $report->missing_pp = !$report->details->contains(fn($d) => $d->category_code === 'PP' && $d->amount > 0);
                        if ($report->missing_pp) {
                            $mCount++;
                        }
                    }
                    $period['missing_count'] = $mCount;
                    unset($report);
                }
                unset($period);
            }
            unset($yearGroup);
        }

        if ($isMP) {
            foreach ($groupedData as &$group) {
                if (isset($group['periods'])) {
                    foreach ($group['periods'] as &$period) {
                        $mCount = 0;
                        foreach ($period['reports'] as &$report) {
                            $report->missing_pp = !$report->details->contains(fn($d) => $d->category_code === 'PP' && $d->amount > 0);
                            if ($report->missing_pp) {
                                $mCount++;
                            }
                        }
                        $period['missing_count'] = $mCount;
                        unset($report);
                    }
                    unset($period);
                }
            }
            unset($group);
        }

        return view('features.finance.reports.index', [
            'groupedData' => $groupedData,
            'groupedReceived' => $this->buildReceived($request, $isMP, $myBranchId),
            'isMP' => $isMP,
            'isMD' => $isMD,
            'pendingCount' => $pendingCount,
            'categories' => $this->getReportCategories(),
            'groups' => $this->getReportGroups(),
            'churches' => $this->churchOptions($myBranchId),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'report_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        ]);

        $userRole = auth()->user()->roles->first()->name ?? '';
        $isMD = Str::startsWith(strtolower($userRole), 'md_');
        $myBranchId = Auth::user()?->roles->first()?->branch_id;

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return response()->json(['success' => false, 'message' => 'File kosong atau tidak memiliki data'], 400);
        }

        $columnMap = [
            0 => 'nama_pendeta',
            1 => 'PP',
            2 => 'IURAN_PEJABAT',
            3 => 'DANA_MISI',
            4 => 'BUNGA_BANK',
            5 => 'PENERIMAAN_LAIN',
            6 => 'BPJS',
            7 => 'IURAN_MDF',
            8 => 'TAHAKA',
            9 => 'PAJAK_BUNGA',
            10 => 'ADMIN_BANK',
            11 => 'PENGELUARAN_LAIN',
            12 => 'period_start',
            13 => 'period_end',
        ];

        $categories = $this->getReportCategories();
        $groups = $this->getReportGroups();

        $skipped = [];
        $importedCount = 0;
        $errors = [];

        $dataRows = array_slice($rows, 1);

        $grouped = [];
        foreach ($dataRows as $rowIndex => $row) {
            $namaPendeta = isset($row[0]) ? trim((string) $row[0]) : '';

            if ($namaPendeta === '') {
                $skipped[] = 'Baris ' . ($rowIndex + 2) . ' dilewati karena nama pendeta kosong';
                continue;
            }

            $periodStart = isset($row[12]) ? trim((string) $row[12]) : null;
            $periodEnd = isset($row[13]) ? trim((string) $row[13]) : null;

            if (!$periodStart || !$periodEnd) {
                $skipped[] = 'Baris ' . ($rowIndex + 2) . ' ('. $namaPendeta .') dilewati karena periode tidak lengkap';
                continue;
            }

            try {
                if (is_numeric($periodStart)) {
                    $periodStart = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($periodStart)->format('Y-m-d');
                } else {
                    $periodStart = \Carbon\Carbon::parse($periodStart)->format('Y-m-d');
                }
                if (is_numeric($periodEnd)) {
                    $periodEnd = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($periodEnd)->format('Y-m-d');
                } else {
                    $periodEnd = \Carbon\Carbon::parse($periodEnd)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $skipped[] = 'Baris ' . ($rowIndex + 2) . ' ('. $namaPendeta .') dilewati karena format tanggal tidak valid';
                continue;
            }

            $month = (int) \Carbon\Carbon::parse($periodStart)->format('m');
            $year = (int) \Carbon\Carbon::parse($periodStart)->format('Y');
            $periodKey = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

            if (!isset($grouped[$periodKey])) {
                $grouped[$periodKey] = [
                    'year' => $year,
                    'month' => $month,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'rows' => [],
                ];
            }

            $detailRow = ['nama_pendeta' => $namaPendeta, 'categories' => []];
            foreach ($columnMap as $colIndex => $key) {
                if (in_array($key, ['nama_pendeta', 'period_start', 'period_end'])) {
                    continue;
                }
                $detailRow['categories'][$key] = $this->normalizeNumber($row[$colIndex] ?? 0);
            }
            $grouped[$periodKey]['rows'][] = $detailRow;
        }

        DB::beginTransaction();
        try {
            foreach ($grouped as $periodKey => $periodData) {
                $year = $periodData['year'];
                $month = $periodData['month'];
                $periodStart = $periodData['period_start'];
                $periodEnd = $periodData['period_end'];

                $existing = FinancialReport::where('branch_id', $myBranchId)
                    ->where('report_type', 'monthly')
                    ->where('report_year', $year)
                    ->where('report_month', $month)
                    ->first();

                if ($existing && $existing->status !== 'draft') {
                    $skipped[] = 'Periode ' . $periodKey . ' dilewati karena laporan sudah dikirim/disetujui';
                    continue;
                }

                $openingBalance = $this->calculateOpeningBalance($myBranchId, 'monthly', $year, $month);

                if ($existing) {
                    $report = $existing;
                    $report->details()->delete();
                } else {
                    $report = FinancialReport::create([
                        'branch_id' => $myBranchId,
                        'report_type' => 'monthly',
                        'report_year' => $year,
                        'report_month' => $month,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'opening_balance' => $openingBalance,
                        'status' => 'draft',
                        'created_by' => Auth::id(),
                    ]);
                }

                foreach ($periodData['rows'] as $rowData) {
                    $namaPendeta = $rowData['nama_pendeta'];
                    foreach ($rowData['categories'] as $categoryCode => $amount) {
                        $catInfo = $categories[$categoryCode] ?? null;
                        if (!$catInfo) {
                            continue;
                        }
                        FinancialReportDetail::create([
                            'financial_report_id' => $report->id,
                            'church_id' => null,
                            'category_code' => $categoryCode,
                            'category_name' => $catInfo['name'] . ' - ' . $namaPendeta,
                            'category_group' => $catInfo['group'],
                            'type' => $catInfo['group'] === 'income' ? 'income' : 'expense',
                            'amount' => $amount,
                            'notes' => $namaPendeta,
                        ]);
                    }
                }

                $totalIncome = $report->details()->whereIn('category_code', $groups['income']['cats'])->sum('amount');
                $totalExpense = $report->details()->whereIn('category_code', $groups['expense']['cats'])->sum('amount');

                $report->update([
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'closing_balance' => $openingBalance + $totalIncome - $totalExpense,
                    'updated_by' => Auth::id(),
                ]);

                $importedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $importedCount . ' periode laporan berhasil diimport',
                'imported_count' => $importedCount,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal import: ' . $e->getMessage()], 500);
        }
    }

    // ================= IMPORT EXCEL (WIZARD) =================

    // 1. Unggah + analisis semua sheet
    public function importAnalyze(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:20480']);

        $file = $request->file('file');
        $token = (string) Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $rel = 'imports/' . Auth::id() . '/' . $token;
        Storage::disk('local')->putFileAs('imports/' . Auth::id(), $file, $token);
        $path = Storage::disk('local')->path($rel);

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $book = $reader->load($path);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($rel);
            return response()->json(['success' => false, 'message' => 'File tidak bisa dibaca: ' . $e->getMessage()], 422);
        }

        $sheets = [];
        foreach ($book->getAllSheets() as $ws) {
            $rows = $this->trimRows($this->sheetToRows($ws));
            [$gRow, $sRow] = $this->guessHeader($rows);
            $sheets[] = [
                'name' => $ws->getTitle(),
                'hidden' => $ws->getSheetState() !== \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VISIBLE,
                'empty' => count($rows) < 3,
                'rows' => count($rows),
                'group_row' => $gRow,
                'sub_row' => $sRow,
                'first_row' => $sRow + 1,
                'last_row' => count($rows),
                'columns' => $this->buildColumns($rows, $gRow, $sRow),
                'period' => $this->guessPeriod($ws->getTitle()),
            ];
        }
        $book->disconnectWorksheets();
        unset($book);

        return response()->json([
            'success' => true,
            'token' => $token,
            'targets' => $this->importTargets(),
            'date_column' => Schema::hasColumn('financial_report_details', 'transaction_date'),
            'sheets' => $sheets,
        ]);
    }

    // Deteksi ulang kolom bila baris header diubah
    public function importColumns(Request $request)
    {
        $d = $request->validate([
            'token' => 'required|string',
            'sheet' => 'required|string',
            'group_row' => 'required|integer|min:1',
            'sub_row' => 'required|integer|min:1',
        ]);
        $rows = $this->loadSheetRows($d['token'], $d['sheet']);

        return response()->json(['success' => true, 'columns' => $this->buildColumns($rows, $d['group_row'], $d['sub_row'])]);
    }

    // 2. Pengecekan satu sheet (dipanggil berurutan oleh wizard)
    public function importPreview(Request $request)
    {
        $d = $request->validate([
            'token' => 'required|string',
            'sheet' => 'required|array',
            'sheet.name' => 'required|string',
            'opts' => 'nullable|array',
        ]);
    
        $branchId = $this->importBranchId($request);
        $cfg = $request->input('sheet');
        $opts = $request->input('opts') ?? [];
        $cats = $this->getReportCategories();
    
        $alias = [];
        foreach ($request->input('all_sheets', []) as $o) {
            if (empty($o['name'])) continue;
            foreach ($this->sheetAlias($this->loadSheetRows($d['token'], $o['name']), $o) as $k => $v) {
                $alias[$k] ??= $v;
            }
        }
        $parsed = $this->parseSheetRows($this->loadSheetRows($d['token'], $cfg['name']), $cfg, $opts, $alias);
        $names = $this->summarizeNames($parsed, $this->churchIndex($branchId));
    
        $used = 0;
        $income = 0;
        $expense = 0;
        foreach ($parsed as $p) {
            if ($p['skip']) {
                continue;
            }
            $used++;
            foreach ($p['amounts'] as $a) {
                if (($cats[$a['code']]['group'] ?? '') === 'income') {
                    $income += $a['amount'];
                } else {
                    $expense += $a['amount'];
                }
            }
        }
    
        // Satu periode bisa punya banyak laporan (satu per gereja)
        $existing = FinancialReport::where('branch_id', $branchId)
            ->where('report_type', 'monthly')
            ->where('report_year', (int) ($cfg['period']['year'] ?? 0))
            ->where('report_month', (int) ($cfg['period']['month'] ?? 0))
            ->get();
        $will = $existing->isEmpty()
            ? 'create'
            : ($existing->every(fn($r) => $r->status !== 'draft') ? 'skip' : 'replace');
    
        return response()->json([
            'success' => true,
            'data' => [
                'rows' => array_map(fn($p) => [
                    'row' => $p['row'],
                    'date' => $p['date'],
                    'raw' => $p['raw'],
                    'who' => $p['who'],
                    'skip' => $p['skip'],
                    'manual' => $p['manual'],
                    'amounts' => implode(', ', array_map(
                        fn($a) => ($cats[$a['code']]['name'] ?? $a['code']) . ': ' . number_format($a['amount'], 0, ',', '.'),
                        $p['amounts']
                    )),
                ], array_slice($parsed, 0, 600)),
                'names' => $names,
                'summary' => ['used' => $used, 'skipped' => count($parsed) - $used, 'income' => $income, 'expense' => $expense],
                'report' => ['will' => $will],
            ],
        ]);
    }

    // 3. Import semua sheet sekaligus (satu transaksi)
    public function importCommit(Request $request)
    {
        $d = $request->validate([
            'token' => 'required|string',
            'opts' => 'nullable|array',
            'decisions' => 'nullable|array',
            'sheets' => 'required|array|min:1',
            'sheets.*.name' => 'required|string',
            'sheets.*.period.year' => 'required|integer|min:2000|max:2100',
            'sheets.*.period.month' => 'required|integer|min:1|max:12',
        ]);
    
        $branchId = $this->importBranchId($request);
        $opts = $request->input('opts') ?? [];
        $decisions = $request->input('decisions') ?? [];
        $index = $this->churchIndex($branchId);
        $nameById = collect($index)->pluck('name', 'id')->all();
        $cats = $this->getReportCategories();
        $groups = $this->getReportGroups();
        $hasDate = Schema::hasColumn('financial_report_details', 'transaction_date');
    
        // a. Baca semua sheet dulu (belum menulis apa pun).
        //    Alias dibuat dari SELURUH sheet: pendeta yang di satu baris punya kolom Gereja
        //    dipakai juga untuk barisnya yang kolom Gereja-nya kosong.
        $loaded = [];
        $alias = [];
        foreach ($request->input('sheets') as $i => $cfg) {
            $loaded[$i] = $this->loadSheetRows($d['token'], $cfg['name']);
            foreach ($this->sheetAlias($loaded[$i], $cfg) as $k => $v) {
                $alias[$k] ??= $v;
            }
        }
    
        $plans = [];
        $names = [];
        foreach ($request->input('sheets') as $i => $cfg) {
            $parsed = $this->parseSheetRows($loaded[$i], $cfg, $opts, $alias);
            $plans[] = ['cfg' => $cfg, 'parsed' => $parsed];
            foreach ($this->summarizeNames($parsed, $index) as $n) {
                if (isset($names[$n['key']])) {
                    $names[$n['key']]['variants'] = array_values(array_unique(array_merge($names[$n['key']]['variants'], $n['variants'])));
                    $names[$n['key']]['pic'] ??= $n['pic'];
                } else {
                    $names[$n['key']] = $n;
                }
            }
        }
        unset($loaded);
    
        // b. Tentukan tindakan tiap nama (pakai yang lama / buat baru / tanpa gereja)
        $resolution = [];
        $unresolved = [];
        foreach ($names as $key => $n) {
            $dec = $decisions[$key] ?? [];
            $action = $dec['action'] ?? null;
    
            if (!$action) {
                $match = $this->matchChurch($key, $n['variants'], $index);
                if ($match['status'] === 'new') {
                    $action = 'new';
                    $dec = ['type' => 'church'];
                } else {
                    $action = 'use';
                    $dec = ['church_id' => $match['candidates'][0]['id']];
                }
            }
            if (!in_array($action, ['use', 'new', 'skip'], true) || ($action === 'use' && !isset($nameById[(string) ($dec['church_id'] ?? '')]))) {
                $unresolved[] = $n['name'];
                continue;
            }
            $resolution[$key] = [
                'action' => $action,
                'church_id' => (string) ($dec['church_id'] ?? ''),
                'type' => ($dec['type'] ?? 'church') === 'item' ? 'item' : 'church',
                'name' => $n['name'],
                'pic' => $n['pic'] ?? null,
            ];
        }
        if ($unresolved) {
            return response()->json(['success' => false, 'message' => 'Konfirmasi belum lengkap untuk: ' . implode(', ', array_slice($unresolved, 0, 10))], 422);
        }
    
        // c. Tulis ke database
        $created = [];
        $skipped = [];
        $seen = [];
        $reportCount = 0;
        $detailCount = 0;
    
        DB::beginTransaction();
        try {
            foreach ($plans as $pl) {
                $cfg = $pl['cfg'];
                $y = (int) $cfg['period']['year'];
                $m = (int) $cfg['period']['month'];
    
                if (isset($seen["$y-$m"])) {
                    $skipped[] = "Sheet \"{$cfg['name']}\" dilewati: periode $m/$y sudah diimport dari sheet lain";
                    continue;
                }
                $seen["$y-$m"] = true;
    
                // Kelompokkan baris per gereja
                $buckets = [];
                foreach ($pl['parsed'] as $p) {
                    if ($p['skip'] || !$p['amounts']) {
                        continue;
                    }
                    $churchId = null;
                    $label = $p['raw'];
                    if ($p['key'] && isset($resolution[$p['key']])) {
                        $churchId = $this->resolveImportChurch($p['key'], $resolution[$p['key']], $branchId, $created);
                        if ($churchId) {
                            $label = $nameById[$churchId] ?? $resolution[$p['key']]['name'];
                        }
                    }
                    $bk = $churchId ?? '_none';
                    $buckets[$bk]['church_id'] = $churchId;
                    $buckets[$bk]['rows'][] = ['p' => $p, 'label' => $label];
                }
    
                if (!$buckets) {
                    $skipped[] = "Sheet \"{$cfg['name']}\" dilewati: tidak ada baris yang bisa diimport";
                    continue;
                }
    
                // Saldo awal (dari laporan disetujui sebelumnya) dipasang di satu laporan saja
                $opening = $this->calculateOpeningBalance($branchId, 'monthly', $y, $m);
                $openingKey = isset($buckets['_none']) ? '_none' : array_key_first($buckets);
                $start = Carbon::create($y, $m, 1);
    
                foreach ($buckets as $bk => $bucket) {
                    $churchId = $bucket['church_id'];
                    $open = $bk === $openingKey ? $opening : 0;
    
                    $existing = FinancialReport::where('branch_id', $branchId)
                        ->where('report_type', 'monthly')
                        ->where('report_year', $y)
                        ->where('report_month', $m)
                        ->whereHas('details', function ($q) use ($churchId) {
                            $churchId ? $q->where('church_id', $churchId) : $q->whereNull('church_id');
                        })
                        ->first();
    
                    if ($existing && $existing->status !== 'draft') {
                        $skipped[] = "Sheet \"{$cfg['name']}\": laporan $m/$y untuk "
                            . ($churchId ? ($nameById[$churchId] ?? $churchId) : 'baris tanpa gereja')
                            . ' dilewati (sudah dikirim/disetujui)';
                        continue;
                    }
    
                    if ($existing) {
                        $report = $existing;
                        $report->details()->delete();
                    } else {
                        $report = FinancialReport::create([
                            'branch_id' => $branchId,
                            'report_type' => 'monthly',
                            'report_year' => $y,
                            'report_month' => $m,
                            'period_start' => $start->format('Y-m-d'),
                            'period_end' => $start->copy()->endOfMonth()->format('Y-m-d'),
                            'opening_balance' => $open,
                            'status' => 'draft',
                            'created_by' => Auth::id(),
                        ]);
                    }
    
                    foreach ($bucket['rows'] as $r) {
                        $p = $r['p'];
                        foreach ($p['amounts'] as $a) {
                            $info = $cats[$a['code']];
                            $attrs = [
                                'financial_report_id' => $report->id,
                                'church_id' => $churchId,
                                'category_code' => $a['code'],
                                'category_name' => mb_substr($info['name'] . ($r['label'] !== '' ? ' - ' . $r['label'] : ''), 0, 250),
                                'category_group' => $info['group'],
                                'type' => $info['group'] === 'income' ? 'income' : 'expense',
                                'amount' => $a['amount'],
                                'notes' => mb_substr($p['notes'], 0, 500),
                            ];
                            if ($hasDate) {
                                $attrs['transaction_date'] = $p['date'];
                            }
                            FinancialReportDetail::create($attrs);
                            $detailCount++;
                        }
                    }
    
                    $totalIncome = $report->details()->whereIn('category_code', $groups['income']['cats'])->sum('amount');
                    $totalExpense = $report->details()->whereIn('category_code', $groups['expense']['cats'])->sum('amount');
                    $report->update([
                        'opening_balance' => $open,
                        'total_income' => $totalIncome,
                        'total_expense' => $totalExpense,
                        'closing_balance' => $open + $totalIncome - $totalExpense,
                        'updated_by' => Auth::id(),
                    ]);
                    $reportCount++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal import: ' . $e->getMessage()], 500);
        }
    
        Storage::disk('local')->delete('imports/' . Auth::id() . '/' . $d['token']);
    
        return response()->json([
            'success' => true,
            'message' => "$reportCount laporan diimport ($detailCount rincian), " . count($created) . ' gereja/item baru dibuat',
            'skipped' => $skipped,
        ]);
    }

    // ---------- Helper import ----------

    private function importBranchId(Request $request)
    {
        $role = strtolower(Auth::user()?->roles->first()->name ?? '');
        $mine = Auth::user()?->roles->first()?->branch_id;
        if ($role === 'mp' && $request->filled('branch_id')) {
            return Branch::where('id', $request->branch_id)->value('id') ?? $mine;
        }
        return $mine;
    }

    private function importTargets(): array
    {
        $t = [
            ['value' => 'ignore', 'label' => '— Abaikan kolom ini —', 'group' => 'Umum'],
            ['value' => 'date', 'label' => 'Tanggal', 'group' => 'Umum'],
            ['value' => 'name', 'label' => 'Uraian (keterangan transaksi)', 'group' => 'Umum'],
            ['value' => 'pic', 'label' => 'Penanggung Jawab / Pendeta', 'group' => 'Umum'],
            ['value' => 'church', 'label' => 'Gereja', 'group' => 'Umum'],
            ['value' => 'notes', 'label' => 'Catatan tambahan', 'group' => 'Umum'],
        ];
        foreach ($this->getReportCategories() as $code => $c) {
            $t[] = ['value' => $code, 'label' => $c['name'], 'group' => $c['group'] === 'income' ? 'Pemasukan (Debit)' : 'Pengeluaran (Kredit)'];
        }
        return $t;
    }

    private function importFilePath(string $token): string
    {
        if (!preg_match('/^[a-f0-9\-]{36}\.(xlsx|xls)$/', $token)) {
            abort(422, 'Token file tidak valid');
        }
        $rel = 'imports/' . Auth::id() . '/' . $token;
        if (!Storage::disk('local')->exists($rel)) {
            abort(422, 'File import kadaluarsa, silakan unggah ulang');
        }
        return Storage::disk('local')->path($rel);
    }

    private function loadSheetRows(string $token, string $sheetName): array
    {
        $path = $this->importFilePath($token);
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);
        $book = $reader->load($path);
        $ws = $book->getSheetByName($sheetName) ?? $book->getActiveSheet();
        $rows = $this->trimRows($this->sheetToRows($ws));
        $book->disconnectWorksheets();
        return $rows;
    }

    private function sheetToRows($ws): array
    {
        try {
            return $ws->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            return $ws->toArray(null, false, false, false);
        }
    }

    private function trimRows(array $rows): array
    {
        while ($rows && count(array_filter(end($rows), fn($v) => $v !== null && $v !== '')) === 0) {
            array_pop($rows);
        }
        return $rows;
    }

    private function csv(string $s): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $s)), fn($x) => $x !== ''));
    }

    // Cari baris header: baris yang berisi "Uraian"; baris di bawahnya = sub-header bila berisi >= 3 teks
    private function guessHeader(array $rows): array
    {
        $textCount = fn($row) => count(array_filter($row, fn($v) => is_string($v) && trim($v) !== ''));
        $g = null;
        foreach (array_slice($rows, 0, 25, true) as $i => $row) {
            foreach ($row as $cell) {
                if (is_string($cell) && preg_match('/^\s*(uraian|keterangan)\s*$/i', $cell)) {
                    $g = $i + 1;
                    break 2;
                }
            }
        }
        if ($g === null) {
            foreach (array_slice($rows, 0, 25, true) as $i => $row) {
                if ($textCount($row) >= 3) {
                    $g = $i + 1;
                    break;
                }
            }
        }
        $g ??= 1;
        $s = (isset($rows[$g]) && $textCount($rows[$g]) >= 3) ? $g + 1 : $g;
        return [$g, $s];
    }

    // Label kolom = sub-header (bila ada) + grup (Debit/Kredit) yang diteruskan dari sel yang di-merge
    private function buildColumns(array $rows, int $gRow, int $sRow): array
    {
        $g = $rows[$gRow - 1] ?? [];
        $s = $sRow !== $gRow ? ($rows[$sRow - 1] ?? []) : [];
        $n = max(count($g), count($s));
        $last = '';
        $cols = [];
        for ($c = 0; $c < $n; $c++) {
            $gv = trim((string) ($g[$c] ?? ''));
            $sv = trim((string) ($s[$c] ?? ''));
            if ($gv !== '') {
                $last = $gv;
            }
            $label = $sv !== '' ? $sv : $gv;
            $group = $sv !== '' ? $last : '';

            $sample = [];
            for ($r = $sRow; $r < count($rows) && count($sample) < 3; $r++) {
                $v = $rows[$r][$c] ?? null;
                if ($v !== null && $v !== '') {
                    $sample[] = mb_substr(is_float($v) ? (string) round($v, 2) : (string) $v, 0, 40);
                }
            }
            if ($label === '' && !$sample) {
                continue;
            }
            $cols[] = [
                'index' => $c,
                'letter' => Coordinate::stringFromColumnIndex($c + 1),
                'group' => $group,
                'label' => $label,
                'sample' => $sample,
                'target' => $this->guessTarget($group, $label),
            ];
        }
        return $cols;
    }

    private function guessTarget(string $group, string $label): string
    {
        $g = mb_strtolower($group);
        $l = mb_strtolower(trim($label));
        if ($l === '') return 'ignore';
        if (preg_match('/tanggal|tgl/', $l)) return 'date';
        if (preg_match('/penanggung|gembala|pendeta|\bpic\b/', $l)) return 'pic';
        if (preg_match('/^gereja$|jemaat/', $l)) return 'church';
        if (preg_match('/uraian|keterangan|nama/', $l)) return 'name';
        if (str_contains($l, 'saldo')) return 'ignore';
        $credit = str_contains($g, 'kredit') || str_contains($g, 'keluar');
    
        if (str_contains($l, 'iuran') && str_contains($l, 'mdf')) return 'IURAN_MDF';
        if (preg_match('/^pp$|persepuluhan/', $l)) return 'PP';
        if (str_contains($l, 'iuran')) return 'IURAN_PEJABAT';
        if (str_contains($l, 'misi')) return 'DANA_MISI';
        if (str_contains($l, 'pajak')) return 'PAJAK_BUNGA';
        if (str_contains($l, 'bunga')) return 'BUNGA_BANK';
        if (str_contains($l, 'bpjs')) return 'BPJS';
        if (str_contains($l, 'tahaka')) return 'TAHAKA';
        if (str_contains($l, 'admin')) return 'ADMIN_BANK';
        if (str_contains($l, 'lain')) return $credit ? 'PENGELUARAN_LAIN' : 'PENERIMAAN_LAIN';
        return 'ignore';
    }

    // "Agustus 2022", "Mar-23", dst. -> bulan & tahun
    private function guessPeriod(string $name): ?array
    {
        $map = ['jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'mei' => 5, 'may' => 5, 'jun' => 6, 'jul' => 7, 'agu' => 8, 'aug' => 8, 'sep' => 9, 'okt' => 10, 'oct' => 10, 'nov' => 11, 'des' => 12, 'dec' => 12];
        if (preg_match('/\b(jan|feb|mar|apr|mei|may|jun|jul|agu|aug|sep|okt|oct|nov|des|dec)[a-z]*\.?[\s\-_\/]*(\d{4}|\d{2})\b/i', $name, $m)) {
            $y = (int) $m[2];
            return ['month' => $map[strtolower($m[1])], 'year' => $y < 100 ? $y + 2000 : $y];
        }
        return null;
    }

    private function mappingCols(array $cfg): array
    {
        $cats = $this->getReportCategories();
        $c = ['date' => null, 'name' => null, 'pic' => null, 'church' => null, 'notes' => null, 'amount' => []];
        foreach (($cfg['mapping'] ?? []) as $idx => $target) {
            $idx = (int) $idx;
            if (in_array($target, ['date', 'name', 'pic', 'church', 'notes'], true)) {
                $c[$target] ??= $idx;
            } elseif (isset($cats[$target])) {
                $c['amount'][$idx] = $target;
            }
        }
        return $c;
    }
    
    private function cellText($row, ?int $col): string
    {
        return $col === null ? '' : trim(preg_replace('/\s+/u', ' ', (string) ($row[$col] ?? '')));
    }
    
    // Penanggung Jawab -> Gereja, dari baris yang dua-duanya terisi
    private function sheetAlias(array $rows, array $cfg): array
    {
        $c = $this->mappingCols($cfg);
        if ($c['pic'] === null || $c['church'] === null) {
            return [];
        }
        $first = max(1, (int) ($cfg['first_row'] ?? 1));
        $lastCfg = (int) ($cfg['last_row'] ?? 0);
        $last = $lastCfg > 0 ? min($lastCfg, count($rows)) : count($rows);
    
        $alias = [];
        for ($r = $first; $r <= $last; $r++) {
            $row = $rows[$r - 1] ?? [];
            $pic = $this->cellText($row, $c['pic']);
            $church = $this->cellText($row, $c['church']);
            if ($pic !== '' && $church !== '') {
                $alias[$this->normName($pic)] ??= $church;
            }
        }
        return $alias;
    }
    
    private function parseSheetRows(array $rows, array $cfg, array $opts, array $extraAlias = []): array
    {
        $c = $this->mappingCols($cfg);
        $alias = $this->sheetAlias($rows, $cfg) + $extraAlias;
    
        $first = max(1, (int) ($cfg['first_row'] ?? 1));
        $lastCfg = (int) ($cfg['last_row'] ?? 0);
        $last = $lastCfg > 0 ? min($lastCfg, count($rows)) : count($rows);
        $exclude = array_flip(array_map('intval', $cfg['exclude_rows'] ?? []));
        $keywords = $this->csv(mb_strtolower((string) ($opts['skip_keywords'] ?? '')));
        $py = (int) ($cfg['period']['year'] ?? 0);
        $pm = (int) ($cfg['period']['month'] ?? 0);
    
        $out = [];
        $lastDate = null; // tanggal terakhir yang terlihat (untuk baris yang tanggalnya kosong)
    
        for ($r = $first; $r <= $last; $r++) {
            $row = $rows[$r - 1] ?? [];
            $raw = $this->cellText($row, $c['name']);          // Uraian
            $pic = $this->cellText($row, $c['pic']);           // Penanggung Jawab
            $church = $this->cellText($row, $c['church']);     // Gereja
    
            $amounts = [];
            foreach ($c['amount'] as $ci => $code) {
                $v = $this->cellAmount($row[$ci] ?? null);
                if ($v != 0.0) {
                    $amounts[] = ['code' => $code, 'amount' => $v];
                }
            }
            $dateRaw = $c['date'] !== null ? ($row[$c['date']] ?? null) : null;
            $hasDate = $dateRaw !== null && trim((string) $dateRaw) !== '';
    
            if ($raw === '' && $pic === '' && $church === '' && !$amounts && !$hasDate) {
                continue; // baris kosong
            }
    
            $date = $this->resolveRowDate($dateRaw, $py, $pm, $opts, $lastDate);
    
            $skip = null;
            if (isset($exclude[$r])) {
                $skip = 'Dikecualikan manual';
            } elseif ($raw === '' && $pic === '' && $church === '') {
                $skip = !$hasDate ? 'Tanpa uraian dan tanggal (mis. baris total)' : null;
            } else {
                $low = mb_strtolower($raw);
                foreach ($keywords as $k) {
                    if (str_contains($low, $k)) {
                        $skip = 'Mengandung "' . $k . '"';
                        break;
                    }
                }
            }
            if (!$skip && !$amounts) {
                $skip = 'Tidak ada nilai di kolom yang dipetakan';
            }
    
            // Gereja/pendeta: kolom Gereja dulu; kalau kosong pakai alias dari Penanggung Jawab
            $name = $key = $picName = $churchName = null;
            if (!$skip) {
                $picKey = $this->normName($pic);
                $churchName = $church !== '' ? $church : ($picKey !== '' ? ($alias[$picKey] ?? null) : null);
                if ($churchName !== null) {
                    $name = $churchName;
                    $picName = $pic !== '' ? $pic : null;
                } elseif ($pic !== '') {
                    $name = $pic;
                }
                if ($name !== null) {
                    $name = mb_substr($name, 0, 255);
                    $key = $this->normName($name);
                    if ($key === '') {
                        $key = 'raw' . md5($name);
                    }
                }
            }
    
            $extra = $this->cellText($row, $c['notes']);
            $out[] = [
                'row' => $r,
                'raw' => $raw,
                'date' => $date,
                'name' => $name,
                'pic_name' => $picName,
                'key' => $key,
                'who' => $name !== null ? ($picName ? $name . ' / ' . $picName : $name) : '',
                'amounts' => $amounts,
                'notes' => implode(' | ', array_filter([$raw, $pic, $extra], fn($x) => $x !== '')),
                'skip' => $skip,
                'manual' => isset($exclude[$r]),
            ];
        }
        return $out;
    }

    private function extractName(string $raw, array $prefixes, bool $stripParens): string
    {
        $t = ltrim($raw, "( \t");
        foreach ($prefixes as $p) {
            if (preg_match('/^' . preg_quote($p, '/') . '(?=[\s.:\-]|$)[\s.:\-]*/iu', $t, $m)) {
                $t = trim(mb_substr($t, mb_strlen($m[0])));
                break;
            }
        }
        if ($stripParens) {
            $t = preg_replace('/\s*\([^()]*\)\s*$/u', '', $t);
        }
        $t = trim($t, " -:.");
        return mb_substr($t !== '' ? $t : $raw, 0, 255);
    }

    // Huruf kecil + buang spasi & tanda baca -> dasar penentuan "mirip"
    private function normName(string $s): string
    {
        return preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($s));
    }

    private function cellAmount($v): float
    {
        if ($v === null || $v === '') return 0.0;
        if (is_int($v) || is_float($v)) return round((float) $v, 2);
        $s = trim((string) $v);
        if ($s === '' || $s[0] === '=' || $s[0] === '#') return 0.0;
        return $this->normalizeNumber($s);
    }

    private function parseCellDate($v, int $py, int $pm): ?Carbon
    {
        if ($v === null || $v === '') return null;
        try {
            if (is_numeric($v)) {
                $n = (float) $v;
                if ($n >= 1 && $n <= 31 && floor($n) == $n) { // hanya angka hari
                    return ($py && $pm) ? Carbon::create($py, $pm, min((int) $n, Carbon::create($py, $pm, 1)->daysInMonth)) : null;
                }
                return $n > 31 ? Carbon::instance(XlDate::excelToDateTimeObject($n)) : null;
            }
            $map = ['januari' => 'January', 'februari' => 'February', 'maret' => 'March', 'mei' => 'May', 'juni' => 'June', 'juli' => 'July', 'agustus' => 'August', 'oktober' => 'October', 'desember' => 'December', 'agu' => 'Aug', 'agt' => 'Aug', 'okt' => 'Oct', 'des' => 'Dec'];
            $s = preg_replace_callback('/[A-Za-z]+/', fn($m) => $map[strtolower($m[0])] ?? $m[0], trim((string) $v));
            return Carbon::parse($s);
        } catch (\Throwable $e) {
            return null;
        }
    }

    // Tanggal baris: pakai tanggal sel (tahun bisa dipaksa = tahun periode); kosong -> tanggal bawaan; kosong semua -> null
    private function resolveRowDate($raw, int $py, int $pm, array $opts, ?string &$last): ?string
    {
        $d = $this->parseCellDate($raw, $py, $pm);
        if ($d) {
            if (!empty($opts['force_year']) && $py) {
                $d = Carbon::create($py, $d->month, min($d->day, Carbon::create($py, $d->month, 1)->daysInMonth));
            }
            return $last = $d->format('Y-m-d');
        }
        if (($opts['carry_date'] ?? true) && $last) {
            return $last;
        }
        $bd = $opts['blank_day'] ?? null;
        if ($bd !== null && $bd !== '' && $py && $pm) {
            return Carbon::create($py, $pm, min(max(1, (int) $bd), Carbon::create($py, $pm, 1)->daysInMonth))->format('Y-m-d');
        }
        return null;
    }

    private function churchIndex($branchId): array
    {
        return Church::where('branch_id', $branchId)->get(['id', 'name', 'pic_name'])->map(function ($c) {
            $isItem = str_starts_with((string) $c->pic_name, 'item::');
            $keys = array_values(array_unique(array_filter(
                [$this->normName((string) $c->name), $isItem ? '' : $this->normName((string) $c->pic_name)],
                fn($k) => $k !== ''
            )));
            return ['id' => (string) $c->id, 'name' => $c->name, 'pic' => $isItem ? null : ($c->pic_name ?: null), 'type' => $isItem ? 'item' : 'church', 'keys' => $keys];
        })->all();
    }

    // exact = tulisan sama persis; similar = beda huruf besar/kecil/spasi/tanda baca; new = belum ada
    private function matchChurch(string $key, array $variants, array $index): array
    {
        $exact = [];
        $similar = [];
        foreach ($index as $c) {
            if (!in_array($key, $c['keys'], true)) {
                continue;
            }
            $row = ['id' => $c['id'], 'name' => $c['name'], 'type' => $c['type']];
            if (in_array($c['name'], $variants, true) || ($c['pic'] !== null && in_array($c['pic'], $variants, true))) {
                $exact[] = $row;
            } else {
                $similar[] = $row;
            }
        }
        if ($exact) return ['status' => 'exact', 'candidates' => [$exact[0]]];
        if ($similar) return ['status' => 'similar', 'candidates' => $similar];
        return ['status' => 'new', 'candidates' => []];
    }

    private function summarizeNames(array $parsed, array $index): array
    {
        $g = [];
        foreach ($parsed as $p) {
            if ($p['skip'] || !$p['key']) {
                continue;
            }
            $k = $p['key'];
            $g[$k] ??= ['key' => $k, 'name' => $p['name'], 'pic' => null, 'variants' => [], 'count' => 0];
            $g[$k]['count']++;
            $g[$k]['pic'] ??= $p['pic_name'];
            if (!in_array($p['name'], $g[$k]['variants'], true)) {
                $g[$k]['variants'][] = $p['name'];
            }
        }
        foreach ($g as &$n) {
            $m = $this->matchChurch($n['key'], $n['variants'], $index);
            if ($m['status'] === 'new' && $n['pic']) {
                $m2 = $this->matchChurch($this->normName($n['pic']), [$n['pic']], $index);
                if ($m2['status'] !== 'new') {
                    $m = ['status' => 'similar', 'candidates' => $m2['candidates']];
                }
            }
            $n['status'] = $m['status'];
            $n['candidates'] = $m['candidates'];
        }
        unset($n);
        return array_values($g);
    }
    
    private function resolveImportChurch(string $key, array $res, $branchId, array &$created): ?string
    {
        if ($res['action'] === 'skip') return null;
        if ($res['action'] === 'use') return $res['church_id'];
        if (isset($created[$key])) return $created[$key];
    
        $pic = trim((string) ($res['pic'] ?? ''));
        $church = Church::create([
            'branch_id' => $branchId,
            'name' => $res['name'],
            'pic_name' => $res['type'] === 'item' ? 'item::' : ($pic !== '' ? $pic : null),
            'is_active' => true,
        ]);
        return $created[$key] = (string) $church->id;
    }

    public function create(Request $request)
    {
        return redirect()->route('admin.finance.reports.index');
    }

    public function storeReport(Request $request)
    {
        $userRole = auth()->user()->roles->first()->name ?? '';
        $isMD = Str::startsWith(strtolower($userRole), 'md_');

        $rules = [
            'report_type' => 'required|in:monthly,annual',
            'report_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'report_month' => 'required_if:report_type,monthly|nullable|integer|min:1|max:12',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'details' => 'nullable|array',
            'details.*.category_code' => 'required|string',
            'details.*.amount' => 'nullable|numeric|min:0',
            'details.*.notes' => 'nullable|string|max:500',
        ];

        if (!$isMD) {
            $rules['branch_id'] = 'required|exists:branches,id';
        } else {
            $rules['church_id'] = 'required|exists:churches,id';
        }

        $validated = validator($request->all(), $rules)->validate();

        DB::beginTransaction();
        try {
            $branchId = $isMD ? Auth::user()?->roles->first()?->branch_id : $validated['branch_id'];

            $openingBalance = $this->calculateOpeningBalance($branchId, $validated['report_type'], $validated['report_year'], $validated['report_month'] ?? null);

            $report = FinancialReport::create([
                'branch_id' => $branchId,
                'report_type' => $validated['report_type'],
                'report_year' => $validated['report_year'],
                'report_month' => $validated['report_month'] ?? null,
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'opening_balance' => $openingBalance,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $categories = $this->getReportCategories();
            $churchName = '';
            if ($isMD && isset($validated['church_id'])) {
                $church = Church::find($validated['church_id']);
                $churchName = $church ? ' - ' . $church->pic_name : '';
            }

            foreach ($validated['details'] ?? [] as $d) {
                $amount = $this->normalizeNumber($d['amount'] ?? 0);
                $catInfo = $categories[$d['category_code']] ?? null;

                if (!$catInfo) {
                    continue;
                }

                FinancialReportDetail::create([
                    'financial_report_id' => $report->id,
                    'church_id' => $isMD ? $validated['church_id'] ?? null : $d['church_id'] ?? null,
                    'category_code' => $d['category_code'],
                    'category_name' => $catInfo['name'] . $churchName,
                    'category_group' => $catInfo['group'],
                    'type' => $catInfo['group'] === 'income' ? 'income' : 'expense',
                    'amount' => $amount,
                    'notes' => $d['notes'] ?? null,
                ]);
            }

            $groups = $this->getReportGroups();
            $totalIncome = $report->details()->whereIn('category_code', $groups['income']['cats'])->sum('amount');
            $totalExpense = $report->details()->whereIn('category_code', $groups['expense']['cats'])->sum('amount');

            $report->update([
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'closing_balance' => $openingBalance + $totalIncome - $totalExpense,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'redirect' => route('admin.finance.reports.show', $report),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, FinancialReport $report)
    {
        $userRole = auth()->user()->roles->first()->name ?? '';
        $isMD = Str::startsWith(strtolower($userRole), 'md_');
        $isMP = strtolower($userRole) === 'mp';
        $myBranchId = Auth::user()?->roles->first()?->branch_id;

        if (!$isMP && (string) $report->branch_id !== (string) $myBranchId) {
            abort(403);
        }
        if (!$isMP && $report->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Laporan sudah dikirim/disetujui'], 403);
        }

        $rules = [
            'report_type' => 'sometimes|in:monthly,annual',
            'report_year' => 'sometimes|integer|min:2020|max:' . (date('Y') + 1),
            'report_month' => 'sometimes|nullable|integer|min:1|max:12',
            'period_start' => 'sometimes|date',
            'period_end' => 'sometimes|date|after_or_equal:period_start',
            'details' => 'nullable|array',
            'details.*.category_code' => 'required|string',
            'details.*.amount' => 'nullable|numeric|min:0',
            'details.*.notes' => 'nullable|string|max:500',
        ];

        if (!$isMD) {
            $rules['branch_id'] = 'sometimes|exists:branches,id';
        } else {
            $rules['church_id'] = 'sometimes|exists:churches,id';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $updateData = ['updated_by' => Auth::id()];
            $periodChanged = false;

            if (!$isMD && isset($validated['branch_id'])) {
                $updateData['branch_id'] = $validated['branch_id'];
            }
            if (isset($validated['report_type'])) {
                $updateData['report_type'] = $validated['report_type'];
                $periodChanged = true;
            }
            if (isset($validated['report_year'])) {
                $updateData['report_year'] = $validated['report_year'];
                $periodChanged = true;
            }
            if (array_key_exists('report_month', $validated)) {
                $updateData['report_month'] = $validated['report_month'];
                $periodChanged = true;
            }
            if (isset($validated['period_start'])) {
                $updateData['period_start'] = $validated['period_start'];
            }
            if (isset($validated['period_end'])) {
                $updateData['period_end'] = $validated['period_end'];
            }

            $report->update($updateData);

            if ($periodChanged) {
                $newOpening = $this->calculateOpeningBalance($report->branch_id, $report->report_type, $report->report_year, $report->report_month);
                $report->update(['opening_balance' => $newOpening]);
            }

            $report->details()->delete();

            $categories = $this->getReportCategories();
            $churchName = '';
            if ($isMD) {
                $churchId = $validated['church_id'] ?? $report->details()->value('church_id');
                $church = $churchId ? Church::find($churchId) : null;
                $churchName = $church ? ' - ' . $church->pic_name : '';
            }

            foreach ($validated['details'] ?? [] as $d) {
                $amount = $this->normalizeNumber($d['amount'] ?? 0);
                $catInfo = $categories[$d['category_code']] ?? null;
                if (!$catInfo) {
                    continue;
                }

                FinancialReportDetail::create([
                    'financial_report_id' => $report->id,
                    'church_id' => $isMD ? $validated['church_id'] ?? null : $d['church_id'] ?? null,
                    'category_code' => $d['category_code'],
                    'category_name' => $catInfo['name'] . $churchName,
                    'category_group' => $catInfo['group'],
                    'type' => $catInfo['group'] === 'income' ? 'income' : 'expense',
                    'amount' => $amount,
                    'notes' => $d['notes'] ?? null,
                ]);
            }

            $groups = $this->getReportGroups();
            $totalIncome = $report->details()->whereIn('category_code', $groups['income']['cats'])->sum('amount');
            $totalExpense = $report->details()->whereIn('category_code', $groups['expense']['cats'])->sum('amount');

            $report->update([
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'closing_balance' => $report->opening_balance + $totalIncome - $totalExpense,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laporan diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal: ' . $e->getMessage())
                ->withInput();
        }
    }

        public function link(Request $request, Church $church)
    {
        if ($church->branch_id != $this->branchId()) {
            abort(403);
        }

        if (!$this->isItem($church)) {
            return response()->json(['success' => false, 'message' => 'Hanya item yang bisa dihubungkan'], 422);
        }

        $validated = $request->validate([
            'church_id' => 'nullable|string|max:64',
        ]);

        $parentId = $this->validParent($validated['church_id'] ?? null);

        $church->update([
            'pic_name' => self::ITEM_PREFIX . ($parentId ?? ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => $parentId ? 'Item berhasil dihubungkan ke gereja' : 'Hubungan dilepas, item kini mandiri',
        ]);
    }

    public function migrate(Request $request, Church $church)
    {
        if ($church->branch_id != $this->branchId()) {
            abort(403);
        }

        if (!$this->isItem($church)) {
            return response()->json(['success' => false, 'message' => 'Data ini sudah berupa gereja'], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pic_name' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (str_starts_with((string) $value, self::ITEM_PREFIX)) {
                        $fail('Gembala / PIC tidak boleh diawali "' . self::ITEM_PREFIX . '"');
                    }
                },
            ],
        ]);

        $pic = trim((string) ($validated['pic_name'] ?? ''));

        $church->update([
            'name' => $validated['name'],
            'pic_name' => $pic !== '' ? $pic : null,
        ]);

        return response()->json(['success' => true, 'message' => 'Item berhasil dimigrasi menjadi gereja']);
    }

    public function show(FinancialReport $report)
    {
        $user = Auth::user();
        $isMP = strtolower($user?->roles->first()->name ?? '') === 'mp';
        $myBranchId = $user?->roles->first()?->branch_id;

        $canView = $isMP || (string) $report->branch_id === (string) $myBranchId || (string) $report->submitted_to === (string) $myBranchId || $report->forwards()->where('to_branch_id', $myBranchId)->exists();
        if (!$canView) {
            abort(403);
        }

        $report->load(['details.church', 'branch', 'submittedToBranch', 'approver']);
        $church = $report->details->first()?->church;
        $status = $report->status;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $report->id,
                'status' => $status,
                'report_type' => $report->report_type,
                'report_year' => $report->report_year,
                'report_month' => $report->report_month,
                'branch_name' => $report->branch?->name,
                'church_name' => $church?->name,
                'church_pic' => $church?->pic_name,
                'submitted_to_name' => $report->submittedToBranch?->name,
                'opening_balance' => (float) $report->opening_balance,
                'total_income' => (float) $report->total_income,
                'total_expense' => (float) $report->total_expense,
                'closing_balance' => (float) $report->closing_balance,
                'rejection_reason' => $report->rejection_reason,
                'notes' => $report->notes,
                'created_at' => $report->created_at?->format('d F Y H:i'),
                'submitted_at' => $report->submitted_at?->format('d F Y H:i'),
                'approved_at' => $report->approved_at?->format('d F Y H:i'),
                'approver_name' => $report->approver?->name,
                'details' => $report->details->map(fn($d) => [
                    'category_name' => $d->category_name,
                    'category_group' => $d->category_group,
                    'amount' => (float) $d->amount,
                    'notes' => $d->notes,
                ])->values(),
                'can_edit' => ($isMP && in_array($status, ['draft', 'submitted'])) || (!$isMP && $status === 'draft'),
                'can_submit' => !$isMP && $status === 'draft' && $user->hasPermission('finance.report.submit'),
                'can_approve' => $user->hasPermission('finance.report.approve')
                    && in_array($status, ['submitted', 'reviewed'])
                    && (string) $this->getCurrentHolder($report) === (string) $myBranchId,
                'export_url' => $user->hasPermission('finance.report.export')
                    ? route('admin.finance.export.annual-report', $report)
                    : null,
            ],
        ]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');

        $sheet->setCellValue('A1', 'Bulan (1-12)');
        $sheet->setCellValue('B1', 'Kode Kategori');
        $sheet->setCellValue('C1', 'Jumlah');
        $sheet->setCellValue('D1', 'Catatan');

        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $categories = array_keys($this->getReportCategories());
        $row = 2;

        foreach (range(1, 12) as $month) {
            foreach ($categories as $cat) {
                if ($cat === 'SALDO_AWAL' && $month > 1) {
                    continue;
                }

                $sheet->setCellValue("A{$row}", $month);
                $sheet->setCellValue("B{$row}", $cat);
                $sheet->setCellValue("C{$row}", 0);
                $sheet->setCellValue("D{$row}", '');
                $row++;
            }
        }

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(40);

        $filename = 'Template_Import_Laporan_1_Tahun.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function edit(FinancialReport $report)
    {
        $isMP = strtolower(Auth::user()?->roles->first()->name ?? '') === 'mp';
        $myBranchId = Auth::user()?->roles->first()?->branch_id;

        if (!$isMP && (string) $report->branch_id !== (string) $myBranchId) {
            abort(403);
        }

        $report->load('details');

        // Satu baris per kategori (jumlah digabung bila ada beberapa baris dengan kode sama)
        $details = [];
        foreach ($report->details as $d) {
            $details[$d->category_code]['amount'] = ($details[$d->category_code]['amount'] ?? 0) + (float) $d->amount;
            if (empty($details[$d->category_code]['notes']) && $d->notes) {
                $details[$d->category_code]['notes'] = $d->notes;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $report->id,
                'branch_id' => $report->branch_id,
                'church_id' => $report->details->first()?->church_id,
                'report_type' => $report->report_type,
                'report_year' => $report->report_year,
                'report_month' => $report->report_month,
                'period_start' => $report->period_start ? \Carbon\Carbon::parse($report->period_start)->format('Y-m-d') : null,
                'period_end' => $report->period_end ? \Carbon\Carbon::parse($report->period_end)->format('Y-m-d') : null,
                'details' => $details,
            ],
        ]);
    }

    public function destroy(FinancialReport $report)
    {
        $userRole = auth()->user()->roles->first()->name ?? '';
        $isMP = strtolower($userRole) === 'mp';
        $myBranchId = Auth::user()?->roles->first()?->branch_id;

        if (!$isMP && (string) $report->branch_id !== (string) $myBranchId) {
            abort(403);
        }
        if (!$isMP && $report->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Laporan sudah dikirim'], 403);
        }

        $report->details()->delete();
        $report->delete();

        return response()->json(['success' => true, 'message' => 'Laporan dihapus']);
    }

    public function submit(FinancialReport $report, Request $request)
    {
        $myBranchId = Auth::user()?->roles->first()?->branch_id;
        if ($myBranchId && (string) $report->branch_id !== (string) $myBranchId) {
            abort(403);
        }
        if ($report->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Laporan sudah dikirim'], 400);
        }

        $validated = $request->validate([
            'submitted_to' => 'required|exists:branches,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ((string) $validated['submitted_to'] === (string) $report->branch_id) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa kirim ke diri sendiri'], 400);
        }

        DB::beginTransaction();
        try {
            $report->update([
                'status' => 'submitted',
                'submitted_to' => $validated['submitted_to'],
                'submitted_at' => now(),
                'submitted_by' => Auth::id(),
            ]);

            FinancialReportForward::create([
                'financial_report_id' => $report->id,
                'from_branch_id' => $report->branch_id,
                'to_branch_id' => $validated['submitted_to'],
                'forwarded_by' => Auth::id(),
                'forwarded_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'action' => 'submit',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laporan berhasil dikirim']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function submitFolder(Request $request)
    {
        $userRole = Auth::user()?->roles->first()->name ?? '';
        $isMD = Str::startsWith(strtolower($userRole), 'md_');
        $myBranchId = Auth::user()?->roles->first()?->branch_id;

        if (!$isMD) {
            return response()->json(['success' => false, 'message' => 'Hanya MD yang dapat mengirim per periode'], 403);
        }

        $validated = $request->validate([
            'period_key' => 'required|string',
            'submitted_to' => 'required|exists:branches,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $parts = explode('-', $validated['period_key']);
        $year = (int) $parts[0];
        $month = isset($parts[1]) && $parts[1] !== 'annual' ? (int) $parts[1] : null;
        if ($month !== null) {
            $month = max(1, min(12, $month));
        }

        $reports = FinancialReport::where('branch_id', $myBranchId)
            ->where('report_year', $year)
            ->when($month !== null, fn($q) => $q->where('report_month', $month))
            ->where('status', 'draft')
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada laporan draft di periode ini'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($reports as $report) {
                $report->update([
                    'status' => 'submitted',
                    'submitted_to' => $validated['submitted_to'],
                    'submitted_at' => now(),
                    'submitted_by' => Auth::id(),
                ]);

                FinancialReportForward::create([
                    'financial_report_id' => $report->id,
                    'from_branch_id' => $myBranchId,
                    'to_branch_id' => $validated['submitted_to'],
                    'forwarded_by' => Auth::id(),
                    'forwarded_at' => now(),
                    'notes' => $validated['notes'],
                    'action' => 'submit',
                ]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => count($reports) . ' laporan periode berhasil dikirim']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function forward(FinancialReport $report, Request $request)
    {
        if (!in_array($report->status, ['submitted', 'reviewed'])) {
            return response()->json(['success' => false, 'message' => 'Status laporan tidak memungkinkan untuk diteruskan'], 400);
        }

        $validated = $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $alreadyForwarded = $report->forwards()->where('to_branch_id', $validated['to_branch_id'])->exists();
        if ($alreadyForwarded || (string) $validated['to_branch_id'] === (string) $report->branch_id) {
            return response()->json(['success' => false, 'message' => 'Laporan sudah pernah dikirim ke cabang tersebut'], 400);
        }

        DB::beginTransaction();
        try {
            FinancialReportForward::create([
                'financial_report_id' => $report->id,
                'from_branch_id' => Auth::user()?->roles->first()?->branch_id,
                'to_branch_id' => $validated['to_branch_id'],
                'forwarded_by' => Auth::id(),
                'forwarded_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'action' => 'forward',
            ]);

            $report->update(['submitted_to' => $validated['to_branch_id'], 'status' => 'reviewed']);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laporan berhasil diteruskan ke tingkat berikutnya']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function approve(FinancialReport $report)
    {
        $myBranchId = Auth::user()?->roles->first()?->branch_id;
        $currentHolder = $this->getCurrentHolder($report);

        if ((string) $currentHolder !== (string) $myBranchId) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berhak menyetujui laporan ini'], 403);
        }
        if (!in_array($report->status, ['submitted', 'reviewed'])) {
            return response()->json(['success' => false, 'message' => 'Status laporan tidak memungkinkan untuk disetujui'], 400);
        }

        DB::beginTransaction();
        try {
            $report->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
            FinancialReportForward::create([
                'financial_report_id' => $report->id,
                'from_branch_id' => $myBranchId,
                'to_branch_id' => $report->branch_id,
                'forwarded_by' => Auth::id(),
                'forwarded_at' => now(),
                'notes' => 'Laporan disetujui',
                'action' => 'approve',
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laporan disetujui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function approveFolder(Request $request)
    {
        $myBranchId = Auth::user()?->roles->first()?->branch_id;
        $validated = $request->validate([
            'period_key' => 'required|string',
            'md_id' => 'required|exists:branches,id',
        ]);

        $parts = explode('-', $validated['period_key']);
        $year = (int) $parts[0];
        $month = isset($parts[1]) && $parts[1] !== 'annual' ? (int) $parts[1] : null;
        if ($month !== null) {
            $month = max(1, min(12, $month));
        }

        $reports = FinancialReport::where('branch_id', $validated['md_id'])
            ->where('submitted_to', $myBranchId)
            ->where('report_year', $year)
            ->when($month !== null, fn($q) => $q->where('report_month', $month))
            ->whereIn('status', ['submitted', 'reviewed'])
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada laporan yang dapat disetujui di periode ini'], 400);
        }

        DB::beginTransaction();
        try {
            $approvedCount = 0;
            foreach ($reports as $report) {
                $currentHolder = $this->getCurrentHolder($report);
                if ((string) $currentHolder !== (string) $myBranchId) {
                    continue;
                }
                $report->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
                FinancialReportForward::create([
                    'financial_report_id' => $report->id,
                    'from_branch_id' => $myBranchId,
                    'to_branch_id' => $report->branch_id,
                    'forwarded_by' => Auth::id(),
                    'forwarded_at' => now(),
                    'notes' => 'Laporan disetujui (bulk)',
                    'action' => 'approve',
                ]);
                $approvedCount++;
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => $approvedCount . ' laporan berhasil disetujui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function reject(FinancialReport $report, Request $request)
    {
        $myBranchId = Auth::user()?->roles->first()?->branch_id;
        $currentHolder = $this->getCurrentHolder($report);

        if ((string) $currentHolder !== (string) $myBranchId) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berhak menolak laporan ini'], 403);
        }
        if (!in_array($report->status, ['submitted', 'reviewed'])) {
            return response()->json(['success' => false, 'message' => 'Status laporan tidak memungkinkan untuk ditolak'], 400);
        }

        $validated = $request->validate(['rejection_reason' => 'required|string|min:10']);

        DB::beginTransaction();
        try {
            $report->update(['status' => 'rejected', 'rejection_reason' => $validated['rejection_reason']]);
            FinancialReportForward::create([
                'financial_report_id' => $report->id,
                'from_branch_id' => $myBranchId,
                'to_branch_id' => $report->branch_id,
                'forwarded_by' => Auth::id(),
                'forwarded_at' => now(),
                'notes' => $validated['rejection_reason'],
                'action' => 'reject',
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laporan ditolak']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function rejectFolder(Request $request)
    {
        $myBranchId = Auth::user()?->roles->first()?->branch_id;
        $validated = $request->validate([
            'period_key' => 'required|string',
            'md_id' => 'required|exists:branches,id',
            'rejection_reason' => 'required|string|min:10',
        ]);

        $parts = explode('-', $validated['period_key']);
        $year = (int) $parts[0];
        $month = isset($parts[1]) && $parts[1] !== 'annual' ? (int) $parts[1] : null;
        if ($month !== null) {
            $month = max(1, min(12, $month));
        }

        $reports = FinancialReport::where('branch_id', $validated['md_id'])
            ->where('submitted_to', $myBranchId)
            ->where('report_year', $year)
            ->when($month !== null, fn($q) => $q->where('report_month', $month))
            ->whereIn('status', ['submitted', 'reviewed'])
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada laporan yang dapat ditolak di periode ini'], 400);
        }

        DB::beginTransaction();
        try {
            $rejectedCount = 0;
            foreach ($reports as $report) {
                $currentHolder = $this->getCurrentHolder($report);
                if ((string) $currentHolder !== (string) $myBranchId) {
                    continue;
                }
                $report->update(['status' => 'rejected', 'rejection_reason' => $validated['rejection_reason']]);
                FinancialReportForward::create([
                    'financial_report_id' => $report->id,
                    'from_branch_id' => $myBranchId,
                    'to_branch_id' => $report->branch_id,
                    'forwarded_by' => Auth::id(),
                    'forwarded_at' => now(),
                    'notes' => $validated['rejection_reason'],
                    'action' => 'reject',
                ]);
                $rejectedCount++;
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => $rejectedCount . ' laporan berhasil ditolak']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function receivedReports(Request $request)
    {
        return redirect()->route('admin.finance.reports.index', array_merge($request->query(), ['tab' => 'received']));
    }

    public function generateFromTransactions(Request $request)
    {
        if ($request->isMethod('post') && $request->header('Content-Type') === 'application/json') {
            $data = json_decode($request->getContent(), true);
            $request->merge($data);
        }

        $year = $request->year;
        $month = $request->month;
        $start = $month ? "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01' : "$year-01-01";
        $end = $month ? date('Y-m-t', strtotime($start)) : "$year-12-31";

        $codeMap = [
            'pp' => 'PP',
            'iuran_pejabat' => 'IURAN_PEJABAT',
            'dana_misi' => 'DANA_MISI',
            'bunga' => 'BUNGA_BANK',
            'lain_lain' => 'PENERIMAAN_LAIN',
            'bantuan_sakit_bpjs' => 'BPJS',
            'setoran_iuran_mp' => 'IURAN_MDF',
            'biaya_admin_bank' => 'ADMIN_BANK',
            'biaya_lain_majelis' => 'PENGELUARAN_LAIN',
        ];

        $myBranchId = Auth::user()?->roles->first()?->branch_id;
        $transactions = FinancialTransaction::where('branch_id', $myBranchId)
            ->whereBetween('transaction_date', [$start, $end])
            ->whereNotNull('approved_at')
            ->get();

        $details = [];
        foreach ($transactions as $tx) {
            $rc = $codeMap[$tx->category_code] ?? null;
            if ($rc) {
                $details[$rc] = ($details[$rc] ?? 0) + $tx->amount;
            }
        }

        $opening = $this->calculateOpeningBalance($myBranchId, $month ? 'monthly' : 'annual', $year, $month);
        $categories = $this->getReportCategories();

        return response()->json([
            'success' => true,
            'data' => [
                'period_start' => $start,
                'period_end' => $end,
                'opening_balance' => $opening,
                'details' => collect($details)
                    ->map(
                        fn($v, $k) => [
                            'category_code' => $k,
                            'category_name' => $categories[$k]['name'] ?? $k,
                            'category_group' => $categories[$k]['group'] ?? '',
                            'type' => $categories[$k]['group'] === 'income' ? 'income' : 'expense',
                            'amount' => $v,
                        ],
                    )
                    ->values(),
            ],
        ]);
    }

    public function consolidateReports(Request $request)
    {
        $myBranchId = Auth::user()?->roles->first()?->branch_id;
        $query = FinancialReport::with('details', 'branch')->where('submitted_to', $myBranchId)->where('status', 'approved')->where('report_year', $request->year);
        if ($request->month) {
            $query->where('report_month', $request->month);
        }

        $reports = $query->get();
        $detailsMap = [];

        foreach ($reports as $r) {
            foreach ($r->details as $d) {
                $key = $d->category_code . '_' . $d->type;
                if (!isset($detailsMap[$key])) {
                    $detailsMap[$key] = ['category_code' => $d->category_code, 'category_name' => $d->category_name, 'type' => $d->type, 'amount' => 0, 'branches' => []];
                }
                $detailsMap[$key]['amount'] += $d->amount;
                $bn = $r->branch->name;
                $detailsMap[$key]['branches'][$bn] = ($detailsMap[$key]['branches'][$bn] ?? 0) + $d->amount;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reports_count' => $reports->count(),
                'total_income' => $reports->sum('total_income'),
                'total_expense' => $reports->sum('total_expense'),
                'net_balance' => $reports->sum('total_income') - $reports->sum('total_expense'),
                'details' => array_values($detailsMap),
            ],
        ]);
    }

    private function getCurrentHolder(FinancialReport $report)
    {
        $lastForward = $report->forwards()->latest('forwarded_at')->first();
        return $lastForward ? $lastForward->to_branch_id : $report->submitted_to;
    }

    // Gereja untuk dropdown laporan (item dari halaman Data Item tidak ikut)
    private function churchOptions($branchId)
    {
        return Church::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('pic_name')->orWhere('pic_name', 'not like', 'item::%');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'pic_name']);
    }

    // Data tab "Laporan Masuk" (dulu ada di receivedReports)
    private function buildReceived(Request $request, bool $isMP, $myBranchId): array
    {
        $reports = FinancialReport::with(['branch', 'details'])
            ->when(!$isMP, fn($q) => $q->where('submitted_to', $myBranchId))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->year, fn($q, $v) => $q->where('report_year', $v))
            ->when($request->type, fn($q, $v) => $q->where('report_type', $v))
            ->when($request->month, fn($q, $v) => $q->where('report_month', $v))
            ->orderBy('report_year', 'desc')
            ->orderBy('report_month', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [];
        foreach ($reports as $report) {
            $mdId = $report->branch_id;
            $month = (int) $report->report_month;

            if ($report->report_type === 'monthly') {
                $safeMonth = str_pad(max(1, min(12, $month)), 2, '0', STR_PAD_LEFT);
                $periodKey = $report->report_year . '-' . $safeMonth;
                $periodLabel = \Carbon\Carbon::create($report->report_year, $safeMonth)->format('F Y');
            } else {
                $periodKey = $report->report_year . '-annual';
                $periodLabel = 'Tahunan ' . $report->report_year;
            }

            $report->missing_pp = !$report->details->contains(fn($d) => $d->category_code === 'PP' && $d->amount > 0);

            if (!isset($grouped[$mdId])) {
                $grouped[$mdId] = ['md_name' => $report->branch->name ?? '-', 'total_income' => 0, 'total_expense' => 0, 'total_balance' => 0, 'periods' => []];
            }
            if (!isset($grouped[$mdId]['periods'][$periodKey])) {
                $grouped[$mdId]['periods'][$periodKey] = ['label' => $periodLabel, 'reports' => [], 'total_income' => 0, 'total_expense' => 0, 'total_balance' => 0];
            }

            $grouped[$mdId]['periods'][$periodKey]['reports'][] = $report;
            foreach (['total_income' => 'total_income', 'total_expense' => 'total_expense', 'total_balance' => 'closing_balance'] as $k => $col) {
                $grouped[$mdId]['periods'][$periodKey][$k] += (float) $report->{$col};
                $grouped[$mdId][$k] += (float) $report->{$col};
            }
        }

        return $grouped;
    }

    private function normalizeNumber($value): float
    {
        if (is_null($value)) {
            return 0.0;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return 0.0;
        }

        $value = preg_replace('/[^\d,.\-]/', '', $value);
        $commaCount = substr_count($value, ',');
        $dotCount = substr_count($value, '.');

        if ($commaCount > 0 && $dotCount > 0) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($commaCount > 0 && $dotCount === 0) {
            if ($commaCount === 1 && strlen(substr(strrchr($value, ','), 1)) <= 2) {
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($dotCount > 0 && $commaCount === 0) {
            if ($dotCount === 1 && strlen(substr(strrchr($value, '.'), 1)) > 2) {
                $value = str_replace('.', '', $value);
            }
        }

        return (float) $value;
    }
}
