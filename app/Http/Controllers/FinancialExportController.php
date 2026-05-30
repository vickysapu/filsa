<?php

namespace App\Http\Controllers;

use App\Models\ChurchStructure;
use App\Models\FinancialReport;
use App\Models\FinancialReportDetail;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;

class FinancialExportController extends Controller
{
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
            'expense_majelis' => ['SETORAN_PP_MP', 'SETORAN_IURAN_MP', 'BIAYA_MIKERIA', 'BIAYA_PASKAH_NATAL', 'BIAYA_RAPAT', 'BIAYA_KURIKULUM', 'BIAYA_SEKRETARIAT', 'BIAYA_LAIN_MAJELIS', 'BIAYA_ADMIN_BANK'],
            'expense_penatalayanan' => ['BIAYA_HAL', 'BIAYA_BUKU_PENATALAYANAN', 'BIAYA_PENATALAYANAN_LAIN'],
            'expense_penggembalaan' => ['BIAYA_SEKOLAH_MINGGU', 'BIAYA_PEMUDA', 'BIAYA_WANITA', 'BIAYA_CAMP_SEMINAR'],
            'expense_misi' => ['SETORAN_DANA_MISI_MP', 'BIAYA_MISI_SEMINAR', 'BIAYA_PENDIDIKAN_MD'],
            'expense_pelayanan' => ['BANTUAN_SAKIT', 'BANTUAN_DUKACITA', 'BANTUAN_PEMBANGUNAN', 'BANTUAN_BENCANA', 'BIAYA_MULTIMEDIA'],
        ];
    }

    public function exportFormLapkeuMD(Request $request)
    {
        $request->validate([
            'year_start' => 'nullable|integer|min:2020|max:2030',
            'year_end'   => 'nullable|integer|min:2020|max:2030',
        ]);

        $yearStart = $request->input('year_start', 2022);
        $yearEnd   = $request->input('year_end',   2025);

        $reports = FinancialReport::with(['details', 'branch'])
            ->where('status', 'approved')
            ->whereBetween('report_year', [$yearStart, $yearEnd])
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $years = range($yearStart, $yearEnd);
        $yearColMap = [];
        $colLetters = ['C', 'D', 'E', 'F'];
        foreach ($years as $i => $y) {
            $yearColMap[$y] = $colLetters[$i] ?? chr(67 + $i);
        }
        $totalCol = chr(ord(end($colLetters)) + 1);
        $lastDataCol = $totalCol;

        $sheet->mergeCells("A1:{$lastDataCol}1");
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastDataCol}2");
        $sheet->setCellValue('A2', 'MAJELIS DAERAH FILADELFIA JAWA BARAT');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastDataCol}3");
        $sheet->setCellValue('A3', "PERIODE 1 JANUARI {$yearStart} - 31 DESEMBER {$yearEnd}");
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(4)->setRowHeight(6);

        $sheet->setCellValue('A5', '');
        $sheet->setCellValue('B5', 'KETERANGAN');

        $firstYearCol = reset($yearColMap);
        $sheet->mergeCells("{$firstYearCol}4:{$totalCol}4");
        $sheet->setCellValue("{$firstYearCol}4", 'K A S   M A S U K');
        $sheet->getStyle("{$firstYearCol}4")->getFont()->setBold(true);
        $sheet->getStyle("{$firstYearCol}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach ($yearColMap as $y => $col) {
            $sheet->setCellValue("{$col}5", "TH {$y}");
            $sheet->getStyle("{$col}5")->getFont()->setBold(true);
            $sheet->getStyle("{$col}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->setCellValue("{$totalCol}5", 'TOTAL');
        $sheet->getStyle("{$totalCol}5")->getFont()->setBold(true);
        $sheet->getStyle("{$totalCol}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A5:{$lastDataCol}5")->applyFromArray([
            'font'      => ['bold' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $aggregated = $this->aggregateByYearAndCategory($reports, $years);

        $row = 6;
        $structure = $this->getReportStructure();

        foreach ($structure as $item) {
            switch ($item['type']) {
                case 'section_header':
                    $sheet->mergeCells("B{$row}:{$lastDataCol}{$row}");
                    $sheet->setCellValue("B{$row}", $item['label']);
                    $sheet->getStyle("B{$row}")->applyFromArray([
                        'font'      => ['bold' => true],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $row++;
                    break;

                case 'group_header':
                    $sheet->mergeCells("B{$row}:{$lastDataCol}{$row}");
                    $sheet->setCellValue("B{$row}", $item['label']);
                    $sheet->getStyle("B{$row}")->applyFromArray([
                        'font'    => ['bold' => true],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $row++;
                    break;

                case 'row':
                    if (isset($item['no'])) {
                        $sheet->setCellValue("A{$row}", $item['no']);
                    }
                    $sheet->setCellValue("B{$row}", $item['label']);

                    $rowTotal = 0;
                    foreach ($yearColMap as $y => $col) {
                        $val = $aggregated[$item['key']][$y] ?? 0;
                        if ($val != 0) {
                            $sheet->setCellValue("{$col}{$row}", $val);
                            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                        }
                        $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $rowTotal += $val;
                    }
                    if ($rowTotal != 0) {
                        $sheet->setCellValue("{$totalCol}{$row}", $rowTotal);
                        $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                    $sheet->getStyle("{$totalCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("A{$row}:{$lastDataCol}{$row}")->getBorders()
                        ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('CCCCCC');
                    $row++;
                    break;

                case 'subtotal':
                    $sheet->setCellValue("B{$row}", $item['label']);
                    $subtotalTotal = 0;
                    foreach ($yearColMap as $y => $col) {
                        $sub = 0;
                        if ($item['group'] === 'income') {
                            $sub = $aggregated['_total_income'][$y] ?? 0;
                        } else {
                            foreach ($this->groupKeyMap()[$item['group']] ?? [] as $k) {
                                $sub += $aggregated[$k][$y] ?? 0;
                            }
                        }
                        if ($sub != 0) {
                            $sheet->setCellValue("{$col}{$row}", $sub);
                            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                        }
                        $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $subtotalTotal += $sub;
                    }
                    if ($subtotalTotal != 0) {
                        $sheet->setCellValue("{$totalCol}{$row}", $subtotalTotal);
                        $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                    $sheet->getStyle("{$totalCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("A{$row}:{$lastDataCol}{$row}")->applyFromArray([
                        'font'    => ['bold' => true],
                        'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $row++;
                    break;

                case 'grand_total_expense':
                    $sheet->setCellValue("B{$row}", $item['label']);
                    $gtTotal = 0;
                    foreach ($yearColMap as $y => $col) {
                        $val = $aggregated['_total_expense'][$y] ?? 0;
                        if ($val != 0) {
                            $sheet->setCellValue("{$col}{$row}", $val);
                            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                        }
                        $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $gtTotal += $val;
                    }
                    if ($gtTotal != 0) {
                        $sheet->setCellValue("{$totalCol}{$row}", $gtTotal);
                        $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                    $sheet->getStyle("{$totalCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("A{$row}:{$lastDataCol}{$row}")->applyFromArray([
                        'font'    => ['bold' => true, 'size' => 11],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                    ]);
                    $row++;
                    break;

                case 'grand_total_saldo':
                    $sheet->setCellValue("B{$row}", $item['label']);
                    $cbTotal = 0;
                    foreach ($yearColMap as $y => $col) {
                        $val = $aggregated['_closing_balance'][$y] ?? 0;
                        if ($val != 0) {
                            $sheet->setCellValue("{$col}{$row}", $val);
                            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                        }
                        $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $cbTotal += $val;
                    }
                    if ($cbTotal != 0) {
                        $sheet->setCellValue("{$totalCol}{$row}", $cbTotal);
                        $sheet->getStyle("{$totalCol}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                    $sheet->getStyle("{$totalCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("A{$row}:{$lastDataCol}{$row}")->applyFromArray([
                        'font'    => ['bold' => true, 'size' => 11],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                    ]);
                    $row++;
                    break;
            }
        }

        $row += 2;
        $sheet->mergeCells("B{$row}:D{$row}");
        $sheet->setCellValue("B{$row}", 'Mengetahui,');
        $sheet->mergeCells("{$totalCol}{$row}:{$lastDataCol}{$row}");
        $sheet->setCellValue("{$totalCol}{$row}", 'Bandung,….');

        $row += 4;
        $sheet->mergeCells("B{$row}:D{$row}");
        $sheet->setCellValue("B{$row}", 'Ketua MDF Jabar');
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells("{$totalCol}{$row}:{$lastDataCol}{$row}");
        $sheet->setCellValue("{$totalCol}{$row}", 'Bendahara MDF Jabar');
        $sheet->getStyle("{$totalCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(52);
        foreach ($yearColMap as $col) {
            $sheet->getColumnDimension($col)->setWidth(17);
        }
        $sheet->getColumnDimension($totalCol)->setWidth(18);

        $filename = "form_lapkeu_md.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    public function exportDaftarPemasukanJabar(Request $request)
    {
        $request->validate([
            'year_start' => 'nullable|integer|min:2020|max:2030',
            'year_end'   => 'nullable|integer|min:2020|max:2030',
        ]);

        $yearStart = $request->input('year_start', 2022);
        $yearEnd   = $request->input('year_end',   date('Y'));

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        for ($year = $yearStart; $year <= $yearEnd; $year++) {
            $reports = FinancialReport::with(['details', 'branch'])
                ->where('status', 'approved')
                ->where('report_year', $year)
                ->get();

            $this->createDaftarPemasukanSheet($spreadsheet, $reports, $year);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = "DAFTAR_PEMASUKAN_JABAR_BULAN_{$yearEnd}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    private function createDaftarPemasukanSheet(Spreadsheet $spreadsheet, $reports, int $year): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle((string)$year);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(8);

        $monthsShort = ['JANUARI', 'FEBUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];

        $totalColIndex = 3 + 12 * 2;
        $colIndexToLetter = function (int $idx): string {
            $letter = '';
            while ($idx > 0) {
                $mod = ($idx - 1) % 26;
                $letter = chr(65 + $mod) . $letter;
                $idx = intval(($idx - $mod) / 26);
            }
            return $letter;
        };

        $totalColLetter = $colIndexToLetter($totalColIndex);

        $sheet->mergeCells("A1:{$totalColLetter}1");
        $sheet->setCellValue('A1', "DAFTAR PEMASUKAN KAS MDF IV JABAR {$year}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(2)->setRowHeight(6);

        $sheet->setCellValue('A3', 'NO.');
        $sheet->setCellValue('B3', 'NAMA');
        for ($m = 1; $m <= 12; $m++) {
            $jumlahColIdx = 3 + ($m - 1) * 2;
            $jumlahColLetter = $colIndexToLetter($jumlahColIdx);
            $ketColLetter    = $colIndexToLetter($jumlahColIdx + 1);

            $sheet->mergeCells("{$jumlahColLetter}3:{$ketColLetter}3");
            $sheet->setCellValue("{$jumlahColLetter}3", $monthsShort[$m - 1]);
            $sheet->getStyle("{$jumlahColLetter}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->setCellValue("{$totalColLetter}3", '');

        $sheet->setCellValue('A4', '');
        $sheet->setCellValue('B4', '');
        for ($m = 1; $m <= 12; $m++) {
            $jumlahColIdx    = 3 + ($m - 1) * 2;
            $jumlahColLetter = $colIndexToLetter($jumlahColIdx);
            $ketColLetter    = $colIndexToLetter($jumlahColIdx + 1);

            $sheet->setCellValue("{$jumlahColLetter}4", 'JUMLAH');
            $sheet->setCellValue("{$ketColLetter}4", 'KET.');
        }
        $sheet->setCellValue("{$totalColLetter}4", 'TOTAL');

        $headerStyle = [
            'font'      => ['bold' => true, 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ];
        $sheet->getStyle("A3:{$totalColLetter}4")->applyFromArray($headerStyle);

        $branchRows = [];

        foreach ($reports as $report) {
            $branchName = $report->branch->name ?? 'Tidak Diketahui';
            if (!isset($branchRows[$branchName])) {
                $branchRows[$branchName] = ['monthly' => [], 'total' => 0];
            }

            $reportMonth = $report->report_month ?? 1;
            $incomeAmount = $report->total_income ?? 0;
            $ket          = '';

            $existing = $branchRows[$branchName]['monthly'][$reportMonth] ?? ['amount' => 0, 'ket' => ''];
            $existing['amount'] += $incomeAmount;

            $branchRows[$branchName]['monthly'][$reportMonth] = $existing;
            $branchRows[$branchName]['total'] += $incomeAmount;
        }

        $bungaRow = ['monthly' => [], 'total' => 0];
        foreach ($reports as $report) {
            $reportMonth = $report->report_month ?? 1;
            foreach ($report->details as $detail) {
                if ($detail->category_code === 'BUNGA_BANK') {
                    $existing = $bungaRow['monthly'][$reportMonth] ?? ['amount' => 0, 'ket' => ''];
                    $existing['amount'] += $detail->amount;
                    $bungaRow['monthly'][$reportMonth] = $existing;
                    $bungaRow['total'] += $detail->amount;
                }
            }
        }
        if ($bungaRow['total'] > 0) {
            $branchRows['Bunga Bank'] = $bungaRow;
        }

        $dataRow = 5;
        $no = 1;
        $grandMonthly = [];

        foreach ($branchRows as $branchName => $data) {
            $sheet->setCellValue("A{$dataRow}", $no);
            $sheet->setCellValue("B{$dataRow}", $branchName);

            $rowTotal = 0;
            for ($m = 1; $m <= 12; $m++) {
                $jumlahColIdx    = 3 + ($m - 1) * 2;
                $jumlahColLetter = $colIndexToLetter($jumlahColIdx);
                $ketColLetter    = $colIndexToLetter($jumlahColIdx + 1);

                $amount = $data['monthly'][$m]['amount'] ?? 0;
                $ket    = $data['monthly'][$m]['ket']    ?? '';

                if ($amount > 0) {
                    $sheet->setCellValue("{$jumlahColLetter}{$dataRow}", $amount);
                    $sheet->getStyle("{$jumlahColLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $grandMonthly[$m] = ($grandMonthly[$m] ?? 0) + $amount;
                }
                if ($ket !== '') {
                    $sheet->setCellValue("{$ketColLetter}{$dataRow}", $ket);
                }

                $sheet->getStyle("{$jumlahColLetter}{$dataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $rowTotal += $amount;
            }

            if ($rowTotal > 0) {
                $sheet->setCellValue("{$totalColLetter}{$dataRow}", $rowTotal);
                $sheet->getStyle("{$totalColLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("{$totalColLetter}{$dataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("{$totalColLetter}{$dataRow}")->getFont()->setBold(true);
            }

            $sheet->getStyle("A{$dataRow}:{$totalColLetter}{$dataRow}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $no++;
            $dataRow++;
        }

        $sheet->setCellValue("B{$dataRow}", 'Total');
        $sheet->getStyle("B{$dataRow}")->getFont()->setBold(true);

        $grandTotal = 0;
        for ($m = 1; $m <= 12; $m++) {
            $jumlahColIdx    = 3 + ($m - 1) * 2;
            $jumlahColLetter = $colIndexToLetter($jumlahColIdx);

            $mTotal = $grandMonthly[$m] ?? 0;
            if ($mTotal > 0) {
                $sheet->setCellValue("{$jumlahColLetter}{$dataRow}", $mTotal);
                $sheet->getStyle("{$jumlahColLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("{$jumlahColLetter}{$dataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $grandTotal += $mTotal;
        }
        $sheet->setCellValue("{$totalColLetter}{$dataRow}", $grandTotal);
        $sheet->getStyle("{$totalColLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("{$totalColLetter}{$dataRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("A{$dataRow}:{$totalColLetter}{$dataRow}")->applyFromArray([
            'font'    => ['bold' => true],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ]);

        $dataRow++;

        $sheet->setCellValue("B{$dataRow}", 'Perpuluhan MP 35%');
        $sheet->getStyle("B{$dataRow}")->getFont()->setBold(true);

        for ($m = 1; $m <= 12; $m++) {
            $jumlahColIdx    = 3 + ($m - 1) * 2;
            $jumlahColLetter = $colIndexToLetter($jumlahColIdx);

            $mTotal = $grandMonthly[$m] ?? 0;
            $pp35   = round($mTotal * 0.35, 2);
            if ($pp35 > 0) {
                $sheet->setCellValue("{$jumlahColLetter}{$dataRow}", $pp35);
                $sheet->getStyle("{$jumlahColLetter}{$dataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("{$jumlahColLetter}{$dataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }

        $dataRow += 2;
        $sheet->setCellValue("A{$dataRow}", 'Kuning = Sudah ditransfer ke MP');
        $sheet->getStyle("A{$dataRow}")->getFont()->setItalic(true)->setSize(8);

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(38);
        for ($m = 1; $m <= 12; $m++) {
            $jumlahColIdx    = 3 + ($m - 1) * 2;
            $jumlahColLetter = $colIndexToLetter($jumlahColIdx);
            $ketColLetter    = $colIndexToLetter($jumlahColIdx + 1);
            $sheet->getColumnDimension($jumlahColLetter)->setWidth(14);
            $sheet->getColumnDimension($ketColLetter)->setWidth(14);
        }
        $sheet->getColumnDimension($totalColLetter)->setWidth(16);
    }

    private function getBranchesWithPriority(string $priority = 'primer'): array
    {
        return ChurchStructure::with(['jemaat', 'branch'])
            ->whereHas('jemaat', function($q) use ($priority) {
                $q->where('priority', $priority);
            })
            ->whereNotNull('branch_id')
            ->pluck('branch_id')
            ->unique()
            ->toArray();
    }

    public function exportLaporanKeuanganMDFJabar(Request $request)
    {
        $request->validate([
            'year'  => 'required|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
            'priority' => 'nullable|in:primer,sekunder',
        ]);

        $year  = $request->input('year');
        $month = $request->input('month');
        $priority = $request->input('priority');

        $query = FinancialReport::with(['details', 'branch'])
            ->where('status', 'approved')
            ->where('report_year', $year);

        if ($month) {
            $query->where('report_month', $month);
        }

        if ($priority) {
            $branchIds = $this->getBranchesWithPriority($priority);
            if (!empty($branchIds)) {
                $query->whereIn('branch_id', $branchIds);
            } else {
                return redirect()->back()->with('error', 'Tidak ada branch dengan priority terpilih.');
            }
        }

        $reports = $query->orderBy('report_month')->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada laporan disetujui untuk periode tersebut.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);

        foreach ($reports as $report) {
            $this->createLaporanKeuanganMDFSheet($spreadsheet, $report);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $suffix   = $month ? strtoupper(Carbon::create()->month($month)->format('F')) . "_" : '';
        $filename = "LAPORAN_KEUANGAN_MDF_IV_JABAR_{$suffix}{$year}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    private function createLaporanKeuanganMDFSheet(Spreadsheet $spreadsheet, FinancialReport $report): void
    {
        $bulanTahun = Carbon::create()
            ->month($report->report_month)
            ->year($report->report_year)
            ->translatedFormat('F Y');

        $sheetTitle = strtoupper(
            Carbon::create()->month($report->report_month)->format('M') . ' ' . $report->report_year
        );
        $sheetTitle = substr($sheetTitle, 0, 31);

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetTitle);

        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', "LAPORAN KEUANGAN MDF IV JABAR " . strtoupper($bulanTahun));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'TANGGAL');
        $sheet->setCellValue('B2', 'URAIAN');
        $sheet->setCellValue('C2', 'DEBIT');
        $sheet->setCellValue('D2', 'KREDIT');
        $sheet->setCellValue('E2', 'SALDO');

        $headerStyle = [
            'font'      => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ];
        $sheet->getStyle('A2:E2')->applyFromArray($headerStyle);
        $sheet->getStyle('C2:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $currencyFormat = '"Rp "#,##0;-";Rp "#,##0';

        $row = 3;
        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", 'SALDO AWAL');
        $sheet->setCellValue("C{$row}", '');
        $sheet->setCellValue("D{$row}", '');
        $sheet->setCellValue("E{$row}", $report->opening_balance);
        
        $sheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $transactions = $this->getOrderedTransactions($report);

        $runningBalance = $report->opening_balance;
        $totalDebit     = 0;
        $totalKredit    = 0;

        foreach ($transactions as $tx) {
            $dateVal = '';
            if (!empty($tx->transaction_date)) {
                try {
                    $dateVal = Carbon::parse($tx->transaction_date)->format('d-M');
                } catch (\Exception $e) {
                    $dateVal = $tx->transaction_date;
                }
            }

            $debit   = (float)($tx->debit  ?? 0);
            $credit  = (float)($tx->credit ?? 0);
            
            $runningBalance = $runningBalance + $debit - $credit;

            $sheet->setCellValue("A{$row}", $dateVal);
            $sheet->setCellValue("B{$row}", $tx->description ?? '');
            
            if ($debit > 0) {
                $sheet->setCellValue("C{$row}", $debit);
            } else {
                $sheet->setCellValue("C{$row}", '');
            }
            
            if ($credit > 0) {
                $sheet->setCellValue("D{$row}", $credit);
            } else {
                $sheet->setCellValue("D{$row}", '');
            }
            
            $sheet->setCellValue("E{$row}", $runningBalance);

            $sheet->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("A{$row}:E{$row}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $totalDebit  += $debit;
            $totalKredit += $credit;
            $row++;
        }

        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", 'Total');
        $sheet->setCellValue("C{$row}", $totalDebit);
        $sheet->setCellValue("D{$row}", $totalKredit);
        $sheet->setCellValue("E{$row}", '');
        
        $sheet->getStyle("C{$row}:D{$row}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
            'font'    => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ]);

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(20);
    }

    private function getOrderedTransactions(FinancialReport $report): array
    {
        $transactions = [];

        if (method_exists($report, 'transactions') && $report->transactions->isNotEmpty()) {
            foreach ($report->transactions as $tx) {
                $transactions[] = (object)[
                    'transaction_date' => $tx->transaction_date ?? null,
                    'description'      => $tx->description ?? $tx->category_name ?? '',
                    'debit'            => $tx->debit ?? $tx->amount ?? 0,
                    'credit'           => $tx->credit ?? 0,
                ];
            }
        } elseif ($report->details->isNotEmpty()) {
            foreach ($report->details as $detail) {
                $isIncome = in_array($detail->category_code, $this->groupKeyMap()['income'] ?? []);
                
                $transactions[] = (object)[
                    'transaction_date' => $detail->transaction_date ?? null,
                    'description'      => $detail->category_name ?? $detail->category_code ?? '',
                    'debit'            => $isIncome ? ($detail->amount ?? 0) : 0,
                    'credit'           => !$isIncome ? ($detail->amount ?? 0) : 0,
                ];
            }
        }

        usort($transactions, function($a, $b) {
            $aHasDate = !empty($a->transaction_date);
            $bHasDate = !empty($b->transaction_date);
            
            if ($aHasDate && !$bHasDate) return -1;
            if (!$aHasDate && $bHasDate) return 1;
            
            return strcmp($a->description ?? '', $b->description ?? '');
        });

        return $transactions;
    }

    private function aggregateByYearAndCategory($reports, array $years): array
    {
        $agg = [];
        $initYear = array_fill_keys($years, 0);

        foreach ($reports as $report) {
            $y = $report->report_year;
            if (!in_array($y, $years)) {
                continue;
            }

            if (!isset($agg['opening_balance'])) {
                $agg['opening_balance'] = $initYear;
            }
            if ($agg['opening_balance'][$y] == 0) {
                $agg['opening_balance'][$y] = $report->opening_balance;
            }

            foreach ($report->details as $detail) {
                $code = $detail->category_code;
                if (!$code) continue;
                if (!isset($agg[$code])) {
                    $agg[$code] = $initYear;
                }
                $agg[$code][$y] += $detail->amount;
            }

            if (!isset($agg['_total_income']))   $agg['_total_income']   = $initYear;
            if (!isset($agg['_total_expense']))  $agg['_total_expense']  = $initYear;
            if (!isset($agg['_closing_balance'])) $agg['_closing_balance'] = $initYear;

            $agg['_total_income'][$y]   += $report->total_income;
            $agg['_total_expense'][$y]  += $report->total_expense;
            $agg['_closing_balance'][$y] += $report->closing_balance;
        }

        return $agg;
    }
}