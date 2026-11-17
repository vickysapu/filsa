<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Church;
use App\Models\ExportTemplate;
use App\Models\FinancialReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinancialExportController extends Controller
{
    private const AGGS = ['sum', 'count', 'avg', 'min', 'max', 'last'];

    private const BUCKETS = ['week', 'month', 'quarter', 'semester', 'year', 'all'];

    private const DIMS = ['church', 'pic', 'branch', 'category', 'description'];

    private const DEBIT_MAP = [
        'PP' => 'pp',
        'IURAN_PEJABAT' => 'iuran',
        'DANA_MISI' => 'dana_misi',
        'BUNGA_BANK' => 'bunga',
        'PENERIMAAN_LAIN' => 'lain_debit',
    ];

    private const CREDIT_MAP = [
        'BPJS'              => 'bpjs',
        'IURAN_MDF'          => 'iuran_mdf',
        'TAHAKA'             => 'tahaka',
        'PAJAK_BUNGA'        => 'pajak_bunga',
        'ADMIN_BANK'         => 'admin',
        'PENGELUARAN_LAIN'   => 'lain_kredit',
    ];

    private const FIELDS = [
        'date' => ['Tanggal', 'date', 12],
        'description' => ['Uraian', 'text', 40],
        'pic' => ['Penanggung Jawab', 'text', 36],
        'church' => ['Gereja', 'text', 26],
        'branch' => ['MD / Cabang', 'text', 22],
        'category' => ['Kategori', 'text', 24],
        'debit' => ['Debit (total)', 'number', 18],
        'credit' => ['Kredit (total)', 'number', 18],
        'balance' => ['Saldo', 'number', 18],
        'pp' => ['PP', 'number', 16],
        'iuran' => ['Iuran', 'number', 16],
        'dana_misi' => ['Dana Misi', 'number', 16],
        'bunga' => ['Bunga', 'number', 16],
        'lain_debit' => ['Lain-lain (Debit)', 'number', 16],
        'bpjs' => ['BPJS', 'number', 16],
        'iuran_mdf' => ['Iuran MDF', 'number', 16],
        'tahaka' => ['TAHAKA', 'number', 16],
        'pajak_bunga' => ['Pajak Bunga', 'number', 16],
        'admin' => ['Admin', 'number', 16],
        'lain_kredit' => ['Lain-lain (Kredit)', 'number', 16],
    ];

    private const RAPI_COLS = [
        'E' => 'pp',
        'F' => 'iuran',
        'G' => 'dana_misi',
        'H' => 'bunga',
        'I' => 'lain_debit',
        'J' => 'bpjs',
        'K' => 'iuran_mdf',
        'L' => 'tahaka',
        'M' => 'pajak_bunga',
        'N' => 'admin',
        'O' => 'lain_kredit',
    ];

    private const BUKU_KAS_DEBIT_COLS = [
        'C' => ['PP'],
        'D' => ['IURAN_PEJABAT'],
        'E' => ['DANA_MISI'],
        'F' => ['BUNGA_BANK'],
        'G' => ['PENERIMAAN_LAIN'],
    ];

    private const BUKU_KAS_CREDIT_COLS = [
        'L' => ['BPJS', 'BANTUAN_SAKIT'],
        'M' => ['IURAN_MDF', 'SETORAN_PP_MP', 'SETORAN_IURAN_MP', 'SETORAN_DANA_MISI_MP'],
        'N' => ['TAHAKA', 'BIAYA_PASKAH_NATAL'],
        'O' => ['PAJAK_BUNGA'],
        'P' => ['ADMIN_BANK', 'BIAYA_ADMIN_BANK'],
        'Q' => ['PENGELUARAN_LAIN', 'BIAYA_LAIN_MAJELIS', 'BIAYA_PENATALAYANAN_LAIN', 'BIAYA_MULTIMEDIA'],
    ];

    private const ACC = '_-"Rp"* #,##0.00_-;\-"Rp"* #,##0.00_-;_-"Rp"* "-"??_-;_-@_-';

    private const XLSX_MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private function getReportStructure(): array
    {
        return [
            ['type' => 'section_header', 'label' => 'K A S   M A S U K'],
            ['type' => 'row', 'key' => 'opening_balance', 'label' => 'SALDO AWAL'],
            ['type' => 'section_header', 'label' => 'PENERIMAAN  DANA'],
            ['type' => 'row', 'key' => 'PP', 'label' => 'A.  PERSEPULUHAN', 'prefix' => 'A.'],
            ['type' => 'row', 'key' => 'IURAN_PEJABAT', 'label' => 'B.  IURAN PEJABAT', 'prefix' => 'B.'],
            ['type' => 'row', 'key' => 'DANA_MISI', 'label' => 'C.  DANA MISI DAN PENDIDIKAN', 'prefix' => 'C.'],
            ['type' => 'row', 'key' => 'PENERIMAAN_LAIN', 'label' => 'D.  PENERIMAAN LAIN-LAIN', 'prefix' => 'D.'],
            ['type' => 'row', 'key' => 'BUNGA_BANK', 'label' => 'E.  BUNGA BANK', 'prefix' => 'E.'],
            ['type' => 'subtotal', 'label' => 'TOTAL PENERIMAAN DANA', 'group' => 'income'],
            ['type' => 'section_header', 'label' => 'PENGELUARAN DANA :'],
            ['type' => 'group_header', 'label' => 'F.  BIAYA MAJELIS DAERAH :'],
            ['type' => 'row', 'key' => 'SETORAN_PP_MP', 'label' => '1.  SETORAN PERSEPULUHAN KE MP', 'group' => 'expense_majelis', 'no' => 1],
            ['type' => 'row', 'key' => 'SETORAN_IURAN_MP', 'label' => '2.  SETORAN IURAN PEJABAT KE MP', 'group' => 'expense_majelis', 'no' => 2],
            ['type' => 'row', 'key' => 'BIAYA_MIKERIA', 'label' => '3.  BIAYA MUSDA, MUKERDA, UPGRADING', 'group' => 'expense_majelis', 'no' => 3],
            ['type' => 'row', 'key' => 'BIAYA_PASKAH_NATAL', 'label' => '4.  BIAYA PASKAH, HUT, NATAL', 'group' => 'expense_majelis', 'no' => 4],
            ['type' => 'row', 'key' => 'BIAYA_RAPAT', 'label' => '5.  BIAYA RAPAT', 'group' => 'expense_majelis', 'no' => 5],
            ['type' => 'row', 'key' => 'BIAYA_KURIKULUM', 'label' => '6.  BIAYA KUNJUNGAN KERJA/DINAS', 'group' => 'expense_majelis', 'no' => 6],
            ['type' => 'row', 'key' => 'BIAYA_SEKRETARIAT', 'label' => '7.  BIAYA SEKRETARIAT', 'group' => 'expense_majelis', 'no' => 7],
            ['type' => 'row', 'key' => 'BIAYA_LAIN_MAJELIS', 'label' => '8.  BIAYA LAIN-LAIN', 'group' => 'expense_majelis', 'no' => 8],
            ['type' => 'row', 'key' => 'BIAYA_ADMIN_BANK', 'label' => '9.  BIAYA ADMINISTRASI BANK', 'group' => 'expense_majelis', 'no' => 9],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA MAJELIS DAERAH', 'group' => 'expense_majelis'],
            ['type' => 'group_header', 'label' => 'G.  BIAYA BIDANG PENATALAYANAN'],
            ['type' => 'row', 'key' => 'BIAYA_HAL', 'label' => '1.  BIAYA HUBUNGAN ANTAR LEMBAGA', 'group' => 'expense_penatalayanan', 'no' => 1],
            ['type' => 'row', 'key' => 'BIAYA_BUKU_PENATALAYANAN', 'label' => '2.  BIAYA PEMBELIAN BUKU PENATALAYANAN', 'group' => 'expense_penatalayanan', 'no' => 2],
            ['type' => 'row', 'key' => 'BIAYA_PENATALAYANAN_LAIN', 'label' => '3.  BIAYA LAIN-LAIN', 'group' => 'expense_penatalayanan', 'no' => 3],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG PENATALAYANAN', 'group' => 'expense_penatalayanan'],
            ['type' => 'group_header', 'label' => 'H.  BIAYA BIDANG PENGGEMBALAAN'],
            ['type' => 'row', 'key' => 'BIAYA_SEKOLAH_MINGGU', 'label' => '1.  BIAYA PROGRAM SEKOLAH MINGGU', 'group' => 'expense_penggembalaan', 'no' => 1],
            ['type' => 'row', 'key' => 'BIAYA_PEMUDA', 'label' => '2.  BIAYA PROGRAM PEMUDA', 'group' => 'expense_penggembalaan', 'no' => 2],
            ['type' => 'row', 'key' => 'BIAYA_WANITA', 'label' => '3.  BIAYA PROGRAM WANITA', 'group' => 'expense_penggembalaan', 'no' => 3],
            ['type' => 'row', 'key' => 'BIAYA_CAMP_SEMINAR', 'label' => '4.  BIAYA CAMP/SEMINAR HAMBA-HAMBA TUHAN', 'group' => 'expense_penggembalaan', 'no' => 4],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG PENGGEMBALAAN', 'group' => 'expense_penggembalaan'],
            ['type' => 'group_header', 'label' => 'I.   BIAYA BIDANG PENDIDIKAN DAN MISI'],
            ['type' => 'row', 'key' => 'SETORAN_DANA_MISI_MP', 'label' => '1.  SETORAN DANA MISI DAN PENDIDIKAN KE MP', 'group' => 'expense_misi', 'no' => 1],
            ['type' => 'row', 'key' => 'BIAYA_MISI_SEMINAR', 'label' => '2.  BIAYA MISI, SEMINAR MISI MD', 'group' => 'expense_misi', 'no' => 2],
            ['type' => 'row', 'key' => 'BIAYA_PENDIDIKAN_MD', 'label' => '3.  BIAYA PENDIDIKAN MD', 'group' => 'expense_misi', 'no' => 3],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG MISI DAN PENDIDIKAN', 'group' => 'expense_misi'],
            ['type' => 'group_header', 'label' => 'J.   BIAYA BIDANG PELAYANAN MASYARAKAT'],
            ['type' => 'row', 'key' => 'BANTUAN_SAKIT', 'label' => '1.  BANTUAN SAKIT (TERMASUK BPJS)', 'group' => 'expense_pelayanan', 'no' => 1],
            ['type' => 'row', 'key' => 'BANTUAN_DUKACITA', 'label' => '2.  BANTUAN DUKACITA', 'group' => 'expense_pelayanan', 'no' => 2],
            ['type' => 'row', 'key' => 'BANTUAN_PEMBANGUNAN', 'label' => '3.  BANTUAN PEMBANGUNAN GEREJA', 'group' => 'expense_pelayanan', 'no' => 3],
            ['type' => 'row', 'key' => 'BANTUAN_BENCANA', 'label' => '4.  BANTUAN BENCANA ALAM', 'group' => 'expense_pelayanan', 'no' => 4],
            ['type' => 'row', 'key' => 'BIAYA_MULTIMEDIA', 'label' => '5.  BIAYA BIDANG IT DAN MULTI MEDIA', 'group' => 'expense_pelayanan', 'no' => 5],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG PELAYANAN MASYARAKAT', 'group' => 'expense_pelayanan'],
            ['type' => 'grand_total_expense', 'label' => 'TOTAL PENGELUARAN DANA'],
            ['type' => 'grand_total_saldo', 'label' => 'SALDO AKHIR PER 31 DESEMBER'],
        ];
    }

    private function groupKeyMap(): array
    {
        return [
            'income' => ['PP', 'IURAN_PEJABAT', 'DANA_MISI', 'PENERIMAAN_LAIN', 'BUNGA_BANK'],
            'expense_majelis' => [
                'IURAN_MDF', 'TAHAKA', 'ADMIN_BANK', 'PENGELUARAN_LAIN',
            ],
            'expense_penatalayanan' => ['BIAYA_HAL', 'BIAYA_BUKU_PENATALAYANAN', 'BIAYA_PENATALAYANAN_LAIN'],
            'expense_penggembalaan' => ['BIAYA_SEKOLAH_MINGGU', 'BIAYA_PEMUDA', 'BIAYA_WANITA', 'BIAYA_CAMP_SEMINAR'],
            'expense_misi' => ['SETORAN_DANA_MISI_MP', 'BIAYA_MISI_SEMINAR', 'BIAYA_PENDIDIKAN_MD'],
            'expense_pelayanan' => [
                'BPJS', 'BANTUAN_SAKIT', 'BANTUAN_DUKACITA', 'BANTUAN_PEMBANGUNAN', 'BANTUAN_BENCANA', 'BIAYA_MULTIMEDIA',
            ],
        ];
    }

    public static function isAdmin(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->hasPermission('finance.export.advanced')
            || $user->roles->contains(
                fn($role) => in_array(strtolower((string) ($role->slug ?? $role->name ?? '')), ['administrator', 'admin'], true)
            );
    }

    public static function availableTypes(): array
    {
        $all = [
            'all_data' => ['label' => 'Laporan Keuangan (Seluruh Data)', 'icon' => 'ri-file-excel-2-line', 'method' => 'exportAllData', 'admin' => false],
            'template' => ['label' => 'Export dengan Template…', 'icon' => 'ri-layout-grid-line', 'method' => 'exportByTemplate', 'admin' => false, 'modal' => true],
            'lapkeu_md' => ['label' => 'Form Lapkeu MD', 'icon' => 'ri-file-list-3-line', 'method' => 'exportFormLapkeuMD', 'admin' => true],
            'pemasukan_jabar' => ['label' => 'Daftar Pemasukan Jabar', 'icon' => 'ri-money-dollar-circle-line', 'method' => 'exportDaftarPemasukanJabar', 'admin' => true],
            'pdf' => ['label' => 'Laporan Keuangan (PDF)', 'icon' => 'ri-file-pdf-line', 'method' => 'exportLaporanKeuanganPDF', 'admin' => true],
        ];

        $isAdmin = self::isAdmin();

        return array_filter(
            $all,
            fn($type) => method_exists(self::class, $type['method']) && ($isAdmin || !$type['admin'])
        );
    }

    public static function fieldDefs(): array
    {
        return self::FIELDS;
    }

    private function authorizeType(string $key): void
    {
        abort_unless(isset(self::availableTypes()[$key]), 403, 'Tipe export tidak diizinkan.');
    }

    public function run(Request $request)
    {
        $request->validate(['type' => 'required|string']);

        $types = self::availableTypes();
        abort_unless(isset($types[$request->type]), 403, 'Tipe export tidak diizinkan.');

        return $this->{$types[$request->type]['method']}($request);
    }

    private function visibleTemplates()
    {
        return ExportTemplate::where(
            fn($q) => $q->where('user_id', auth()->id())->orWhere('is_shared', true)
        );
    }

    private function editableTemplate($id): ExportTemplate
    {
        $template = $this->visibleTemplates()->findOrFail($id);

        abort_unless(
            $template->user_id === auth()->id() || (self::isAdmin() && $template->is_shared),
            403
        );

        return $template;
    }

    public function templateIndex()
    {
        $userId = auth()->id();
        $admin = self::isAdmin();

        return response()->json(
            $this->visibleTemplates()
                ->orderBy('name')
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'config' => $t->config,
                    'is_shared' => (bool) $t->is_shared,
                    'can_edit' => $t->user_id === $userId || ($admin && $t->is_shared),
                ])
                ->values()
        );
    }

    public function templateStore(Request $request)
    {
        $data = $this->validateTemplate($request);

        $template = ExportTemplate::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'is_shared' => self::isAdmin() && $request->boolean('is_shared'),
            'config' => $data['config'],
        ]);

        return response()->json(['success' => true, 'template' => $template]);
    }

    public function templateUpdate(Request $request, $id)
    {
        $template = $this->editableTemplate($id);
        $data = $this->validateTemplate($request, $template->id);

        $template->update([
            'name' => $data['name'],
            'is_shared' => self::isAdmin() && $request->boolean('is_shared'),
            'config' => $data['config'],
        ]);

        return response()->json(['success' => true, 'template' => $template]);
    }

    public function templateDestroy($id)
    {
        $this->editableTemplate($id)->delete();

        return response()->json(['success' => true]);
    }

    private function validateTemplate(Request $request, ?int $ignoreId = null): array
    {
        $numeric = array_keys(array_filter(self::FIELDS, fn($field) => $field[1] === 'number'));

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('export_templates', 'name')->where('user_id', auth()->id())->ignore($ignoreId),
            ],
            'is_shared' => 'nullable|boolean',
            'config.mode' => ['required', Rule::in(['detail', 'summary'])],
            'config.header.lines' => 'nullable|array|max:6',
            'config.header.lines.*' => 'nullable|string|max:200',
            'config.footer.enabled' => 'required|boolean',
            'config.footer.lines' => 'nullable|array|max:6',
            'config.footer.lines.*' => 'nullable|string|max:200',

            'config.columns' => 'required_if:config.mode,detail|nullable|array|max:20',
            'config.columns.*.key' => ['required', 'distinct', Rule::in(array_keys(self::FIELDS))],
            'config.columns.*.label' => 'required|string|max:60',
            'config.columns.*.width' => 'nullable|integer|min:5|max:80',

            'config.rows' => 'nullable|array|max:2',
            'config.rows.*' => ['distinct', Rule::in(self::DIMS)],
            'config.values' => 'required_if:config.mode,summary|nullable|array|max:15',
            'config.values.*.key' => ['required', Rule::in($numeric)],
            'config.values.*.label' => 'required|string|max:60',
            'config.values.*.agg' => ['required', Rule::in(self::AGGS)],
            'config.values.*.per' => ['required', Rule::in(self::BUCKETS)],
            'config.values.*.sub' => ['nullable', Rule::in(['quarter', 'semester', 'year'])],
            'config.values.*.fmt' => ['nullable', Rule::in(['int', 'dec', 'rp'])],
            'config.layout' => ['nullable', Rule::in(['by_value', 'by_period', 'period_as_rows'])],
            'config.sort' => ['nullable', Rule::in(['label', 'value_desc'])],
            'config.sheet_by' => ['nullable', Rule::in(['none', 'branch', 'year'])],
            'config.fill_empty' => 'nullable|boolean',
            'config.hide_empty' => 'nullable|boolean',

            'config.totals.enabled' => 'nullable|boolean',
            'config.totals.row' => 'nullable|boolean',
            'config.totals.col' => 'nullable|boolean',
            'config.totals.sub' => 'nullable|boolean',
            'config.totals.position' => ['nullable', Rule::in(['top', 'bottom'])],
            'config.totals.label' => 'nullable|string|max:40',
        ]);
    }

    private function writePeriodAsRowsSheet($sheet, array $config, array $rows, string $sheetLabel): void
    {
        $thin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $value = $config['values'][0];
        $per = $value['per'] ?? 'month';
        $agg = $value['agg'] ?? 'sum';
        $fmt = $value['fmt'] ?? 'rp';

        $timestamps = array_map(fn($r) => $r['eff_date']->timestamp, $rows);
        $min = Carbon::createFromTimestamp(min($timestamps));
        $max = Carbon::createFromTimestamp(max($timestamps));

        $present = [];
        foreach ($rows as $row) {
            $present[$this->bucketKey($per, $row['eff_date'])] = true;
        }
        $keys = $this->bucketSequence($per, $min, $max, array_keys($present), (bool) ($config['fill_empty'] ?? true));

        // Header
        $row = 1;
        foreach (data_get($config, 'header.lines', []) as $i => $line) {
            $line = trim(strtr((string) $line, [
                '{periode}' => $min->copy()->locale('id')->translatedFormat('M Y') . ' – ' . $max->copy()->locale('id')->translatedFormat('M Y'),
                '{cabang}' => $sheetLabel,
                '{tanggal_cetak}' => now()->format('d/m/Y'),
                '{pengguna}' => auth()->user()->name ?? '',
            ]));
            if ($line === '') continue;
            $sheet->mergeCells("A{$row}:B{$row}");
            $this->putText($sheet, "A{$row}", $line);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize($i === 0 ? 14 : 11);
            $row++;
        }
        if ($row > 1) $row++;

        $header = $row;
        $this->putText($sheet, "A{$header}", 'Bulan');
        $this->putText($sheet, "B{$header}", $value['label'] ?? 'Total');
        $sheet->getStyle("A{$header}:B{$header}")->applyFromArray($thin + [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A9D08E']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $row++;

        $bodyStart = $row;
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$this->bucketKey($per, $r['eff_date'])][] = $r;
        }

        $grand = 0;
        foreach ($keys as $key) {
            $subset = $indexed[$key] ?? [];
            $amount = $this->aggregate($subset, $value['key'], $agg) ?? 0;
            $grand += $amount;

            $this->putText($sheet, "A{$row}", $this->bucketLabel($per, $key));
            $sheet->setCellValue("B{$row}", $amount);
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($thin);
            $row++;
        }

        // Total
        if (!empty($config['totals']['row'] ?? $config['totals']['enabled'] ?? true)) {
            $this->putText($sheet, "A{$row}", $config['totals']['label'] ?? 'TOTAL');
            $sheet->setCellValue("B{$row}", $grand);
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($thin + [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            ]);
        }

        $code = ['int' => '#,##0', 'dec' => '#,##0.00', 'rp' => self::ACC][$fmt] ?? '#,##0.00';
        $sheet->getStyle("B{$bodyStart}:B{$row}")->getNumberFormat()->setFormatCode($code);
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(20);
    }

    public function exportAllData(Request $request)
    {
        $reports = $this->scopedReports($request);

        if ($reports->isEmpty()) {
            return back()->with('error', 'Tidak ada laporan disetujui untuk filter tersebut.');
        }

        $periods = $this->buildPeriods($reports);
        $multi = collect($periods)->pluck('branch')->unique()->count() > 1;

        $spreadsheet = $this->newSpreadsheet();

        foreach ($periods as $period) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->sheetTitle($spreadsheet, $period, $multi));
            $this->writeLaporanSheet($sheet, $period, $multi);
        }

        $suffix = $request->filled('year') ? '_' . $request->year : '';

        return $this->download($spreadsheet, "LAPORAN_KEUANGAN_MDF_IV_JABAR{$suffix}.xlsx");
    }

    private function writeLaporanSheet($sheet, array $period, bool $multi): void
    {
        $thin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $yellow = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']];

        $title = 'LAPORAN KEUANGAN MDF IV JABAR ' . strtoupper($this->periodLabel($period))
            . ($multi ? ' - ' . strtoupper($period['branch']) : '');

        $sheet->mergeCells('A1:P2');
        $this->putText($sheet, 'A1', $title);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        foreach (['A' => 'Tanggal', 'B' => 'Uraian', 'C' => 'Penanggung Jawab', 'D' => 'Gereja', 'P' => 'Saldo'] as $col => $label) {
            $sheet->mergeCells("{$col}3:{$col}4");
            $this->putText($sheet, "{$col}3", $label);
        }

        $sheet->mergeCells('E3:I3');
        $this->putText($sheet, 'E3', 'Debit');
        $sheet->mergeCells('J3:O3');
        $this->putText($sheet, 'J3', 'Kredit');

        foreach (self::RAPI_COLS as $col => $key) {
            $this->putText($sheet, "{$col}4", self::FIELDS[$key][0]);
        }

        $sheet->getStyle('A3:P4')->applyFromArray($thin + [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A9D08E']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
        ]);

        $this->putText($sheet, 'B5', 'Saldo Awal');
        $sheet->setCellValue('P5', $period['opening']);
        $sheet->getStyle('A5:P5')->applyFromArray($thin + ['fill' => $yellow]);
        $sheet->getStyle('B5')->getFont()->setBold(true);

        $r = 6;

        foreach ($period['rows'] as $row) {
            if ($row['date']) {
                $sheet->setCellValue("A{$r}", XlDate::PHPToExcel($row['date']->toDateTime()));
            }

            foreach (['B' => 'description', 'C' => 'pic', 'D' => 'church'] as $col => $key) {
                if (!empty($row[$key])) {
                    $this->putText($sheet, "{$col}{$r}", $row[$key]);
                }
            }

            $terms = '';

            foreach (self::RAPI_COLS as $col => $key) {
                if (empty($row[$key])) {
                    continue;
                }

                $sheet->setCellValue("{$col}{$r}", $row[$key]);
                $terms .= (in_array($col, ['E', 'F', 'G', 'H', 'I']) ? '+' : '-') . "{$col}{$r}";
            }

            $sheet->setCellValue("P{$r}", '=P' . ($r - 1) . $terms);

            if (!empty($row['tahaka'])) {
                $sheet->getStyle("A{$r}:P{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FF0000');
            }

            $r++;
        }

        $last = $r - 1;

        if ($last >= 6) {
            $sheet->getStyle("A6:P{$last}")->applyFromArray($thin);
            $sheet->getStyle("A6:A{$last}")->getNumberFormat()->setFormatCode('d\-mmm');
            $sheet->getStyle("A6:A{$last}")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("B6:D{$last}")->getAlignment()->setWrapText(true)->setHorizontal('left');
        }

        $t = $r;

        for ($c = 5; $c <= 15; $c++) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $sheet->setCellValue("{$letter}{$t}", "=SUM({$letter}6:{$letter}{$last})");
        }

        $sheet->setCellValue("P{$t}", '=P5+F' . ($t + 4) . '-G' . ($t + 4));
        $sheet->getStyle("A{$t}:P{$t}")->applyFromArray($thin + ['fill' => $yellow]);

        $this->putText($sheet, 'F' . ($t + 3), 'Total Debit');
        $this->putText($sheet, 'G' . ($t + 3), 'Total Kredit');
        $sheet->setCellValue('F' . ($t + 4), "=SUM(E{$t}:I{$t})");
        $sheet->setCellValue('G' . ($t + 4), "=SUM(J{$t}:O{$t})");

        $sheet->getStyle('F' . ($t + 3) . ':G' . ($t + 3))->applyFromArray($thin + [
            'fill' => $yellow,
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getStyle('F' . ($t + 4) . ':G' . ($t + 4))->applyFromArray($thin);
        $sheet->getStyle('E5:P' . ($t + 4))->getNumberFormat()->setFormatCode(self::ACC);

        $sheet->getColumnDimension('A')->setWidth(9);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(52);
        $sheet->getColumnDimension('D')->setWidth(26);

        foreach (range('E', 'O') as $col) {
            $sheet->getColumnDimension($col)->setWidth(19);
        }

        $sheet->getColumnDimension('P')->setWidth(22);
        $sheet->freezePane('C5');
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getSheetView()->setZoomScale(100);
    }

    public function exportByTemplate(Request $request)
    {
        $request->validate(['template_id' => 'required|integer']);

        $template = $this->visibleTemplates()->findOrFail($request->template_id);

        $reports = $this->scopedReports($request);

        if ($reports->isEmpty()) {
            return back()->with('error', 'Tidak ada laporan disetujui untuk filter tersebut.');
        }

        $config = $template->config;

        if (($config['mode'] ?? 'detail') === 'summary') {
            return $this->exportSummary($config, $reports, $template->name);
        }

        $periods = $this->buildPeriods($reports);
        $multi = collect($periods)->pluck('branch')->unique()->count() > 1;

        $spreadsheet = $this->newSpreadsheet();

        foreach ($periods as $period) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->sheetTitle($spreadsheet, $period, $multi));
            $this->writeTemplateSheet($sheet, $config, $period);
        }

        return $this->download($spreadsheet, 'EXPORT_' . Str::upper(Str::slug($template->name, '_')) . '.xlsx');
    }

    private function fillPlaceholders(string $text, array $period): string
    {
        return strtr($text, [
            '{periode}' => $this->periodLabel($period),
            '{cabang}' => $period['branch'],
            '{tanggal_cetak}' => now()->format('d/m/Y'),
            '{pengguna}' => auth()->user()->name ?? '',
        ]);
    }

    private function writeTemplateSheet($sheet, array $config, array $period): void
    {
        $cols = $config['columns'];
        $count = count($cols);
        $lastCol = Coordinate::stringFromColumnIndex($count);
        $thin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        $row = 1;

        foreach (data_get($config, 'header.lines', []) as $i => $line) {
            $line = trim($this->fillPlaceholders((string) $line, $period));

            if ($line === '') {
                continue;
            }

            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $this->putText($sheet, "A{$row}", $line);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => $i === 0 ? 14 : 11],
                'alignment' => ['horizontal' => 'center'],
            ]);
            $row++;
        }

        if ($row > 1) {
            $row++;
        }

        $header = $row;

        foreach ($cols as $i => $col) {
            $letter = Coordinate::stringFromColumnIndex($i + 1);
            $this->putText($sheet, "{$letter}{$header}", $col['label']);
            $sheet->getColumnDimension($letter)->setWidth($col['width'] ?? self::FIELDS[$col['key']][2]);
        }

        $sheet->getStyle("A{$header}:{$lastCol}{$header}")->applyFromArray($thin + [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A9D08E']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
        ]);
        $row++;

        $totalsEnabled = (bool) data_get($config, 'totals.enabled');
        $totalsOnTop = data_get($config, 'totals.position', 'bottom') === 'top';
        $start = $row + ($totalsEnabled && $totalsOnTop ? 1 : 0);
        $end = $start + count($period['rows']) - 1;
        $totalRow = $totalsEnabled ? ($totalsOnTop ? $row : $end + 1) : null;

        $r = $start;

        foreach ($period['rows'] as $data) {
            foreach ($cols as $i => $col) {
                $letter = Coordinate::stringFromColumnIndex($i + 1);
                $value = $data[$col['key']] ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                $type = self::FIELDS[$col['key']][1];

                if ($type === 'date') {
                    $sheet->setCellValue("{$letter}{$r}", XlDate::PHPToExcel($value->toDateTime()));
                    $sheet->getStyle("{$letter}{$r}")->getNumberFormat()->setFormatCode('d\-mmm\-yyyy');
                } elseif ($type === 'number') {
                    $sheet->setCellValue("{$letter}{$r}", $value);
                    $sheet->getStyle("{$letter}{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
                } else {
                    $this->putText($sheet, "{$letter}{$r}", $value);
                }
            }

            $r++;
        }

        $tableEnd = $end;

        if ($totalsEnabled) {
            $tableEnd = max($end, $totalRow);

            foreach ($cols as $i => $col) {
                $letter = Coordinate::stringFromColumnIndex($i + 1);
                $summable = self::FIELDS[$col['key']][1] === 'number' && $col['key'] !== 'balance';

                if ($summable && count($period['rows']) > 0) {
                    $sheet->setCellValue("{$letter}{$totalRow}", "=SUM({$letter}{$start}:{$letter}{$end})");
                    $sheet->getStyle("{$letter}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }
            }

            if (self::FIELDS[$cols[0]['key']][1] !== 'number') {
                $this->putText($sheet, "A{$totalRow}", data_get($config, 'totals.label', 'TOTAL') ?: 'TOTAL');
            }

            $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            ]);
        }

        $sheet->getStyle("A{$header}:{$lastCol}{$tableEnd}")->applyFromArray($thin);

        if (data_get($config, 'footer.enabled')) {
            $f = $tableEnd + 2;

            foreach (data_get($config, 'footer.lines', []) as $line) {
                $line = trim($this->fillPlaceholders((string) $line, $period));

                if ($line === '') {
                    continue;
                }

                $this->putText($sheet, "A{$f}", $line);
                $sheet->getStyle("A{$f}")->getFont()->setItalic(true);
                $f++;
            }
        }

        $sheet->freezePane('A' . ($header + 1));
        $sheet->getPageSetup()
            ->setOrientation($count > 6 ? 'landscape' : 'portrait')
            ->setFitToWidth(1)
            ->setFitToHeight(0);
    }

    private function bucketKey(string $per, Carbon $date): string
    {
        return match ($per) {
            'week' => $date->isoWeekYear . '-W' . str_pad((string) $date->isoWeek, 2, '0', STR_PAD_LEFT),
            'month' => $date->format('Y-m'),
            'quarter' => $date->year . '-Q' . $date->quarter,
            'semester' => $date->year . '-S' . ($date->month <= 6 ? 1 : 2),
            'year' => (string) $date->year,
            default => 'ALL',
        };
    }

    private function bucketLabel(string $per, string $key): string
    {
        return match ($per) {
            'week' => 'W' . substr($key, -2) . ' ' . substr($key, 0, 4),
            'month' => Carbon::createFromFormat('Y-m-d', $key . '-01')->locale('id')->translatedFormat('M Y'),
            'quarter' => substr($key, -2) . ' ' . substr($key, 0, 4),
            'semester' => 'Sem ' . substr($key, -1) . ' ' . substr($key, 0, 4),
            'year' => $key,
            default => 'Total',
        };
    }

    private function keyToDate(string $per, string $key): Carbon
    {
        return match ($per) {
            'month' => Carbon::createFromFormat('Y-m-d', $key . '-01')->startOfDay(),
            'quarter' => Carbon::create((int) substr($key, 0, 4), ((int) substr($key, -1) - 1) * 3 + 1, 1),
            'semester' => Carbon::create((int) substr($key, 0, 4), ((int) substr($key, -1) - 1) * 6 + 1, 1),
            'year' => Carbon::create((int) $key, 1, 1),
            default => Carbon::now(),
        };
    }

    private function bucketSequence(string $per, Carbon $min, Carbon $max, array $present, bool $fill): array
    {
        if ($per === 'all') {
            return ['ALL'];
        }

        sort($present);

        if (!$fill) {
            return $present;
        }

        $cursor = match ($per) {
            'week' => $min->copy()->startOfWeek(),
            'month' => $min->copy()->startOfMonth(),
            'quarter' => Carbon::create($min->year, ($min->quarter - 1) * 3 + 1, 1),
            'semester' => Carbon::create($min->year, $min->month <= 6 ? 1 : 7, 1),
            default => Carbon::create($min->year, 1, 1),
        };

        $endKey = $this->bucketKey($per, $max);
        $keys = [];

        for ($i = 0; $i < 1500; $i++) {
            $key = $this->bucketKey($per, $cursor);
            $keys[] = $key;

            if ($key >= $endKey) {
                break;
            }

            match ($per) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonthNoOverflow(),
                'quarter' => $cursor->addMonthsNoOverflow(3),
                'semester' => $cursor->addMonthsNoOverflow(6),
                default => $cursor->addYear(),
            };
        }

        return array_values(array_unique($keys));
    }

    private function aggregate(array $rows, string $field, string $agg): ?float
    {
        $values = [];

        foreach ($rows as $row) {
            $value = $row[$field] ?? null;

            if ($value === null || ($value == 0 && $field !== 'balance')) {
                continue;
            }

            $values[] = (float) $value;
        }

        if (!$values) {
            return null;
        }

        return match ($agg) {
            'count' => (float) count($values),
            'avg' => array_sum($values) / count($values),
            'min' => min($values),
            'max' => max($values),
            'last' => end($values),
            default => array_sum($values),
        };
    }

    private function flatRows($reports): array
    {
        $rows = [];

        foreach (array_reverse($this->buildPeriods($reports)) as $period) {
            foreach ($period['rows'] as $row) {
                $row['eff_date'] = $row['date'] ?? Carbon::create($period['year'], $period['month'], 1);
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function exportSummary(array $config, $reports, string $name)
    {
        $rows = $this->flatRows($reports);

        $groups = match ($config['sheet_by'] ?? 'none') {
            'branch' => collect($rows)->groupBy(fn($r) => $r['branch'] ?: 'Tanpa MD')->map->all()->all(),
            'year' => collect($rows)->groupBy(fn($r) => (string) $r['eff_date']->year)->sortKeys()->map->all()->all(),
            default => ['Ringkasan' => $rows],
        };

        $spreadsheet = $this->newSpreadsheet();

        foreach ($groups as $label => $groupRows) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->uniqueTitle($spreadsheet, (string) $label));
            $this->writeSummarySheet($sheet, $config, array_values($groupRows), (string) $label);
        }

        return $this->download($spreadsheet, 'RINGKASAN_' . Str::upper(Str::slug($name, '_')) . '.xlsx');
    }

    private function writeSummarySheet($sheet, array $config, array $rows, string $sheetLabel): void
    {
        $thin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
        $values = array_values($config['values'] ?? []);
        $dims = array_values($config['rows'] ?? []);
        $totals = $config['totals'] ?? [];
        $labelCols = max(1, count($dims));

        if (!$rows || !$values) {
            $this->putText($sheet, 'A1', 'Tidak ada data.');
            return;
        }

        if (($config['layout'] ?? '') === 'period_as_rows') {
            $this->writePeriodAsRowsSheet($sheet, $config, $rows, $sheetLabel);
            return;
        }

        $timestamps = array_map(fn($r) => $r['eff_date']->timestamp, $rows);
        $min = Carbon::createFromTimestamp(min($timestamps));
        $max = Carbon::createFromTimestamp(max($timestamps));
        $rangeLabel = $min->copy()->locale('id')->translatedFormat('M Y')
            . ' – '
            . $max->copy()->locale('id')->translatedFormat('M Y');

        $pers = array_values(array_unique(array_merge(
            array_column($values, 'per'),
            array_filter(array_column($values, 'sub'))
        )));

        $seq = [];

        foreach ($pers as $per) {
            $present = [];

            foreach ($rows as $row) {
                $present[$this->bucketKey($per, $row['eff_date'])] = true;
            }

            $seq[$per] = $this->bucketSequence($per, $min, $max, array_keys($present), (bool) ($config['fill_empty'] ?? true));
        }

        $rank = ['week' => 0, 'month' => 1, 'quarter' => 2, 'semester' => 3, 'year' => 4, 'all' => 0];

        $segments = function (string $per, ?string $sub, bool $withTotal) use ($seq, $rank) {
            $out = [];
            $keys = $seq[$per] ?? ['ALL'];
            $useSub = $sub && in_array($per, ['month', 'quarter', 'semester'], true) && $rank[$sub] > $rank[$per];

            foreach ($keys as $i => $key) {
                $out[] = ['kind' => 'bucket', 'per' => $per, 'key' => $key, 'label' => $this->bucketLabel($per, $key)];

                if (!$useSub) {
                    continue;
                }

                $parent = $this->bucketKey($sub, $this->keyToDate($per, $key));
                $next = $keys[$i + 1] ?? null;

                if ($next === null || $this->bucketKey($sub, $this->keyToDate($per, $next)) !== $parent) {
                    $out[] = ['kind' => 'sub', 'per' => $sub, 'key' => $parent, 'label' => 'Σ ' . $this->bucketLabel($sub, $parent)];
                }
            }

            if ($withTotal && $per !== 'all') {
                $out[] = ['kind' => 'total', 'per' => 'all', 'key' => 'ALL', 'label' => 'Total'];
            }

            return $out;
        };

        $withTotal = (bool) ($totals['col'] ?? true);
        $sameShape = count(array_unique(array_map(fn($v) => $v['per'] . '|' . ($v['sub'] ?? ''), $values))) === 1;
        $byPeriod = ($config['layout'] ?? 'by_value') === 'by_period' && $sameShape;

        $specs = [];

        if ($byPeriod) {
            foreach ($segments($values[0]['per'], $values[0]['sub'] ?? null, $withTotal) as $segment) {
                foreach ($values as $vi => $value) {
                    $specs[] = ['vi' => $vi, 'seg' => $segment];
                }
            }
        } else {
            foreach ($values as $vi => $value) {
                foreach ($segments($value['per'], $value['sub'] ?? null, $withTotal) as $segment) {
                    $specs[] = ['vi' => $vi, 'seg' => $segment];
                }
            }
        }

        $dimLabel = fn($dim) => self::FIELDS[$dim][0];

        $groups = [];

        foreach ($rows as $row) {
            $keys = $dims
                ? array_map(
                    fn($dim) => ($row[$dim] !== null && $row[$dim] !== '') ? (string) $row[$dim] : '(Tanpa ' . $dimLabel($dim) . ')',
                    $dims
                )
                : ['Semua'];

            $id = implode("\x1f", $keys);
            $groups[$id]['keys'] = $keys;
            $groups[$id]['rows'][] = $row;
        }

        $index = function (array $subset) use ($pers) {
            $indexed = [];

            foreach ($subset as $row) {
                foreach ($pers as $per) {
                    $indexed[$per][$this->bucketKey($per, $row['eff_date'])][] = $row;
                }
            }

            return $indexed;
        };

        $calc = function (array $subset) use ($index, $specs, $values) {
            $indexed = $index($subset);

            return array_map(function ($spec) use ($subset, $indexed, $values) {
                $value = $values[$spec['vi']];
                $set = $spec['seg']['kind'] === 'total'
                    ? $subset
                    : ($indexed[$spec['seg']['per']][$spec['seg']['key']] ?? []);

                return $this->aggregate($set, $value['key'], $value['agg'] ?? 'sum');
            }, $specs);
        };

        $groupRows = [];

        foreach ($groups as $group) {
            $group['cells'] = $calc($group['rows']);
            $groupRows[] = $group;
        }

        if (!empty($config['hide_empty'])) {
            $groupRows = array_values(array_filter(
                $groupRows,
                fn($g) => array_filter($g['cells'], fn($v) => $v !== null && $v != 0)
            ));
        }

        $first = $values[0];
        $metric = fn($group) => $this->aggregate($group['rows'], $first['key'], $first['agg'] ?? 'sum') ?? 0;

        usort($groupRows, function ($a, $b) use ($dims, $config, $metric) {
            if (count($dims) === 2 && ($cmp = strnatcasecmp($a['keys'][0], $b['keys'][0])) !== 0) {
                return $cmp;
            }

            if (($config['sort'] ?? 'label') === 'value_desc') {
                return $metric($b) <=> $metric($a);
            }

            return strnatcasecmp(implode(' ', $a['keys']), implode(' ', $b['keys']));
        });

        $totalCols = $labelCols + count($specs);
        $lastLetter = Coordinate::stringFromColumnIndex($totalCols);
        $placeholders = [
            '{periode}' => $rangeLabel,
            '{cabang}' => $sheetLabel,
            '{tanggal_cetak}' => now()->format('d/m/Y'),
            '{pengguna}' => auth()->user()->name ?? '',
        ];

        $row = 1;

        foreach (data_get($config, 'header.lines', []) as $i => $line) {
            $line = trim(strtr((string) $line, $placeholders));

            if ($line === '') {
                continue;
            }

            $sheet->mergeCells("A{$row}:{$lastLetter}{$row}");
            $this->putText($sheet, "A{$row}", $line);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => $i === 0 ? 14 : 11],
                'alignment' => ['horizontal' => 'center'],
            ]);
            $row++;
        }

        if ($row > 1) {
            $row++;
        }

        $h1 = $row;
        $h2 = $row + 1;

        for ($i = 0; $i < $labelCols; $i++) {
            $letter = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->mergeCells("{$letter}{$h1}:{$letter}{$h2}");
            $this->putText($sheet, "{$letter}{$h1}", isset($dims[$i]) ? $dimLabel($dims[$i]) : 'Keterangan');
            $sheet->getColumnDimension($letter)->setWidth(30);
        }

        $topKey = fn($s) => $byPeriod ? $s['seg']['kind'] . $s['seg']['key'] : 'v' . $s['vi'];
        $topLabel = fn($s) => $byPeriod ? $s['seg']['label'] : $values[$s['vi']]['label'];
        $subLabel = fn($s) => $byPeriod ? $values[$s['vi']]['label'] : $s['seg']['label'];

        $start = 0;

        foreach ($specs as $i => $spec) {
            $c = $labelCols + 1 + $i;
            $this->putText($sheet, Coordinate::stringFromColumnIndex($c) . $h2, $subLabel($spec));

            if (!isset($specs[$i + 1]) || $topKey($specs[$i + 1]) !== $topKey($spec)) {
                $from = Coordinate::stringFromColumnIndex($labelCols + 1 + $start);
                $to = Coordinate::stringFromColumnIndex($c);

                if ($from !== $to) {
                    $sheet->mergeCells("{$from}{$h1}:{$to}{$h1}");
                }

                $this->putText($sheet, "{$from}{$h1}", $topLabel($spec));
                $start = $i + 1;
            }
        }

        $sheet->getStyle("A{$h1}:{$lastLetter}{$h2}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A9D08E']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
        ]);

        $writeRow = function (int $r, array $labels, array $cells, ?string $fill = null, bool $bold = false) use ($sheet, $labelCols, $specs) {
            foreach ($labels as $i => $text) {
                if ($text !== '') {
                    $this->putText($sheet, Coordinate::stringFromColumnIndex($i + 1) . $r, $text);
                }
            }

            foreach ($cells as $i => $value) {
                if ($value !== null) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($labelCols + 1 + $i) . $r, $value);
                }
            }

            $range = 'A' . $r . ':' . Coordinate::stringFromColumnIndex($labelCols + count($specs)) . $r;

            if ($fill) {
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fill);
            }

            if ($bold) {
                $sheet->getStyle($range)->getFont()->setBold(true);
            }
        };

        $row = $h2 + 1;
        $bodyStart = $row;
        $showTotal = !empty($totals['row']);
        $totalOnTop = ($totals['position'] ?? 'bottom') === 'top';
        $totalLabels = array_pad([$totals['label'] ?? 'TOTAL'], $labelCols, '');

        if ($showTotal && $totalOnTop) {
            $writeRow($row++, $totalLabels, $calc($rows), 'FFFF00', true);
        }

        $twoLevel = count($dims) === 2;
        $subtotalOn = $twoLevel && !empty($totals['sub']);

        $levelRows = [];

        foreach ($groupRows as $group) {
            $levelRows[$group['keys'][0]] = array_merge($levelRows[$group['keys'][0]] ?? [], $group['rows']);
        }

        $emitSubtotal = function (string $key) use (&$row, $levelRows, $calc, $writeRow) {
            $writeRow($row++, ['Subtotal ' . $key, ''], $calc($levelRows[$key]), 'F2F2F2', true);
        };

        $previous = null;

        foreach ($groupRows as $group) {
            $key = $group['keys'][0];

            if ($subtotalOn && $previous !== null && $previous !== $key) {
                $emitSubtotal($previous);
            }

            $labels = $twoLevel
                ? [$previous === $key ? '' : $key, $group['keys'][1]]
                : [$group['keys'][0]];

            $writeRow($row++, $labels, $group['cells']);
            $previous = $key;
        }

        if ($subtotalOn && $previous !== null) {
            $emitSubtotal($previous);
        }

        if ($showTotal && !$totalOnTop) {
            $writeRow($row++, $totalLabels, $calc($rows), 'FFFF00', true);
        }

        $bodyEnd = $row - 1;

        foreach ($specs as $i => $spec) {
            $value = $values[$spec['vi']];
            $letter = Coordinate::stringFromColumnIndex($labelCols + 1 + $i);
            $format = ($value['agg'] ?? 'sum') === 'count' ? 'int' : ($value['fmt'] ?? 'dec');
            $code = ['int' => '#,##0', 'dec' => '#,##0.00', 'rp' => self::ACC][$format] ?? '#,##0.00';

            $sheet->getStyle("{$letter}{$bodyStart}:{$letter}{$bodyEnd}")->getNumberFormat()->setFormatCode($code);
            $sheet->getColumnDimension($letter)->setWidth(17);

            if ($spec['seg']['kind'] !== 'bucket') {
                $sheet->getStyle("{$letter}{$h1}:{$letter}{$h2}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($spec['seg']['kind'] === 'sub' ? 'FFF2CC' : 'E2EFDA');
            }
        }

        $sheet->getStyle("A{$h1}:{$lastLetter}{$bodyEnd}")->applyFromArray($thin);

        if (data_get($config, 'footer.enabled')) {
            $f = $bodyEnd + 2;

            foreach (data_get($config, 'footer.lines', []) as $line) {
                $line = trim(strtr((string) $line, $placeholders));

                if ($line === '') {
                    continue;
                }

                $this->putText($sheet, "A{$f}", $line);
                $sheet->getStyle("A{$f}")->getFont()->setItalic(true);
                $f++;
            }
        }

        $sheet->freezePane(Coordinate::stringFromColumnIndex($labelCols + 1) . ($h2 + 1));
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
    }

    private function createDaftarPemasukanSheet(Spreadsheet $spreadsheet, $reports, int $year, $branchId = null): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle((string) $year);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(8);

        $months = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        $totalLetter = Coordinate::stringFromColumnIndex(3 + 12 * 2);
        $amountLetter = fn(int $month): string => Coordinate::stringFromColumnIndex(3 + ($month - 1) * 2);
        $noteLetter = fn(int $month): string => Coordinate::stringFromColumnIndex(4 + ($month - 1) * 2);
        $right = Alignment::HORIZONTAL_RIGHT;

        $sheet->mergeCells("A1:{$totalLetter}1");
        $sheet->setCellValue('A1', "DAFTAR PEMASUKAN KAS MDF IV JABAR {$year}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(6);

        $sheet->setCellValue('A3', 'NO.');
        $sheet->setCellValue('B3', 'NAMA');
        $sheet->setCellValue("{$totalLetter}3", 'TOTAL');

        for ($m = 1; $m <= 12; $m++) {
            $jLetter = $amountLetter($m);
            $kLetter = $noteLetter($m);

            $sheet->mergeCells("{$jLetter}3:{$kLetter}3");
            $sheet->setCellValue("{$jLetter}3", $months[$m - 1]);
            $sheet->getStyle("{$jLetter}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("{$jLetter}4", 'JUMLAH');
            $sheet->setCellValue("{$kLetter}4", 'KET.');
        }

        $sheet->getStyle("A3:{$totalLetter}4")->applyFromArray([
            'font' => ['bold' => true, 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);

        $rows = [];

        if ($branchId) {
            $churches = Church::where('branch_id', $branchId)->where('is_active', true)->orderBy('name')->get();

            foreach ($churches as $church) {
                $rows[$church->name . ($church->pic_name ? ' - ' . $church->pic_name : '')] = ['monthly' => [], 'total' => 0];
            }

            $labelOf = fn($report) => $report->church
                ? $report->church->name . ($report->church->pic_name ? ' - ' . $report->church->pic_name : '')
                : 'Tanpa Gereja';
        } else {
            $branches = Branch::where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', 'md_%')
                        ->orWhere('name', 'like', 'mp_%')
                        ->orWhere('name', 'like', 'MD %')
                        ->orWhere('name', 'like', 'MP %');
                })
                ->orderBy('name')
                ->get();

            foreach ($branches as $branch) {
                $rows[$branch->name . ($branch->pic_name ? ' - ' . $branch->pic_name : '')] = ['monthly' => [], 'total' => 0];
            }

            $labelOf = fn($report) => $report->branch
                ? $report->branch->name . ($report->branch->pic_name ? ' - ' . $report->branch->pic_name : '')
                : 'Tanpa MD';
        }

        foreach ($reports as $report) {
            $label = $labelOf($report);

            if (!isset($rows[$label])) {
                $rows[$label] = ['monthly' => [], 'total' => 0];
            }

            $ppAmount = $report->details->where('category_code', 'PP')->sum('amount');

            if ($ppAmount == 0) {
                $ppAmount = (float) ($report->total_income ?? 0);
            }

            $m = $report->report_month ?? 1;
            $rows[$label]['monthly'][$m]['amount'] = ($rows[$label]['monthly'][$m]['amount'] ?? 0) + $ppAmount;
            $rows[$label]['total'] += $ppAmount;
        }

        $dataRow = 5;
        $no = 1;
        $grandMonthly = [];

        foreach ($rows as $name => $data) {
            $sheet->setCellValue("A{$dataRow}", $no);
            $sheet->setCellValue("B{$dataRow}", $name);

            $rowTotal = 0;

            for ($m = 1; $m <= 12; $m++) {
                $jLetter = $amountLetter($m);
                $kLetter = $noteLetter($m);
                $amount = $data['monthly'][$m]['amount'] ?? 0;
                $note = $data['monthly'][$m]['ket'] ?? '';

                if ($amount > 0) {
                    $sheet->setCellValue("{$jLetter}{$dataRow}", $amount);
                    $sheet->getStyle("{$jLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $grandMonthly[$m] = ($grandMonthly[$m] ?? 0) + $amount;
                }

                if ($note !== '') {
                    $sheet->setCellValue("{$kLetter}{$dataRow}", $note);
                }

                $sheet->getStyle("{$jLetter}{$dataRow}")->getAlignment()->setHorizontal($right);
                $rowTotal += $amount;
            }

            if ($rowTotal > 0) {
                $sheet->setCellValue("{$totalLetter}{$dataRow}", $rowTotal);
                $sheet->getStyle("{$totalLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("{$totalLetter}{$dataRow}")->getAlignment()->setHorizontal($right);
                $sheet->getStyle("{$totalLetter}{$dataRow}")->getFont()->setBold(true);
            }

            $sheet->getStyle("A{$dataRow}:{$totalLetter}{$dataRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            $no++;
            $dataRow++;
        }

        $sheet->setCellValue("B{$dataRow}", 'Total');
        $sheet->getStyle("B{$dataRow}")->getFont()->setBold(true);

        $grandTotal = 0;

        for ($m = 1; $m <= 12; $m++) {
            $jLetter = $amountLetter($m);
            $monthTotal = $grandMonthly[$m] ?? 0;

            if ($monthTotal > 0) {
                $sheet->setCellValue("{$jLetter}{$dataRow}", $monthTotal);
                $sheet->getStyle("{$jLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("{$jLetter}{$dataRow}")->getAlignment()->setHorizontal($right);
            }

            $grandTotal += $monthTotal;
        }

        $sheet->setCellValue("{$totalLetter}{$dataRow}", $grandTotal);
        $sheet->getStyle("{$totalLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("{$totalLetter}{$dataRow}")->getAlignment()->setHorizontal($right);
        $sheet->getStyle("A{$dataRow}:{$totalLetter}{$dataRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ]);

        $dataRow++;

        $sheet->setCellValue("B{$dataRow}", 'Perpuluhan MP 35%');
        $sheet->getStyle("B{$dataRow}")->getFont()->setBold(true);

        for ($m = 1; $m <= 12; $m++) {
            $jLetter = $amountLetter($m);
            $pp35 = round(($grandMonthly[$m] ?? 0) * 0.35, 2);

            if ($pp35 > 0) {
                $sheet->setCellValue("{$jLetter}{$dataRow}", $pp35);
                $sheet->getStyle("{$jLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("{$jLetter}{$dataRow}")->getAlignment()->setHorizontal($right);
            }
        }

        $dataRow += 2;

        $sheet->setCellValue("A{$dataRow}", 'Kuning = Sudah ditransfer ke MP');
        $sheet->getStyle("A{$dataRow}")->getFont()->setItalic(true)->setSize(8);

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(38);

        for ($m = 1; $m <= 12; $m++) {
            $sheet->getColumnDimension($amountLetter($m))->setWidth(14);
            $sheet->getColumnDimension($noteLetter($m))->setWidth(8);
        }

        $sheet->getColumnDimension($totalLetter)->setWidth(16);
    }

    public function exportLaporanKeuanganMDFJabar(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
            'branch_id' => 'nullable|integer',
            'church_id' => 'nullable|integer',
        ]);

        $year = (int) $request->input('year');
        $month = $request->input('month') ? (int) $request->input('month') : null;
        $branchId = $request->input('branch_id');
        $churchId = $request->input('church_id');

        $query = FinancialReport::with(['details', 'branch', 'church'])
            ->where('status', 'approved')
            ->where('report_year', $year);

        if ($month) {
            $query->where('report_month', $month);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($churchId) {
            $query->where('church_id', $churchId);
        }

        $reports = $query->orderBy('report_month')->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada laporan disetujui untuk periode tersebut.');
        }

        $spreadsheet = $this->newSpreadsheet(9);

        foreach ($reports->groupBy('branch_id') as $branchReports) {
            if ($month) {
                $this->createBukuKasSheet($spreadsheet, $branchReports, $year, $month);
                continue;
            }

            foreach ($branchReports->pluck('report_month')->unique()->sort() as $m) {
                $this->createBukuKasSheet($spreadsheet, $branchReports->where('report_month', $m), $year, $m);
            }
        }

        $suffix = $month ? strtoupper(Carbon::create($year, $month, 1)->format('F')) . '_' : '';

        return $this->download($spreadsheet, "LAPORAN_KEUANGAN_MDF_IV_JABAR_{$suffix}{$year}.xlsx");
    }

    private function createBukuKasSheet(Spreadsheet $spreadsheet, $reports, int $year, int $reportMonth): void
    {
        $branchName = $reports->first()->branch->name ?? 'MD';
        $date = Carbon::create($year, $reportMonth, 1);
        $monthYear = $date->translatedFormat('F Y');
        $sheetTitle = substr(strtoupper($date->format('M') . ' ' . $year . ' - ' . $branchName), 0, 31);

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetTitle);

        $sheet->mergeCells('A1:R1');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN MDF IV JABAR - ' . strtoupper($branchName) . ' - ' . strtoupper($monthYear));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Tanggal');
        $sheet->setCellValue('B2', 'Uraian');
        $sheet->mergeCells('C2:F2');
        $sheet->setCellValue('C2', 'Debit');
        $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('C3', 'PP');
        $sheet->setCellValue('D3', 'Iuran');
        $sheet->setCellValue('E3', 'Dana Misi');
        $sheet->setCellValue('F3', 'Bunga');
        $sheet->mergeCells('G3:K3');
        $sheet->setCellValue('G3', 'Lain-Lain');
        $sheet->mergeCells('L3:Q3');
        $sheet->setCellValue('L3', 'Kredit');
        $sheet->getStyle('L3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('L4', 'BPJS');
        $sheet->setCellValue('M4', 'Iuran MDF');
        $sheet->setCellValue('N4', 'TAHAKA');
        $sheet->setCellValue('O4', 'Pajak Bunga');
        $sheet->setCellValue('P4', 'Admin');
        $sheet->setCellValue('Q4', 'Lain-Lain');
        $sheet->setCellValue('R2', 'Saldo');

        $sheet->getStyle('A1:R4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);
        $sheet->getStyle('C3:R4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $currencyFormat = '"Rp "#,##0.00;"-Rp "#,##0.00';
        $right = Alignment::HORIZONTAL_RIGHT;

        $row = 5;
        $openingBalance = (float) $reports->sortBy('report_month')->first()->opening_balance;

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'SALDO AWAL');
        $sheet->setCellValue("R{$row}", $openingBalance);
        $sheet->getStyle("R{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("R{$row}")->getAlignment()->setHorizontal($right);
        $sheet->getStyle("A{$row}:R{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
        ]);
        $row++;

        $transactions = [];

        foreach ($reports as $report) {
            foreach ($this->getOrderedTransactions($report) as $tx) {
                $transactions[] = $tx;
            }
        }

        usort($transactions, [$this, 'compareTransactions']);

        $runningBalance = $openingBalance;

        foreach ($transactions as $tx) {
            $dateValue = '';

            if (!empty($tx->transaction_date)) {
                try {
                    $dateValue = Carbon::parse($tx->transaction_date)->format('d-M');
                } catch (\Exception $e) {
                    $dateValue = $tx->transaction_date;
                }
            }

            $debit = (float) ($tx->debit ?? 0);
            $credit = (float) ($tx->credit ?? 0);
            $runningBalance += $debit - $credit;

            $sheet->setCellValue("A{$row}", $dateValue);
            $sheet->setCellValue("B{$row}", $tx->description ?? '');

            foreach (self::BUKU_KAS_DEBIT_COLS as $col => $codes) {
                $sheet->setCellValue("{$col}{$row}", in_array($tx->category_code, $codes) ? $debit : '');
            }

            foreach (self::BUKU_KAS_CREDIT_COLS as $col => $codes) {
                $sheet->setCellValue("{$col}{$row}", in_array($tx->category_code, $codes) ? $credit : '');
            }

            $sheet->setCellValue("R{$row}", $runningBalance);
            $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("C{$row}:R{$row}")->getAlignment()->setHorizontal($right);
            $sheet->getStyle("A{$row}:R{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(45);

        foreach (['C', 'D', 'E', 'F', 'G', 'L', 'M', 'N', 'O', 'P', 'Q'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }

        $sheet->getColumnDimension('R')->setWidth(18);
    }

    private function scopedReports(Request $request)
    {
        $request->validate([
            'year'      => 'nullable|integer|min:2020|max:2035',
            'month'     => 'nullable|integer|min:1|max:12',
            'branch_id' => 'nullable|integer',
            'church_id' => 'nullable|integer',
        ]);

        $query = FinancialReport::with(['details', 'branch', 'church']);

        if ($request->filled('year')) {
            $query->where('report_year', (int) $request->year);
        }

        if ($request->filled('month')) {
            $query->where('report_month', (int) $request->month);
        }

        if ($request->filled('church_id')) {
            $query->where('church_id', (int) $request->church_id);
        }

        if (self::isAdmin()) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', (int) $request->branch_id);
            }
        } else {
            $branchId = auth()->user()?->roles->first()?->branch_id;
            $branchId
                ? $query->where('branch_id', $branchId)
                : $query->whereRaw('1 = 0');
        }

        return $query->orderBy('report_year')->orderBy('report_month')->get();
    }

    private function collectRows($reports): array
    {
        $rows = [];

        foreach ($reports as $report) {
            foreach ($this->getOrderedTransactions($report) as $tx) {
                $debit = (float) ($tx->debit ?? 0);
                $credit = (float) ($tx->credit ?? 0);
                $code = $tx->category_code ?? '';

                $row = array_fill_keys(array_keys(self::FIELDS), null);

                try {
                    $row['date'] = !empty($tx->transaction_date) ? Carbon::parse($tx->transaction_date) : null;
                } catch (\Throwable $e) {
                    $row['date'] = null;
                }

                $row['description'] = $tx->description ?? '';
                $row['church'] = $report->church->name ?? null;
                $row['pic'] = $report->church->pic_name ?? null;
                $row['branch'] = $report->branch->name ?? null;
                $row['category'] = $code;
                $row['debit'] = $debit;
                $row['credit'] = $credit;

                if ($debit > 0) {
                    $row[self::DEBIT_MAP[$code] ?? 'lain_debit'] = $debit;
                }

                if ($credit > 0) {
                    $row[self::CREDIT_MAP[$code] ?? 'lain_kredit'] = $credit;
                }

                $rows[] = $row;
            }
        }

        usort($rows, fn($a, $b) => ($a['date']?->timestamp ?? PHP_INT_MAX) <=> ($b['date']?->timestamp ?? PHP_INT_MAX));

        return $rows;
    }

    private function buildPeriods($reports): array
    {
        $periods = [];
        $groups = $reports->groupBy(fn($r) => $r->branch_id . '|' . $r->report_year . '|' . $r->report_month);

        foreach ($groups as $group) {
            $first = $group->sortBy('id')->first();
            $rows = $this->collectRows($group);
            $opening = (float) $first->opening_balance;
            $balance = $opening;

            foreach ($rows as &$row) {
                $balance += $row['debit'] - $row['credit'];
                $row['balance'] = $balance;
            }
            unset($row);

            $periods[] = [
                'branch' => $first->branch->name ?? 'MD',
                'year' => (int) $first->report_year,
                'month' => (int) $first->report_month,
                'opening' => $opening,
                'rows' => $rows,
            ];
        }

        usort($periods, fn($a, $b) => [$b['year'], $b['month'], $a['branch']] <=> [$a['year'], $a['month'], $b['branch']]);

        return $periods;
    }

    private function periodLabel(array $period): string
    {
        return Carbon::create($period['year'], $period['month'], 1)->locale('id')->translatedFormat('F Y');
    }

    private function uniqueTitle(Spreadsheet $spreadsheet, string $base): string
    {
        $base = mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $base), 0, 28) ?: 'Sheet';
        $title = $base;

        for ($i = 2; $spreadsheet->getSheetByName($title); $i++) {
            $title = $base . ' ' . $i;
        }

        return $title;
    }

    private function sheetTitle(Spreadsheet $spreadsheet, array $period, bool $multi): string
    {
        return $this->uniqueTitle(
            $spreadsheet,
            $this->periodLabel($period) . ($multi ? ' - ' . $period['branch'] : '')
        );
    }

    private function newSpreadsheet(int $fontSize = 11): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize($fontSize);

        return $spreadsheet;
    }

    private function putText($sheet, string $cell, $value): void
    {
        $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
    }

    private function download(Spreadsheet $spreadsheet, string $filename)
    {
        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(
            function () use ($spreadsheet) {
                (new Xlsx($spreadsheet))->save('php://output');
            },
            $filename,
            ['Content-Type' => self::XLSX_MIME],
        );
    }

    private function compareTransactions($a, $b): int
    {
        $aHasDate = !empty($a->transaction_date);
        $bHasDate = !empty($b->transaction_date);

        if ($aHasDate && !$bHasDate) {
            return -1;
        }

        if (!$aHasDate && $bHasDate) {
            return 1;
        }

        if ($aHasDate && $bHasDate) {
            return strcmp($a->transaction_date, $b->transaction_date);
        }

        return strcmp($a->description ?? '', $b->description ?? '');
    }

    private function getOrderedTransactions(FinancialReport $report): array
    {
        $transactions = [];

        if (method_exists($report, 'transactions') && $report->transactions->isNotEmpty()) {
            foreach ($report->transactions as $tx) {
                $transactions[] = (object) [
                    'transaction_date' => $tx->transaction_date ?? null,
                    'description' => $tx->description ?? ($tx->category_name ?? ''),
                    'debit' => $tx->debit ?? ($tx->amount ?? 0),
                    'credit' => $tx->credit ?? 0,
                    'category_code' => $tx->category_code ?? '',
                ];
            }
        } elseif ($report->details->isNotEmpty()) {
            $incomeCodes = $this->groupKeyMap()['income'];

            foreach ($report->details as $detail) {
                $isIncome = in_array($detail->category_code, $incomeCodes);

                $transactions[] = (object) [
                    'transaction_date' => $detail->transaction_date ?? null,
                    'description' => $detail->category_name ?? ($detail->category_code ?? ''),
                    'debit' => $isIncome ? $detail->amount ?? 0 : 0,
                    'credit' => !$isIncome ? $detail->amount ?? 0 : 0,
                    'category_code' => $detail->category_code ?? '',
                ];
            }
        }

        usort($transactions, [$this, 'compareTransactions']);

        return $transactions;
    }

    private function aggregateByYearAndCategory($reports, array $years): array
    {
        $agg = [];
        $initYear = array_fill_keys($years, 0);

        foreach ($reports as $report) {
            $year = (int) $report->report_year;

            if (!in_array($year, $years)) {
                continue;
            }

            if (!isset($agg['opening_balance'])) {
                $agg['opening_balance'] = $initYear;
            }

            if ($agg['opening_balance'][$year] == 0) {
                $agg['opening_balance'][$year] = $report->opening_balance;
            }

            foreach ($report->details as $detail) {
                $code = $detail->category_code;

                if (!$code) {
                    continue;
                }

                if (!isset($agg[$code])) {
                    $agg[$code] = $initYear;
                }

                $agg[$code][$year] += $detail->amount;
            }

            foreach (['_total_income', '_total_expense', '_closing_balance'] as $key) {
                if (!isset($agg[$key])) {
                    $agg[$key] = $initYear;
                }
            }

            $agg['_total_income'][$year] += (float) $report->total_income;
            $agg['_total_expense'][$year] += (float) $report->total_expense;
            $agg['_closing_balance'][$year] += (float) $report->closing_balance;
        }

        return $agg;
    }
}