<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FinancialTransaction;
use App\Models\FinancialReport;
use App\Models\FinancialReportDetail;
use App\Models\FinancialReportForward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialReportController extends Controller
{
    private function getReportCategories()
    {
        return [
            'SALDO_AWAL' => ['name' => 'SALDO AWAL', 'group' => 'balance'],
            'PP' => ['name' => 'PERSEPULUHAN', 'group' => 'income'],
            'IURAN_PEJABAT' => ['name' => 'IURAN PEJABAT', 'group' => 'income'],
            'DANA_MISI' => ['name' => 'DANA MISI DAN PENDIDIKAN', 'group' => 'income'],
            'PENERIMAAN_LAIN' => ['name' => 'PENERIMAAN LAIN-LAIN', 'group' => 'income'],
            'BUNGA_BANK' => ['name' => 'BUNGA BANK', 'group' => 'income'],
            'SETORAN_PP_MP' => ['name' => 'Setoran Persepuluhan ke MP', 'group' => 'expense_majelis'],
            'SETORAN_IURAN_MP' => ['name' => 'Setoran Iuran Pejabat ke MP', 'group' => 'expense_majelis'],
            'BIAYA_MIKERIA' => ['name' => 'Biaya Misi, Mikeria, Upgrading', 'group' => 'expense_majelis'],
            'BIAYA_PASKAH_NATAL' => ['name' => 'Biaya Paskah, HUT, Natal', 'group' => 'expense_majelis'],
            'BIAYA_RAPAT' => ['name' => 'Biaya Rapat', 'group' => 'expense_majelis'],
            'BIAYA_KURIKULUM' => ['name' => 'Biaya Kurikulum dan Kerja Dinas', 'group' => 'expense_majelis'],
            'BIAYA_SEKRETARIAT' => ['name' => 'Biaya Sekretariat', 'group' => 'expense_majelis'],
            'BIAYA_LAIN_MAJELIS' => ['name' => 'Biaya Lain-lain Majelis', 'group' => 'expense_majelis'],
            'BIAYA_ADMIN_BANK' => ['name' => 'Biaya Administrasi Bank', 'group' => 'expense_majelis'],
            'BIAYA_BUKU_PENATALAYANAN' => ['name' => 'Biaya Pembelian Buku Penatalayanan', 'group' => 'expense_penatalayanan'],
            'BIAYA_PENATALAYANAN_LAIN' => ['name' => 'Biaya Lain-lain Penatalayanan', 'group' => 'expense_penatalayanan'],
            'BIAYA_SEKOLAH_MINGGU' => ['name' => 'Biaya Program Sekolah Minggu', 'group' => 'expense_penggembalaan'],
            'BIAYA_PEMUDA' => ['name' => 'Biaya Program Pemuda', 'group' => 'expense_penggembalaan'],
            'BIAYA_WANITA' => ['name' => 'Biaya Program Wanita', 'group' => 'expense_penggembalaan'],
            'BIAYA_CAMP_SEMINAR' => ['name' => 'Biaya Camp, Seminar Hamba-hamba Tuhan', 'group' => 'expense_penggembalaan'],
            'SETORAN_DANA_MISI_MP' => ['name' => 'Setoran Dana Misi dan Pendidikan ke MP', 'group' => 'expense_misi'],
            'BIAYA_MISI_SEMINAR' => ['name' => 'Biaya Misi, Seminar Misi MD', 'group' => 'expense_misi'],
            'BIAYA_PENDIDIKAN_MD' => ['name' => 'Biaya Pendidikan MD', 'group' => 'expense_misi'],
            'BANTUAN_SAKIT' => ['name' => 'Bantuan Sakit (Termasuk BPJS)', 'group' => 'expense_pelayanan'],
            'BANTUAN_DUKACITA' => ['name' => 'Bantuan Dukacita', 'group' => 'expense_pelayanan'],
            'BANTUAN_PEMBANGUNAN' => ['name' => 'Bantuan Pembangunan Gereja', 'group' => 'expense_pelayanan'],
            'BANTUAN_BENCANA' => ['name' => 'Bantuan Bencana Alam', 'group' => 'expense_pelayanan'],
            'BIAYA_MULTIMEDIA' => ['name' => 'Biaya Bidang dan Multi Media', 'group' => 'expense_pelayanan'],
        ];
    }

    private function getReportGroups()
    {
        return [
            'income' => [
                'title' => 'PEMASUKAN',
                'cats' => ['PP', 'IURAN_PEJABAT', 'DANA_MISI', 'PENERIMAAN_LAIN', 'BUNGA_BANK'],
            ],
            'expense_majelis' => [
                'title' => 'BIAYA MAJELIS DAERAH',
                'cats' => ['SETORAN_PP_MP', 'SETORAN_IURAN_MP', 'BIAYA_MIKERIA', 'BIAYA_PASKAH_NATAL', 'BIAYA_RAPAT', 'BIAYA_KURIKULUM', 'BIAYA_SEKRETARIAT', 'BIAYA_LAIN_MAJELIS', 'BIAYA_ADMIN_BANK'],
            ],
            'expense_penatalayanan' => [
                'title' => 'BIAYA BIDANG PENATALAYANAN',
                'cats' => ['BIAYA_BUKU_PENATALAYANAN', 'BIAYA_PENATALAYANAN_LAIN'],
            ],
            'expense_penggembalaan' => [
                'title' => 'BIAYA BIDANG PENGGEMBALAAN',
                'cats' => ['BIAYA_SEKOLAH_MINGGU', 'BIAYA_PEMUDA', 'BIAYA_WANITA', 'BIAYA_CAMP_SEMINAR'],
            ],
            'expense_misi' => [
                'title' => 'BIAYA BIDANG PENDIDIKAN DAN MISI',
                'cats' => ['SETORAN_DANA_MISI_MP', 'BIAYA_MISI_SEMINAR', 'BIAYA_PENDIDIKAN_MD'],
            ],
            'expense_pelayanan' => [
                'title' => 'BIAYA BIDANG PELAYANAN MASYARAKAT',
                'cats' => ['BANTUAN_SAKIT', 'BANTUAN_DUKACITA', 'BANTUAN_PEMBANGUNAN', 'BANTUAN_BENCANA', 'BIAYA_MULTIMEDIA'],
            ],
        ];
    }

    public function index(Request $request)
    {
        $query = FinancialReport::with(['branch', 'submittedToBranch', 'approver'])
            ->when( Auth::user()?->roles->first()?->branch_id, fn($q) => $q->where('branch_id',  Auth::user()?->roles->first()?->branch_id))
            ->orderBy('report_year', 'desc')
            ->orderBy('report_month', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->type) {
            $query->where('report_type', $request->type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->year) {
            $query->where('report_year', $request->year);
        }

        if ($request->scope === 'internal') {
            $query->whereNull('submitted_to');
        } elseif ($request->scope === 'external') {
            $query->whereNotNull('submitted_to');
        }

        $pendingCount = FinancialReport::where('submitted_to',  Auth::user()?->roles->first()?->branch_id)->where('status', 'submitted')->count();

        return view('features.finance.reports.index', [
            'reports' => $query->paginate(20),
            'pendingCount' => $pendingCount,
            'categories' => $this->getReportCategories(),
            'groups' => $this->getReportGroups(),
        ]);
    }

    public function create(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month ?? date('m');
        $type = $request->type ?? 'monthly';
        $categories = $this->getReportCategories();
        $groups = $this->getReportGroups();

        return view('features.finance.reports.create', compact('year', 'month', 'type', 'categories', 'groups'));
    }

    public function storeReport(Request $request)
    {
        $validated = validator($request->all(), [
            'report_type' => 'required|in:monthly,annual',
            'report_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'report_month' => 'required_if:report_type,monthly|nullable|integer|min:1|max:12',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'opening_balance' => 'nullable',
            'details' => 'nullable|array',
            'details.*.category_code' => 'nullable|string',
            'details.*.category_name' => 'nullable|string',
            'details.*.category_group' => 'nullable|string',
            'details.*.type' => 'nullable|in:income,expense,balance',
            'details.*.amount' => 'nullable',
            'details.*.notes' => 'nullable|string|max:500',
        ])->validate();

        DB::beginTransaction();

        $report = FinancialReport::create([
            'branch_id' => Auth::user()?->roles->first()?->branch_id,
            'report_type' => $validated['report_type'],
            'report_year' => $validated['report_year'],
            'report_month' => $validated['report_month'] ?? null,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'opening_balance' => $this->normalizeNumber($validated['opening_balance'] ?? 0),
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        foreach ($validated['details'] ?? [] as $d) {
            $amount = $this->normalizeNumber($d['amount'] ?? 0);
            if ($amount > 0) {
                FinancialReportDetail::create([
                    'financial_report_id' => $report->id,
                    'category_code' => $d['category_code'] ?? null,
                    'category_name' => $d['category_name'] ?? null,
                    'category_group' => $d['category_group'] ?? null,
                    'type' => $d['type'] ?? null,
                    'amount' => $amount,
                    'notes' => $d['notes'] ?? null,
                ]);
            }
        }

        $report->calculateTotals();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dibuat',
            'redirect' => route('admin.finance.reports.show', $report)
        ]);
    }

    private function normalizeNumber($value)
    {
        if (is_null($value)) return 0;

        if (is_int($value) || is_float($value)) return (float) $value;

        $value = trim($value);

        if ($value === '') return 0;

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
            if ($dotCount === 1 && strlen(substr(strrchr($value, '.'), 1)) <= 2) {
            } else {
                $value = str_replace('.', '', $value);
            }
        }

        return (float) $value;
    }

    public function show(FinancialReport $report)
    {
        $canView = $report->branch_id ===  Auth::user()?->roles->first()?->branch_id || $report->submitted_to ===  Auth::user()?->roles->first()?->branch_id || $report->forwards()->where('to_branch_id',  Auth::user()?->roles->first()?->branch_id)->exists();

        if ( Auth::user()?->roles->first()?->branch_id && !$canView) {
            abort(403);
        }

        $report->load(['details', 'branch', 'submittedToBranch', 'approver', 'forwards.fromBranch', 'forwards.toBranch', 'forwards.actor']);

        $forwardedBranchIds = $report->forwards->pluck('to_branch_id')->push($report->branch_id)->unique();
        $availableBranches = Branch::where('is_active', true)->whereNotIn('id', $forwardedBranchIds)->orderBy('name')->get();

        return view('features.finance.reports.show', [
            'report' => $report,
            'categories' => $this->getReportCategories(),
            'availableBranches' => $availableBranches,
        ]);
    }

    public function edit(FinancialReport $report)
    {
        if ( Auth::user()?->roles->first()?->branch_id && $report->branch_id !==  Auth::user()?->roles->first()?->branch_id) {
            abort(403);
        }

        if ($report->status !== 'draft') {
            abort(403, 'Laporan sudah dikirim/disetujui');
        }

        return view('features.finance.reports.create', [
            'report' => $report,
            'year' => $report->report_year,
            'month' => $report->report_month,
            'type' => $report->report_type,
            'categories' => $this->getReportCategories(),
            'groups' => $this->getReportGroups(),
        ]);
    }

    public function update(Request $request, FinancialReport $report)
    {
        if ( Auth::user()?->roles->first()?->branch_id && $report->branch_id !==  Auth::user()?->roles->first()?->branch_id) {
            abort(403);
        }
        if ($report->status !== 'draft') {
            return back()->with('error', 'Laporan sudah dikirim/disetujui');
        }

        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'details' => 'required|array',
            'details.*.category_code' => 'required|string',
            'details.*.category_name' => 'required|string',
            'details.*.category_group' => 'required|string',
            'details.*.type' => 'required|in:income,expense',
            'details.*.amount' => 'required|numeric|min:0',
            'details.*.notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $report->update([
                'opening_balance' => $validated['opening_balance'],
                'updated_by' => Auth::id(),
            ]);
            $report->details()->delete();
            foreach ($validated['details'] as $d) {
                if ((float) $d['amount'] > 0) {
                    FinancialReportDetail::create(array_merge(['financial_report_id' => $report->id], $d));
                }
            }
            $report->calculateTotals();
            DB::commit();
            return redirect()->route('admin.finance.reports.show', $report)->with('success', 'Laporan diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Gagal: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(FinancialReport $report)
    {
        if ( Auth::user()?->roles->first()?->branch_id && $report->branch_id !==  Auth::user()?->roles->first()?->branch_id) {
            abort(403);
        }
        if ($report->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Laporan sudah dikirim'], 403);
        }
        $report->details()->delete();
        $report->delete();
        return response()->json(['success' => true, 'message' => 'Laporan dihapus']);
    }

    public function submit(FinancialReport $report, Request $request)
    {
        if ( Auth::user()?->roles->first()?->branch_id && $report->branch_id !==  Auth::user()?->roles->first()?->branch_id) {
            abort(403);
        }
        if ($report->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Laporan sudah dikirim'], 400);
        }

        $validated = $request->validate([
            'submitted_to' => 'required|exists:branches,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['submitted_to'] == $report->branch_id) {
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
        if ($alreadyForwarded || $validated['to_branch_id'] == $report->branch_id) {
            return response()->json(['success' => false, 'message' => 'Laporan sudah pernah dikirim ke cabang tersebut'], 400);
        }

        DB::beginTransaction();
        try {
            FinancialReportForward::create([
                'financial_report_id' => $report->id,
                'from_branch_id' =>  Auth::user()?->roles->first()?->branch_id,
                'to_branch_id' => $validated['to_branch_id'],
                'forwarded_by' => Auth::id(),
                'forwarded_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'action' => 'forward',
            ]);

            $report->update([
                'submitted_to' => $validated['to_branch_id'],
                'status' => 'reviewed',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laporan berhasil diteruskan ke tingkat berikutnya']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function approve(FinancialReport $report)
    {
        $currentHolder = $this->getCurrentHolder($report);

        if ($currentHolder !==  Auth::user()?->roles->first()?->branch_id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berhak menyetujui laporan ini'], 403);
        }

        if (!in_array($report->status, ['submitted', 'reviewed'])) {
            return response()->json(['success' => false, 'message' => 'Status laporan tidak memungkinkan untuk disetujui'], 400);
        }

        DB::beginTransaction();
        try {
            $report->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            FinancialReportForward::create([
                'financial_report_id' => $report->id,
                'from_branch_id' =>  Auth::user()?->roles->first()?->branch_id,
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

    public function reject(FinancialReport $report, Request $request)
    {
        $currentHolder = $this->getCurrentHolder($report);
        if ($currentHolder !==  Auth::user()?->roles->first()?->branch_id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berhak menolak laporan ini'], 403);
        }
        if (!in_array($report->status, ['submitted', 'reviewed'])) {
            return response()->json(['success' => false, 'message' => 'Status laporan tidak memungkinkan untuk ditolak'], 400);
        }
        $validated = $request->validate(['rejection_reason' => 'required|string|min:10']);
        DB::beginTransaction();
        try {
            $report->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            FinancialReportForward::create([
                'financial_report_id' => $report->id,
                'from_branch_id' =>  Auth::user()?->roles->first()?->branch_id,
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

    public function receivedReports(Request $request)
    {
        $query = FinancialReport::with(['branch', 'submittedToBranch', 'approver', 'forwards'])
            ->where('submitted_to',  Auth::user()?->roles->first()?->branch_id)
            ->orderBy('updated_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->year) {
            $query->where('report_year', $request->year);
        }
        if ($request->type) {
            $query->where('report_type', $request->type);
        }

        $availableBranches = Branch::where('is_active', true)->where('id', '!=',  Auth::user()?->roles->first()?->branch_id)->orderBy('name')->get();

        return view('features.finance.reports.received', [
            'reports' => $query->paginate(20),
            'availableBranches' => $availableBranches,
        ]);
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
            'lain_lain' => 'PENERIMAAN_LAIN',
            'setor_tunai' => 'PENERIMAAN_LAIN',
            'bunga' => 'BUNGA_BANK',
            'setoran_pp_mp' => 'SETORAN_PP_MP',
            'setoran_iuran_mp' => 'SETORAN_IURAN_MP',
            'biaya_mikeria' => 'BIAYA_MIKERIA',
            'biaya_paskah_natal' => 'BIAYA_PASKAH_NATAL',
            'biaya_rapat' => 'BIAYA_RAPAT',
            'biaya_kurikulum' => 'BIAYA_KURIKULUM',
            'biaya_sekretariat' => 'BIAYA_SEKRETARIAT',
            'biaya_lain_majelis' => 'BIAYA_LAIN_MAJELIS',
            'biaya_admin_bank' => 'BIAYA_ADMIN_BANK',
            'biaya_buku_penatalayanan' => 'BIAYA_BUKU_PENATALAYANAN',
            'biaya_penatalayanan_lain' => 'BIAYA_PENATALAYANAN_LAIN',
            'biaya_sekolah_minggu' => 'BIAYA_SEKOLAH_MINGGU',
            'biaya_pemuda' => 'BIAYA_PEMUDA',
            'biaya_wanita' => 'BIAYA_WANITA',
            'biaya_camp_seminar' => 'BIAYA_CAMP_SEMINAR',
            'setoran_dana_misi_mp' => 'SETORAN_DANA_MISI_MP',
            'biaya_misi_seminar' => 'BIAYA_MISI_SEMINAR',
            'biaya_pendidikan_md' => 'BIAYA_PENDIDIKAN_MD',
            'bantuan_sakit_bpjs' => 'BANTUAN_SAKIT',
            'bantuan_dukacita' => 'BANTUAN_DUKACITA',
            'bantuan_pembangunan' => 'BANTUAN_PEMBANGUNAN',
            'bantuan_bencana' => 'BANTUAN_BENCANA',
            'biaya_multimedia' => 'BIAYA_MULTIMEDIA',
        ];

        $transactions = FinancialTransaction::where('branch_id',  Auth::user()?->roles->first()?->branch_id)
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

        $prevReport = FinancialReport::where('branch_id',  Auth::user()?->roles->first()?->branch_id)->where('period_end', '<', $start)->where('status', 'approved')->latest('period_end')->first();
        $opening = $prevReport ? $prevReport->closing_balance : 0;

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
                            'type' => in_array($categories[$k]['group'] ?? '', ['income']) ? 'income' : 'expense',
                            'amount' => $v,
                        ],
                    )
                    ->values(),
            ],
        ]);
    }

    public function consolidateReports(Request $request)
    {
        $query = FinancialReport::with('details', 'branch')->where('submitted_to',  Auth::user()?->roles->first()?->branch_id)->where('status', 'approved')->where('report_year', $request->year);

        if ($request->month) {
            $query->where('report_month', $request->month);
        }

        $reports = $query->get();
        $detailsMap = [];

        foreach ($reports as $r) {
            foreach ($r->details as $d) {
                $key = $d->category_code . '_' . $d->type;
                if (!isset($detailsMap[$key])) {
                    $detailsMap[$key] = [
                        'category_code' => $d->category_code,
                        'category_name' => $d->category_name,
                        'type' => $d->type,
                        'amount' => 0,
                        'branches' => [],
                    ];
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

    private function getCurrentHolder(FinancialReport $report): ?string
    {
        $lastForward = $report->forwards()->latest('forwarded_at')->first();
        return $lastForward ? $lastForward->to_branch_id : $report->submitted_to;
    }
}
