<?php
namespace App\Http\Controllers;

use App\Models\FinancialReport;
use App\Models\FinancialReportDetail;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class FinancialExportController extends Controller
{
    private function getReportStructure()
    {
        return [
            // === KAS MASUK / PENERIMAAN ===
            ['type' => 'section_header', 'label' => 'K A S   M A S U K'],
            ['type' => 'row', 'key' => 'opening_balance', 'label' => 'SALDO AWAL', 'group' => 'balance'],
            ['type' => 'section_header', 'label' => 'PENERIMAAN  DANA'],
            ['type' => 'row', 'key' => 'PP', 'label' => 'A. PERSEPULUHAN', 'group' => 'income', 'prefix' => 'A.'],
            ['type' => 'row', 'key' => 'IURAN_PEJABAT', 'label' => 'B. IURAN PEJABAT', 'group' => 'income', 'prefix' => 'B.'],
            ['type' => 'row', 'key' => 'DANA_MISI', 'label' => 'C. DANA MISI DAN PENDIDIKAN', 'group' => 'income', 'prefix' => 'C.'],
            ['type' => 'row', 'key' => 'PENERIMAAN_LAIN', 'label' => 'D. PENERIMAAN LAIN-LAIN', 'group' => 'income', 'prefix' => 'D.'],
            ['type' => 'row', 'key' => 'BUNGA_BANK', 'label' => 'E. BUNGA BANK', 'group' => 'income', 'prefix' => 'E.'],
            ['type' => 'subtotal', 'label' => 'TOTAL PENERIMAAN DANA', 'group' => 'income'],
            
            // === PENGELUARAN ===
            ['type' => 'section_header', 'label' => 'PENGELUARAN DANA :'],
            
            // F. BIAYA MAJELIS DAERAH
            ['type' => 'group_header', 'key' => 'expense_majelis', 'label' => 'F. BIAYA MAJELIS DAERAH :', 'prefix' => 'F.'],
            ['type' => 'row', 'key' => 'SETORAN_PP_MP', 'label' => '1. SETORAN PERSEPULUHAN KE MP', 'group' => 'expense_majelis', 'no' => 1],
            ['type' => 'row', 'key' => 'SETORAN_IURAN_MP', 'label' => '2. SETORAN IURAN PEJABAT KE MP', 'group' => 'expense_majelis', 'no' => 2],
            ['type' => 'row', 'key' => 'BIAYA_MIKERIA', 'label' => '3. BIAYA MUSDA, MUKERDA, UPGRADING', 'group' => 'expense_majelis', 'no' => 3],
            ['type' => 'row', 'key' => 'BIAYA_PASKAH_NATAL', 'label' => '4. BIAYA PASKAH, HUT, NATAL', 'group' => 'expense_majelis', 'no' => 4],
            ['type' => 'row', 'key' => 'BIAYA_RAPAT', 'label' => '5. BIAYA RAPAT', 'group' => 'expense_majelis', 'no' => 5],
            ['type' => 'row', 'key' => 'BIAYA_KURIKULUM', 'label' => '6. BIAYA KUNJUNGAN KERJA/DINAS', 'group' => 'expense_majelis', 'no' => 6],
            ['type' => 'row', 'key' => 'BIAYA_SEKRETARIAT', 'label' => '7. BIAYA SEKRETARIAT', 'group' => 'expense_majelis', 'no' => 7],
            ['type' => 'row', 'key' => 'BIAYA_LAIN_MAJELIS', 'label' => '8. BIAYA LAIN-LAIN', 'group' => 'expense_majelis', 'no' => 8],
            ['type' => 'row', 'key' => 'BIAYA_ADMIN_BANK', 'label' => '9. BIAYA ADMINISTRASI BANK', 'group' => 'expense_majelis', 'no' => 9],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA MAJELIS DAERAH', 'group' => 'expense_majelis'],
            
            // G. BIAYA BIDANG PENATALAYANAN
            ['type' => 'group_header', 'key' => 'expense_penatalayanan', 'label' => 'G. BIAYA BIDANG PENATALAYANAN', 'prefix' => 'G.'],
            ['type' => 'row', 'key' => 'BIAYA_BUKU_PENATALAYANAN', 'label' => '1. BIAYA PEMBELIAN BUKU PENATALAYANAN', 'group' => 'expense_penatalayanan', 'no' => 1],
            ['type' => 'row', 'key' => 'BIAYA_PENATALAYANAN_LAIN', 'label' => '2. BIAYA LAIN-LAIN', 'group' => 'expense_penatalayanan', 'no' => 2],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG PENATALAYANAN', 'group' => 'expense_penatalayanan'],
            
            // H. BIAYA BIDANG PENGGEMBALAAN
            ['type' => 'group_header', 'key' => 'expense_penggembalaan', 'label' => 'H. BIAYA BIDANG PENGGEMBALAAN', 'prefix' => 'H.'],
            ['type' => 'row', 'key' => 'BIAYA_SEKOLAH_MINGGU', 'label' => '1. BIAYA PROGRAM SEKOLAH MINGGU', 'group' => 'expense_penggembalaan', 'no' => 1],
            ['type' => 'row', 'key' => 'BIAYA_PEMUDA', 'label' => '2. BIAYA PROGRAM PEMUDA', 'group' => 'expense_penggembalaan', 'no' => 2],
            ['type' => 'row', 'key' => 'BIAYA_WANITA', 'label' => '3. BIAYA PROGRAM WANITA', 'group' => 'expense_penggembalaan', 'no' => 3],
            ['type' => 'row', 'key' => 'BIAYA_CAMP_SEMINAR', 'label' => '4. BIAYA CAMP/SEMINAR HAMBA-HAMBA TUHAN', 'group' => 'expense_penggembalaan', 'no' => 4],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG PENGGEMBALAAN', 'group' => 'expense_penggembalaan'],
            
            // I. BIAYA BIDANG PENDIDIKAN DAN MISI
            ['type' => 'group_header', 'key' => 'expense_misi', 'label' => 'I. BIAYA BIDANG PENDIDIKAN DAN MISI', 'prefix' => 'I.'],
            ['type' => 'row', 'key' => 'SETORAN_DANA_MISI_MP', 'label' => '1. SETORAN DANA MISI DAN PENDIDIKAN KE MP', 'group' => 'expense_misi', 'no' => 1],
            ['type' => 'row', 'key' => 'BIAYA_MISI_SEMINAR', 'label' => '2. BIAYA MISI, SEMINAR MISI MD', 'group' => 'expense_misi', 'no' => 2],
            ['type' => 'row', 'key' => 'BIAYA_PENDIDIKAN_MD', 'label' => '3. BIAYA PENDIDIKAN MD', 'group' => 'expense_misi', 'no' => 3],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG MISI DAN PENDIDIKAN', 'group' => 'expense_misi'],
            
            // J. BIAYA BIDANG PELAYANAN MASYARAKAT
            ['type' => 'group_header', 'key' => 'expense_pelayanan', 'label' => 'J. BIAYA BIDANG PELAYANAN MASYARAKAT', 'prefix' => 'J.'],
            ['type' => 'row', 'key' => 'BANTUAN_SAKIT', 'label' => '1. BANTUAN SAKIT (TERMASUK BPJS)', 'group' => 'expense_pelayanan', 'no' => 1],
            ['type' => 'row', 'key' => 'BANTUAN_DUKACITA', 'label' => '2. BANTUAN DUKACITA', 'group' => 'expense_pelayanan', 'no' => 2],
            ['type' => 'row', 'key' => 'BANTUAN_PEMBANGUNAN', 'label' => '3. BANTUAN PEMBANGUNAN GEREJA', 'group' => 'expense_pelayanan', 'no' => 3],
            ['type' => 'row', 'key' => 'BANTUAN_BENCANA', 'label' => '4. BANTUAN BENCANA ALAM', 'group' => 'expense_pelayanan', 'no' => 4],
            ['type' => 'row', 'key' => 'BIAYA_MULTIMEDIA', 'label' => '5. BIAYA BIDANG IT DAN MULTI MEDIA', 'group' => 'expense_pelayanan', 'no' => 5],
            ['type' => 'subtotal', 'label' => 'SUB TOTAL BIAYA BIDANG PELAYANAN MASYARAKAT', 'group' => 'expense_pelayanan'],
            
            ['type' => 'grand_total', 'label' => 'TOTAL PENGELUARAN DANA'],
            ['type' => 'grand_total', 'label' => 'SALDO AKHIR PER 31 DESEMBER'],
        ];
    }

    public function exportAnnualReport(FinancialReport $report)
    {
        $report->load('details', 'branch');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');
        
        // Set default font
        $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // === HEADER ===
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'MAJELIS DAERAH FILADELFIA JAWA BARAT');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A3:G3');
        $period = $report->report_type === 'monthly' 
            ? \Carbon\Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y')
            : $report->report_year . ' - ' . ($report->report_year);
        $sheet->setCellValue('A3', 'PERIODE ' . strtoupper($period));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getRowDimension(4)->setRowHeight(10); // Spacer row

        // === BUILD DATA MAP FROM DETAILS ===
        $dataMap = [];
        foreach ($report->details as $detail) {
            if ($detail->category_code) {
                $dataMap[$detail->category_code] = $detail->amount;
            }
        }
        // Also map opening/closing balance
        $dataMap['opening_balance'] = $report->opening_balance;
        $dataMap['total_income'] = $report->total_income;
        $dataMap['total_expense'] = $report->total_expense;
        $dataMap['closing_balance'] = $report->closing_balance;

        // === STYLE DEFINITIONS ===
        $styleSectionHeader = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];
        $styleGroupHeader = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $styleLabel = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
        $styleNumber = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'numberFormat' => ['formatCode' => '#,##0'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
        $styleSubtotal = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'numberFormat' => ['formatCode' => '#,##0'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ];
        $styleGrandTotal = [
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'numberFormat' => ['formatCode' => '#,##0'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // === WRITE CONTENT ===
        $row = 5;
        $structure = $this->getReportStructure();
        
        // Column layout: A=No, B=Keterangan, C=TH 2022, D=TH 2023, E=TH 2024, F=TH 2025, G=TOTAL
        // For single year report, we use: A=No, B=Keterangan, F=TOTAL
        
        foreach ($structure as $item) {
            if ($item['type'] === 'section_header') {
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->getStyle("B{$row}")->applyFromArray($styleSectionHeader);
                $row++;
                
            } elseif ($item['type'] === 'group_header') {
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->getStyle("B{$row}")->applyFromArray($styleGroupHeader);
                $row++;
                
            } elseif ($item['type'] === 'row') {
                // Column A: No. (if exists)
                if (isset($item['no'])) {
                    $sheet->setCellValue("A{$row}", $item['no']);
                }
                // Column B: Label
                $sheet->setCellValue("B{$row}", $item['label']);
                // Column F: Amount (blank if no data)
                $amount = $dataMap[$item['key']] ?? null;
                if ($amount !== null && $amount != 0) {
                    $sheet->setCellValue("F{$row}", $amount);
                    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0');
                }
                // Apply borders to B and F
                $sheet->getStyle("B{$row}")->applyFromArray($styleLabel);
                $sheet->getStyle("F{$row}")->applyFromArray($styleNumber);
                $row++;
                
            } elseif ($item['type'] === 'subtotal') {
                $sheet->mergeCells("B{$row}:E{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                // Calculate subtotal from grouped details
                $subtotal = 0;
                if (isset($item['group'])) {
                    foreach ($report->details as $d) {
                        if ($d->category_group === $item['group']) {
                            $subtotal += $d->amount;
                        }
                    }
                }
                if ($item['group'] === 'income') {
                    $subtotal = $report->total_income;
                }
                $sheet->setCellValue("F{$row}", $subtotal);
                $sheet->getStyle("B{$row}:F{$row}")->applyFromArray($styleSubtotal);
                $row++;
                
            } elseif ($item['type'] === 'grand_total') {
                $sheet->mergeCells("B{$row}:E{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                
                if (str_contains($item['label'], 'PENGELUARAN')) {
                    $sheet->setCellValue("F{$row}", $report->total_expense);
                } elseif (str_contains($item['label'], 'SALDO AKHIR')) {
                    $sheet->setCellValue("F{$row}", $report->closing_balance);
                }
                
                $sheet->getStyle("B{$row}:F{$row}")->applyFromArray($styleGrandTotal);
                $row++;
            }
        }

        // === SIGNATURE SECTION ===
        $row += 2;
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", 'Mengetahui,');
        
        $sheet->mergeCells("F{$row}:G{$row}");
        $city = $report->branch->city ?? 'Bandung';
        $sheet->setCellValue("F{$row}", "{$city}, " . \Carbon\Carbon::now()->translatedFormat('d F Y'));
        
        $row += 4; // Space for signature
        
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", 'Ketua MDF Jabar');
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells("F{$row}:G{$row}");
        $sheet->setCellValue("F{$row}", 'Bendahara MDF Jabar');
        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // === COLUMN WIDTH & FINAL STYLING ===
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(15);
        
        // Hide unused year columns for single-year report
        if ($report->report_type === 'annual') {
            $sheet->getColumnDimension('C')->setVisible(false); // TH 2022
            $sheet->getColumnDimension('D')->setVisible(false); // TH 2023
            $sheet->getColumnDimension('E')->setVisible(false); // TH 2024
            $sheet->getColumnDimension('G')->setVisible(false); // TOTAL (already using F)
            // Rename F header to show current year
            $sheet->setCellValue('F4', 'TH ' . $report->report_year);
            $sheet->getStyle('F4')->getFont()->setBold(true);
            $sheet->getStyle('F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // === OUTPUT ===
        $filename = "Lapkeu_{$report->branch->code}_{$report->report_year}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    public function exportConsolidatedAnnual(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        // Query approved reports for consolidation
        $query = FinancialReport::with(['details', 'branch'])
            ->where('status', 'approved')
            ->where('report_year', $year);
        
        if ($month) {
            $query->where('report_month', $month);
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada laporan yang disetujui untuk periode tersebut.');
        }

        return $this->generateConsolidatedExcel($reports, $year, $month);
    }

        private function generateConsolidatedExcel($reports, $year, $month = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Konsolidasi');
        
        // Set default font
        $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // === HEADER ===
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'MAJELIS DAERAH FILADELFIA JAWA BARAT');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A3:G3');
        $period = $month 
            ? Carbon::create()->month($month)->year($year)->format('F Y')
            : $year;
        $sheet->setCellValue('A3', 'PERIODE ' . strtoupper($period));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getRowDimension(4)->setRowHeight(10); // Spacer row

        // === AGGREGATE DATA BY CATEGORY ===
        $aggregatedData = $this->aggregateReportsData($reports, $year);

        // === COLUMN HEADERS ===
        $sheet->setCellValue('A4', ''); // No. column header (empty)
        $sheet->setCellValue('B4', 'KETERANGAN');
        
        // Year columns (dynamic based on data range)
        $yearColumns = ['C' => 2022, 'D' => 2023, 'E' => 2024, 'F' => 2025, 'G' => 'TOTAL'];
        foreach ($yearColumns as $col => $label) {
            $sheet->setCellValue("{$col}4", is_int($label) ? 'TH ' . $label : $label);
            $sheet->getStyle("{$col}4")->getFont()->setBold(true);
            $sheet->getStyle("{$col}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // === STYLE DEFINITIONS ===
        $styleSectionHeader = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        ];
        $styleGroupHeader = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $styleLabel = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
        $styleNumber = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'numberFormat' => ['formatCode' => '#,##0'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
        $styleSubtotal = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'numberFormat' => ['formatCode' => '#,##0'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ];
        $styleGrandTotal = [
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'numberFormat' => ['formatCode' => '#,##0'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // === WRITE CONTENT ===
        $row = 5;
        $structure = $this->getReportStructure();
        $yearCols = ['C', 'D', 'E', 'F']; // Columns for years 2022-2025
        
        foreach ($structure as $item) {
            if ($item['type'] === 'section_header') {
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->getStyle("B{$row}:G{$row}")->applyFromArray($styleSectionHeader);
                $row++;
                
            } elseif ($item['type'] === 'group_header') {
                $sheet->mergeCells("B{$row}:G{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->getStyle("B{$row}:G{$row}")->applyFromArray($styleGroupHeader);
                $row++;
                
            } elseif ($item['type'] === 'row') {
                // Column A: No. (if exists)
                if (isset($item['no'])) {
                    $sheet->setCellValue("A{$row}", $item['no']);
                    $sheet->getStyle("A{$row}")->applyFromArray($styleLabel);
                }
                
                // Column B: Label
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->getStyle("B{$row}")->applyFromArray($styleLabel);
                
                // Columns C-F: Year data, Column G: Total
                foreach ($yearCols as $index => $col) {
                    $targetYear = 2022 + $index;
                    $amount = $aggregatedData[$item['key']][$targetYear] ?? 0;
                    if ($amount != 0) {
                        $sheet->setCellValue("{$col}{$row}", $amount);
                        $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                    $sheet->getStyle("{$col}{$row}")->applyFromArray($styleNumber);
                }
                
                // Column G: Total
                $totalAmount = $aggregatedData[$item['key']]['total'] ?? 0;
                if ($totalAmount != 0) {
                    $sheet->setCellValue("G{$row}", $totalAmount);
                    $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
                }
                $sheet->getStyle("G{$row}")->applyFromArray($styleNumber);
                
                $row++;
                
            } elseif ($item['type'] === 'subtotal') {
                $sheet->mergeCells("B{$row}:B{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                
                // Calculate and write subtotals for each year + total
                foreach ($yearCols as $index => $col) {
                    $targetYear = 2022 + $index;
                    $subtotal = 0;
                    if (isset($item['group'])) {
                        foreach ($aggregatedData as $key => $yearData) {
                            if (isset($yearData[$targetYear]) && $this->isKeyInGroup($key, $item['group'])) {
                                $subtotal += $yearData[$targetYear];
                            }
                        }
                    }
                    if ($item['group'] === 'income') {
                        $subtotal = $aggregatedData['total_income'][$targetYear] ?? 0;
                    }
                    if ($subtotal != 0) {
                        $sheet->setCellValue("{$col}{$row}", $subtotal);
                    }
                    $sheet->getStyle("{$col}{$row}")->applyFromArray($styleSubtotal);
                }
                
                // Total column
                $grandSubtotal = $aggregatedData['total_income']['total'] ?? 0;
                if ($item['group'] === 'income') {
                    $sheet->setCellValue("G{$row}", $grandSubtotal);
                }
                $sheet->getStyle("G{$row}")->applyFromArray($styleSubtotal);
                
                $row++;
                
            } elseif ($item['type'] === 'grand_total') {
                $sheet->mergeCells("B{$row}:B{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                
                if (str_contains($item['label'], 'PENGELUARAN')) {
                    foreach ($yearCols as $index => $col) {
                        $targetYear = 2022 + $index;
                        $amount = $aggregatedData['total_expense'][$targetYear] ?? 0;
                        if ($amount != 0) {
                            $sheet->setCellValue("{$col}{$row}", $amount);
                        }
                        $sheet->getStyle("{$col}{$row}")->applyFromArray($styleGrandTotal);
                    }
                    $sheet->setCellValue("G{$row}", $aggregatedData['total_expense']['total'] ?? 0);
                    $sheet->getStyle("G{$row}")->applyFromArray($styleGrandTotal);
                    
                } elseif (str_contains($item['label'], 'SALDO AKHIR')) {
                    foreach ($yearCols as $index => $col) {
                        $targetYear = 2022 + $index;
                        $amount = $aggregatedData['closing_balance'][$targetYear] ?? 0;
                        if ($amount != 0) {
                            $sheet->setCellValue("{$col}{$row}", $amount);
                        }
                        $sheet->getStyle("{$col}{$row}")->applyFromArray($styleGrandTotal);
                    }
                    $sheet->setCellValue("G{$row}", $aggregatedData['closing_balance']['total'] ?? 0);
                    $sheet->getStyle("G{$row}")->applyFromArray($styleGrandTotal);
                }
                $row++;
            }
        }

        // === SIGNATURE SECTION ===
        $row += 2;
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", 'Mengetahui,');
        
        $sheet->mergeCells("F{$row}:G{$row}");
        $sheet->setCellValue("F{$row}", "Bandung, " . Carbon::now()->translatedFormat('d F Y'));
        
        $row += 4; // Space for signature
        
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", 'Ketua MDF Jabar');
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells("F{$row}:G{$row}");
        $sheet->setCellValue("F{$row}", 'Bendahara MDF Jabar');
        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // === COLUMN WIDTH ===
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);

        // === AUTO FILTER FOR YEARS WITH NO DATA ===
        // Hide year columns that have no data
        for ($y = 2022; $y <= 2025; $y++) {
            $hasData = false;
            foreach ($aggregatedData as $categoryData) {
                if (isset($categoryData[$y]) && $categoryData[$y] != 0) {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) {
                $col = chr(67 + ($y - 2022)); // C=2022, D=2023, etc.
                $sheet->getColumnDimension($col)->setVisible(false);
            }
        }

        // === OUTPUT ===
        $periodLabel = $month ? Carbon::create()->month($month)->year($year)->format('F_Y') : $year;
        $filename = "Lapkeu_Konsolidasi_MDF_JABAR_{$periodLabel}.xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }

    /**
     * Aggregate data from multiple reports by category and year
     */
    private function aggregateReportsData($reports, $targetYear)
    {
        $aggregated = [];
        
        foreach ($reports as $report) {
            $reportYear = $report->report_year;
            
            // Skip if report year is outside our display range
            if ($reportYear < 2022 || $reportYear > 2025) {
                continue;
            }
            
            // Aggregate details by category_code
            foreach ($report->details as $detail) {
                if ($detail->category_code) {
                    if (!isset($aggregated[$detail->category_code])) {
                        $aggregated[$detail->category_code] = [
                            2022 => 0, 2023 => 0, 2024 => 0, 2025 => 0, 'total' => 0
                        ];
                    }
                    $aggregated[$detail->category_code][$reportYear] += $detail->amount;
                    $aggregated[$detail->category_code]['total'] += $detail->amount;
                }
            }
            
            // Aggregate opening balance
            if (!isset($aggregated['opening_balance'])) {
                $aggregated['opening_balance'] = [2022 => 0, 2023 => 0, 2024 => 0, 2025 => 0, 'total' => 0];
            }
            // Use the earliest report's opening balance for each year
            if ($aggregated['opening_balance'][$reportYear] == 0) {
                $aggregated['opening_balance'][$reportYear] = $report->opening_balance;
                $aggregated['opening_balance']['total'] += $report->opening_balance;
            }
            
            // Aggregate totals
            $aggregated['total_income'][$reportYear] = ($aggregated['total_income'][$reportYear] ?? 0) + $report->total_income;
            $aggregated['total_income']['total'] = ($aggregated['total_income']['total'] ?? 0) + $report->total_income;
            
            $aggregated['total_expense'][$reportYear] = ($aggregated['total_expense'][$reportYear] ?? 0) + $report->total_expense;
            $aggregated['total_expense']['total'] = ($aggregated['total_expense']['total'] ?? 0) + $report->total_expense;
            
            $aggregated['closing_balance'][$reportYear] = ($aggregated['closing_balance'][$reportYear] ?? 0) + $report->closing_balance;
            $aggregated['closing_balance']['total'] = ($aggregated['closing_balance']['total'] ?? 0) + $report->closing_balance;
        }
        
        return $aggregated;
    }

    private function isKeyInGroup($key, $group)
    {
        $groupMappings = [
            'income' => ['PP', 'IURAN_PEJABAT', 'DANA_MISI', 'PENERIMAAN_LAIN', 'BUNGA_BANK'],
            'expense_majelis' => ['SETORAN_PP_MP', 'SETORAN_IURAN_MP', 'BIAYA_MIKERIA', 'BIAYA_PASKAH_NATAL', 
                                  'BIAYA_RAPAT', 'BIAYA_KURIKULUM', 'BIAYA_SEKRETARIAT', 'BIAYA_LAIN_MAJELIS', 'BIAYA_ADMIN_BANK'],
            'expense_penatalayanan' => ['BIAYA_BUKU_PENATALAYANAN', 'BIAYA_PENATALAYANAN_LAIN'],
            'expense_penggembalaan' => ['BIAYA_SEKOLAH_MINGGU', 'BIAYA_PEMUDA', 'BIAYA_WANITA', 'BIAYA_CAMP_SEMINAR'],
            'expense_misi' => ['SETORAN_DANA_MISI_MP', 'BIAYA_MISI_SEMINAR', 'BIAYA_PENDIDIKAN_MD'],
            'expense_pelayanan' => ['BANTUAN_SAKIT', 'BANTUAN_DUKACITA', 'BANTUAN_PEMBANGUNAN', 'BANTUAN_BENCANA', 'BIAYA_MULTIMEDIA'],
        ];
        
        return in_array($key, $groupMappings[$group] ?? []);
    }

    public function exportConsolidated(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');
        
        // Fetch approved reports only
        $query = FinancialReport::with(['details', 'branch'])
            ->where('status', 'approved')
            ->where('report_year', $year);
        
        if ($month) {
            $query->where('report_type', 'monthly')
                  ->where('report_month', $month);
        }
        
        $reports = $query->get();
        
        if ($reports->isEmpty()) {
            return back()->with('error', 'Tidak ada laporan disetujui untuk periode tersebut.');
        }

        $spreadsheet = new Spreadsheet();
        
        // === SHEET 1: KONSOLIDASI UTAMA (Format form lapkeu md) ===
        $this->createConsolidatedSheet($spreadsheet, $reports, $year, $month);
        
        // === SHEET 2: DAFTAR PEMASUKAN PER CABANG ===
        $this->createIncomeListSheet($spreadsheet, $reports, $year, $month);
        
        // === SHEET 3: RINCIAN TRANSAKSI BULANAN ===
        $this->createTransactionDetailSheet($spreadsheet, $reports, $year, $month);
        
        // === SHEET 4: REKAP PER CABANG ===
        $this->createBranchSummarySheet($spreadsheet, $reports, $year, $month);

        // === OUTPUT ===
        $periodLabel = $month ? Carbon::create()->month($month)->format('F') : 'Tahunan';
        $filename = "Konsolidasi_Lapkeu_MDF_Jabar_{$periodLabel}_{$year}.xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    /**
     * Sheet 1: Konsolidasi Utama (Format seperti form lapkeu md)
     */
    private function createConsolidatedSheet(Spreadsheet $spreadsheet, $reports, $year, $month)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KONSOLIDASI UTAMA');
        $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);

        // === HEADER ===
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN KONSOLIDASI');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'MAJELIS DAERAH FILADELFIA JAWA BARAT');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A3:F3');
        $period = $month ? Carbon::create()->month($month)->year($year)->format('F Y') : "1 Januari - 31 Desember {$year}";
        $sheet->setCellValue('A3', 'PERIODE: ' . strtoupper($period));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getRowDimension(4)->setRowHeight(8);

        // === COLUMN HEADERS ===
        $sheet->setCellValue('A5', '');
        $sheet->setCellValue('B5', 'KETERANGAN');
        $sheet->setCellValue('C5', 'KAS MASUK');
        $sheet->mergeCells('D5:F5');
        $sheet->setCellValue('D5', 'TOTAL');
        
        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ];
        $sheet->getStyle('A5:F5')->applyFromArray($headerStyle);
        $sheet->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // === BUILD DATA ===
        $dataMap = $this->buildConsolidatedData($reports);
        $structure = $this->getReportStructure();
        
        $row = 6;
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($structure as $item) {
            if ($item['type'] === 'section_header') {
                $sheet->mergeCells("B{$row}:F{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                $sheet->getStyle("B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6F3FF');
                $row++;
                
            } elseif ($item['type'] === 'group_header') {
                $sheet->mergeCells("B{$row}:F{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(9);
                $sheet->getStyle("B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                $row++;
                
            } elseif ($item['type'] === 'row') {
                // No. column
                if (isset($item['no'])) {
                    $sheet->setCellValue("A{$row}", $item['no']);
                }
                
                // Label column
                $label = isset($item['prefix']) ? $item['prefix'] . ' ' . $item['label'] : $item['label'];
                $sheet->setCellValue("B{$row}", $label);
                
                // Amount column
                $amount = $dataMap[$item['key']] ?? 0;
                if ($amount > 0) {
                    $sheet->setCellValue("C{$row}", $amount);
                    $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                
                // Apply borders
                $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
                
            } elseif ($item['type'] === 'subtotal') {
                $sheet->mergeCells("B{$row}:B{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                
                // Calculate subtotal
                $subtotal = 0;
                if ($item['group'] === 'income') {
                    $subtotal = $dataMap['total_income'] ?? 0;
                    $totalIncome = $subtotal;
                } elseif (isset($item['group']) && str_starts_with($item['group'], 'expense_')) {
                    foreach ($reports as $report) {
                        foreach ($report->details as $detail) {
                            if ($detail->category_group === $item['group']) {
                                $subtotal += $detail->amount;
                            }
                        }
                    }
                    $totalExpense += $subtotal;
                }
                
                if ($subtotal > 0) {
                    $sheet->mergeCells("C{$row}:F{$row}");
                    $sheet->setCellValue("C{$row}", $subtotal);
                    $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("C{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                
                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
                $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
                $row++;
                
            } elseif ($item['type'] === 'grand_total') {
                $sheet->mergeCells("B{$row}:B{$row}");
                $sheet->setCellValue("B{$row}", $item['label']);
                
                if (str_contains($item['label'], 'PENGELUARAN')) {
                    $sheet->mergeCells("C{$row}:F{$row}");
                    $sheet->setCellValue("C{$row}", $totalExpense);
                } elseif (str_contains($item['label'], 'SALDO AKHIR')) {
                    $closingBalance = ($dataMap['opening_balance'] ?? 0) + $totalIncome - $totalExpense;
                    $sheet->mergeCells("C{$row}:F{$row}");
                    $sheet->setCellValue("C{$row}", $closingBalance);
                }
                
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4C6E7');
                $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
                $row += 2;
            }
        }

        // === SIGNATURE ===
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", 'Mengetahui,');
        
        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->setCellValue("E{$row}", 'Bandung, ' . Carbon::now()->translatedFormat('d F Y'));
        
        $row += 4;
        
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", "Ketua MDF Jabar");
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells("E{$row}:F{$row}");
        $sheet->setCellValue("E{$row}", "Bendahara MDF Jabar");
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // === COLUMN WIDTH ===
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(1);
        $sheet->getColumnDimension('E')->setWidth(1);
        $sheet->getColumnDimension('F')->setWidth(15);
    }

    /**
     * Sheet 2: Daftar Pemasukan Per Cabang (Format seperti DAFTAR PEMASUKAN)
     */
    private function createIncomeListSheet(Spreadsheet $spreadsheet, $reports, $year, $month)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('DAFTAR PEMASUKAN');
        $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(8);

        // Header
        $sheet->mergeCells('A1:Z1');
        $sheet->setCellValue('A1', 'DAFTAR PEMASUKAN KAS MDF IV JABAR ' . ($month ? strtoupper(Carbon::create()->month($month)->format('F')) : '') . ' ' . $year);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getRowDimension(2)->setRowHeight(8);

        // Column headers
        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'NAMA CABANG');
        
        // Month columns or Year total
        $col = 'C';
        if ($month) {
            $sheet->mergeCells("C3:D3");
            $sheet->setCellValue('C3', strtoupper(Carbon::create()->month($month)->format('F')));
            $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('E3', 'TOTAL');
            $sheet->getStyle('E3')->getFont()->setBold(true);
            $lastCol = 'E';
        } else {
            $months = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];
            foreach ($months as $m) {
                $sheet->setCellValue($col . '3', $m);
                $sheet->getStyle($col . '3')->getFont()->setSize(7);
                $sheet->getColumnDimension($col)->setWidth(12);
                $col++;
            }
            $sheet->setCellValue($col . '3', 'TOTAL');
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $lastCol = $col;
        }
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ];
        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray($headerStyle);

        // Group reports by branch
        $branchData = [];
        foreach ($reports as $report) {
            $branchId = $report->branch_id;
            if (!isset($branchData[$branchId])) {
                $branchData[$branchId] = [
                    'name' => $report->branch->name,
                    'code' => $report->branch->code,
                    'monthly' => [],
                    'total' => 0,
                ];
            }
            
            $incomeAmount = $report->details->whereIn('category_group', ['income'])->sum('amount') 
                          + ($report->opening_balance > 0 ? $report->opening_balance : 0);
            
            if ($month) {
                $branchData[$branchId]['monthly'][$month] = $incomeAmount;
                $branchData[$branchId]['total'] += $incomeAmount;
            } else {
                $reportMonth = $report->report_month ?? 1;
                $branchData[$branchId]['monthly'][$reportMonth] = ($branchData[$branchId]['monthly'][$reportMonth] ?? 0) + $incomeAmount;
                $branchData[$branchId]['total'] += $incomeAmount;
            }
        }

        // Write data rows
        $row = 4;
        $no = 1;
        foreach ($branchData as $branch) {
            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $branch['name'] . ($branch['code'] ? " ({$branch['code']})" : ''));
            
            $col = 'C';
            $branchTotal = 0;
            
            if ($month) {
                $amount = $branch['monthly'][$month] ?? 0;
                if ($amount > 0) {
                    $sheet->setCellValue($col . $row, $amount);
                    $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                }
                $col++;
                $sheet->setCellValue($col . $row, $branch['total']);
                $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
            } else {
                for ($m = 1; $m <= 12; $m++) {
                    $amount = $branch['monthly'][$m] ?? 0;
                    if ($amount > 0) {
                        $sheet->setCellValue($col . $row, $amount);
                        $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $branchTotal += $amount;
                    }
                    $col++;
                }
                $sheet->setCellValue($col . $row, $branch['total']);
                $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
            }
            
            // Apply borders
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $row++;
            $no++;
        }

        // Grand total row
        $sheet->setCellValue("B{$row}", 'TOTAL SEMUA CABANG');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        
        $col = 'C';
        $grandTotal = 0;
        if ($month) {
            $monthTotal = array_sum(array_column($branchData, 'total'));
            $sheet->setCellValue($col . $row, $monthTotal);
            $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
            $col++;
            $sheet->setCellValue($col . $row, $monthTotal);
            $grandTotal = $monthTotal;
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $monthSum = 0;
                foreach ($branchData as $b) {
                    $monthSum += $b['monthly'][$m] ?? 0;
                }
                if ($monthSum > 0) {
                    $sheet->setCellValue($col . $row, $monthSum);
                    $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $grandTotal += $monthSum;
                }
                $col++;
            }
            $sheet->setCellValue($col . $row, $grandTotal);
        }
        
        $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4C6E7');
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(35);
    }

    /**
     * Sheet 3: Rincian Transaksi Bulanan (Format seperti LAPORAN KEUANGAN MDF IV JABAR)
     */
    private function createTransactionDetailSheet(Spreadsheet $spreadsheet, $reports, $year, $month)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('RINCIAN TRANSAKSI');
        $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(8);

        $row = 1;
        
        foreach ($reports as $report) {
            // Report header
            $sheet->mergeCells("A{$row}:E{$row}");
            $periodLabel = $report->report_type === 'monthly' 
                ? Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y')
                : $report->report_year;
            $sheet->setCellValue("A{$row}", "LAPORAN KEUANGAN - {$report->branch->name} - {$periodLabel}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
            
            // Table header
            $sheet->setCellValue("A{$row}", 'TANGGAL');
            $sheet->setCellValue("B{$row}", 'URAIAN');
            $sheet->setCellValue("C{$row}", 'DEBIT');
            $sheet->setCellValue("D{$row}", 'KREDIT');
            $sheet->setCellValue("E{$row}", 'SALDO');
            
            $headerStyle = [
                'font' => ['bold' => true, 'size' => 8],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6F3FF']],
            ];
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($headerStyle);
            $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
            
            // Opening balance
            $sheet->setCellValue("B{$row}", 'SALDO AWAL');
            $sheet->setCellValue("E{$row}", $report->opening_balance);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
            
            // Transaction details
            foreach ($report->details as $detail) {
                $sheet->setCellValue("B{$row}", $detail->category_name ?? $detail->category_code);
                
                if ($detail->category_group === 'income') {
                    $sheet->setCellValue("C{$row}", $detail->amount);
                    $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                } else {
                    $sheet->setCellValue("D{$row}", $detail->amount);
                    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
                }
                
                // Calculate running balance
                $runningBalance = $report->opening_balance 
                    + $report->details->whereIn('category_group', ['income'])->where('id', '<=', $detail->id)->sum('amount')
                    - $report->details->whereNotIn('category_group', ['income'])->where('id', '<=', $detail->id)->sum('amount');
                $sheet->setCellValue("E{$row}", $runningBalance);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
                
                $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
            }
            
            // Closing balance
            $sheet->mergeCells("B{$row}:D{$row}");
            $sheet->setCellValue("B{$row}", 'SALDO AKHIR');
            $sheet->setCellValue("E{$row}", $report->closing_balance);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("B{$row}:E{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
            
            $row += 2; // Spacer between reports
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);
    }

    /**
     * Sheet 4: Rekap Per Cabang
     */
    private function createBranchSummarySheet(Spreadsheet $spreadsheet, $reports, $year, $month)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('REKAP CABANG');
        $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);

        // Header
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'REKAPITULASI KEUANGAN PER CABANG');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('A2:G2');
        $period = $month ? Carbon::create()->month($month)->year($year)->format('F Y') : "Tahun {$year}";
        $sheet->setCellValue('A2', 'Periode: ' . $period);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getRowDimension(3)->setRowHeight(8);

        // Column headers
        $headers = ['NO', 'CABANG', 'SALDO AWAL', 'PEMASUKAN', 'PENGELUARAN', 'SALDO AKHIR', 'STATUS'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }
        
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ];
        $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);

        // Data rows
        $row = 5;
        $no = 1;
        $grandOpening = 0;
        $grandIncome = 0;
        $grandExpense = 0;
        $grandClosing = 0;

        foreach ($reports->groupBy('branch_id') as $branchId => $branchReports) {
            $branch = $branchReports->first()->branch;
            
            $opening = $branchReports->sum('opening_balance');
            $income = $branchReports->sum('total_income');
            $expense = $branchReports->sum('total_expense');
            $closing = $branchReports->sum('closing_balance');
            
            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $branch->name . ($branch->code ? " ({$branch->code})" : ''));
            $sheet->setCellValue("C{$row}", $opening);
            $sheet->setCellValue("D{$row}", $income);
            $sheet->setCellValue("E{$row}", $expense);
            $sheet->setCellValue("F{$row}", $closing);
            $sheet->setCellValue("G{$row}", 'Disetujui');
            
            // Number format
            foreach (['C', 'D', 'E', 'F'] as $c) {
                $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("{$c}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            
            // Status badge style
            $sheet->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6EFCE');
            $sheet->getStyle("G{$row}")->getFont()->setColor(new Color('006100'));
            
            // Borders
            $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            // Accumulate grand totals
            $grandOpening += $opening;
            $grandIncome += $income;
            $grandExpense += $expense;
            $grandClosing += $closing;
            
            $row++;
            $no++;
        }

        // Grand total row
        $sheet->setCellValue("B{$row}", 'TOTAL KONSOLIDASI');
        $sheet->setCellValue("C{$row}", $grandOpening);
        $sheet->setCellValue("D{$row}", $grandIncome);
        $sheet->setCellValue("E{$row}", $grandExpense);
        $sheet->setCellValue("F{$row}", $grandClosing);
        
        foreach (['C', 'D', 'E', 'F'] as $c) {
            $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("{$c}{$row}")->getFont()->setBold(true);
        }
        
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4C6E7');
        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(35);
        foreach (['C', 'D', 'E', 'F'] as $c) {
            $sheet->getColumnDimension($c)->setWidth(15);
        }
        $sheet->getColumnDimension('G')->setWidth(12);
    }

    /**
     * Helper: Build consolidated data map from reports
     */
    private function buildConsolidatedData($reports)
    {
        $dataMap = [];
        
        foreach ($reports as $report) {
            // Opening balance (only count once per branch per year)
            $branchKey = "opening_{$report->branch_id}";
            if (!isset($dataMap[$branchKey])) {
                $dataMap['opening_balance'] = ($dataMap['opening_balance'] ?? 0) + $report->opening_balance;
                $dataMap[$branchKey] = true;
            }
            
            // Details
            foreach ($report->details as $detail) {
                if ($detail->category_code) {
                    $dataMap[$detail->category_code] = ($dataMap[$detail->category_code] ?? 0) + $detail->amount;
                }
            }
            
            // Totals
            $dataMap['total_income'] = ($dataMap['total_income'] ?? 0) + $report->total_income;
            $dataMap['total_expense'] = ($dataMap['total_expense'] ?? 0) + $report->total_expense;
            $dataMap['closing_balance'] = ($dataMap['closing_balance'] ?? 0) + $report->closing_balance;
        }
        
        return $dataMap;
    }
}