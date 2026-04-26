<?php
namespace App\Http\Controllers;

use App\Models\FinancialReport;
use App\Models\FinancialReportDetail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancialExportController extends Controller
{
    // ... method exportTransactions & exportMonthlyIncome tetap seperti yang Anda berikan ...

    public function exportAnnualReport(FinancialReport $report)
    {
        $report->load('details', 'branch');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');

        // Header
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN ' . strtoupper($report->branch->name));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A2:E2');
        $period = $report->report_type === 'monthly' 
            ? \Carbon\Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y')
            : 'Tahun ' . $report->report_year;
        $sheet->setCellValue('A2', 'Periode: ' . $period);
        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'Status: ' . ucfirst($report->status));

        $styleHeader = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];

        $row = 5;
        $sheet->setCellValue('A' . $row, 'Kategori');
        $sheet->setCellValue('B' . $row, 'Tipe');
        $sheet->setCellValue('C' . $row, 'Jumlah (Rp)');
        $sheet->setCellValue('D' . $row, 'Catatan');
        $sheet->getStyle('A5:D5')->applyFromArray($styleHeader);

        $row = 6;
        foreach ($report->details as $detail) {
            $sheet->setCellValue('A' . $row, $detail->category_name);
            $sheet->setCellValue('B' . $row, $detail->type === 'income' ? 'Pemasukan' : 'Pengeluaran');
            $sheet->setCellValue('C' . $row, $detail->amount);
            $sheet->setCellValue('D' . $row, $detail->notes ?? '-');
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Summary
        $row++;
        $sheet->setCellValue('A' . $row, 'SALDO AWAL');
        $sheet->setCellValue('C' . $row, $report->opening_balance);
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL PEMASUKAN');
        $sheet->setCellValue('C' . $row, $report->total_income);
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL PENGELUARAN');
        $sheet->setCellValue('C' . $row, $report->total_expense);
        $row++;
        $sheet->setCellValue('A' . $row, 'SALDO AKHIR');
        $sheet->setCellValue('C' . $row, $report->closing_balance);
        $sheet->getStyle('A' . ($row-3) . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getStyle('C' . ($row-3) . ':C' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Auto width
        foreach (range('A', 'D') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $filename = "Laporan_{$report->branch->code}_{$period}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }
}