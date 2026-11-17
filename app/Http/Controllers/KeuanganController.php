<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    /**
     * Dashboard Keuangan Utama
     */
    public function dashboard(Request $request)
    {
        // Get date range from request or default to current month
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

        // Calculate totals
        $totalPemasukan = Pemasukan::verified()
            ->whereBetween('tanggal', [$start, $end])
            ->sum('jumlah');

        $totalPengeluaran = Pengeluaran::verified()
            ->whereBetween('tanggal', [$start, $end])
            ->sum('jumlah');

        $saldo = $totalPemasukan - $totalPengeluaran;

        // Pemasukan per kategori
        $pemasukanPerKategori = Pemasukan::verified()
            ->whereBetween('tanggal', [$start, $end])
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->groupBy('kategori')
            ->get();

        // Pengeluaran per kategori
        $pengeluaranPerKategori = Pengeluaran::verified()
            ->whereBetween('tanggal', [$start, $end])
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->groupBy('kategori')
            ->get();

        // Trend harian (last 30 days or range)
        $trendPemasukan = Pemasukan::verified()
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trendPengeluaran = Pengeluaran::verified()
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Transaksi terbaru (combined)
        $transaksiTerbaru = DB::table(function($query) use ($start, $end) {
                $query->select('id', 'tanggal', 'nomor_transaksi', 'kategori', 'jumlah', 'status', 'created_at', DB::raw("'pemasukan' as type"))
                    ->from('pemasukans')
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('status', 'verified')
                    ->unionAll(
                        DB::table('pengeluarans')
                            ->select('id', 'tanggal', 'nomor_transaksi', 'kategori', 'jumlah', 'status', 'created_at', DB::raw("'pengeluaran' as type"))
                            ->whereBetween('tanggal', [$start, $end])
                            ->where('status', 'verified')
                    );
            })
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();

        // Pending count
        $pendingPemasukan = Pemasukan::pending()->count();
        $pendingPengeluaran = Pengeluaran::pending()->count();

        // Year comparison
        $currentYear = date('Y');
        $lastYear = date('Y') - 1;

        $pemasukanTahunIni = Pemasukan::verified()
            ->whereYear('tanggal', $currentYear)
            ->sum('jumlah');

        $pemasukanTahunLalu = Pemasukan::verified()
            ->whereYear('tanggal', $lastYear)
            ->sum('jumlah');

        $growth = $pemasukanTahunLalu > 0 
            ? (($pemasukanTahunIni - $pemasukanTahunLalu) / $pemasukanTahunLalu) * 100 
            : 0;

        return view('features.keuangan.dashboard', compact(
            'start', 'end', 'totalPemasukan', 'totalPengeluaran', 'saldo',
            'pemasukanPerKategori', 'pengeluaranPerKategori',
            'trendPemasukan', 'trendPengeluaran', 'transaksiTerbaru',
            'pendingPemasukan', 'pendingPengeluaran',
            'pemasukanTahunIni', 'pemasukanTahunLalu', 'growth'
        ));
    }

    /**
     * Laporan/Rekap Keuangan
     */
    public function laporan(Request $request)
    {
        // Get filters
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));
        $kategori = $request->get('kategori');

        // Get all verified transactions
        $pemasukans = Pemasukan::with('creator')
            ->verified()
            ->whereBetween('tanggal', [$start, $end])
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->orderBy('tanggal', 'desc')
            ->get();

        $pengeluarans = Pengeluaran::with('creator')
            ->verified()
            ->whereBetween('tanggal', [$start, $end])
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate totals
        $totalPemasukan = $pemasukans->sum('jumlah');
        $totalPengeluaran = $pengeluarans->sum('jumlah');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        // Group by date for report
        $transaksiPerHari = [];
        
        foreach ($pemasukans as $p) {
            $date = $p->tanggal->format('Y-m-d');
            if (!isset($transaksiPerHari[$date])) {
                $transaksiPerHari[$date] = ['pemasukan' => 0, 'pengeluaran' => 0, 'transactions' => []];
            }
            $transaksiPerHari[$date]['pemasukan'] += $p->jumlah;
            $transaksiPerHari[$date]['transactions'][] = ['type' => 'pemasukan', 'data' => $p];
        }

        foreach ($pengeluarans as $p) {
            $date = $p->tanggal->format('Y-m-d');
            if (!isset($transaksiPerHari[$date])) {
                $transaksiPerHari[$date] = ['pemasukan' => 0, 'pengeluaran' => 0, 'transactions' => []];
            }
            $transaksiPerHari[$date]['pengeluaran'] += $p->jumlah;
            $transaksiPerHari[$date]['transactions'][] = ['type' => 'pengeluaran', 'data' => $p];
        }

        // Sort by date descending
        krsort($transaksiPerHari);

        return view('features.keuangan.laporan', compact(
            'start', 'end', 'kategori',
            'pemasukans', 'pengeluarans',
            'totalPemasukan', 'totalPengeluaran', 'saldoAkhir',
            'transaksiPerHari'
        ));
    }

    /**
     * Export Laporan Keuangan ke Excel/CSV
     */
    public function exportLaporan(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

        $pemasukans = Pemasukan::with('creator')
            ->verified()
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'desc')
            ->get();

        $pengeluarans = Pengeluaran::with('creator')
            ->verified()
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'desc')
            ->get();

        $filename = 'Laporan_Keuangan_' . $start . '_to_' . $end . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pemasukans, $pengeluarans, $start, $end) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Title
            fputcsv($file, ['LAPORAN KEUANGAN GEREJA FILADELFIA']);
            fputcsv($file, ['Periode: ' . date('d F Y', strtotime($start)) . ' - ' . date('d F Y', strtotime($end))]);
            fputcsv($file, []);
            
            // Summary
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Pemasukan', 'Rp ' . number_format($pemasukans->sum('jumlah'), 0, ',', '.')]);
            fputcsv($file, ['Total Pengeluaran', 'Rp ' . number_format($pengeluarans->sum('jumlah'), 0, ',', '.')]);
            fputcsv($file, ['Saldo', 'Rp ' . number_format($pemasukans->sum('jumlah') - $pengeluarans->sum('jumlah'), 0, ',', '.')]);
            fputcsv($file, []);
            
            // Pemasukan Section
            fputcsv($file, ['=== PEMASUKAN ===']);
            fputcsv($file, ['Tanggal', 'No. Transaksi', 'Kategori', 'Sumber Dana', 'Jumlah', 'Dibuat Oleh', 'Keterangan']);
            foreach ($pemasukans as $p) {
                fputcsv($file, [
                    $p->tanggal->format('d/m/Y'),
                    $p->nomor_transaksi,
                    $p->kategori_label,
                    $p->sumber_dana,
                    number_format($p->jumlah, 0, ',', '.'),
                    $p->creator->name ?? '-',
                    $p->keterangan ?? '-'
                ]);
            }
            fputcsv($file, ['SUBTOTAL PEMASUKAN', '', '', '', number_format($pemasukans->sum('jumlah'), 0, ',', '.'), '', '']);
            fputcsv($file, []);
            
            // Pengeluaran Section
            fputcsv($file, ['=== PENGELUARAN ===']);
            fputcsv($file, ['Tanggal', 'No. Transaksi', 'Kategori', 'Keperluan', 'Penerima', 'Jumlah', 'Dibuat Oleh', 'Keterangan']);
            foreach ($pengeluarans as $p) {
                fputcsv($file, [
                    $p->tanggal->format('d/m/Y'),
                    $p->nomor_transaksi,
                    $p->kategori_label,
                    $p->keperluan,
                    $p->penerima,
                    number_format($p->jumlah, 0, ',', '.'),
                    $p->creator->name ?? '-',
                    $p->keterangan ?? '-'
                ]);
            }
            fputcsv($file, ['SUBTOTAL PENGELUARAN', '', '', '', '', number_format($pengeluarans->sum('jumlah'), 0, ',', '.'), '', '']);
            fputcsv($file, []);
            
            // Final Summary
            fputcsv($file, ['SALDO AKHIR', '', '', '', '', 'Rp ' . number_format($pemasukans->sum('jumlah') - $pengeluarans->sum('jumlah'), 0, ',', '.'), '', '']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API untuk Chart/Graph
     */
    public function chartData(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

        $data = [
            'pemasukan' => [
                'total' => Pemasukan::verified()->whereBetween('tanggal', [$start, $end])->sum('jumlah'),
                'per_kategori' => Pemasukan::verified()
                    ->whereBetween('tanggal', [$start, $end])
                    ->select('kategori', DB::raw('SUM(jumlah) as total'))
                    ->groupBy('kategori')
                    ->get(),
                'trend' => Pemasukan::verified()
                    ->whereBetween('tanggal', [$start, $end])
                    ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ],
            'pengeluaran' => [
                'total' => Pengeluaran::verified()->whereBetween('tanggal', [$start, $end])->sum('jumlah'),
                'per_kategori' => Pengeluaran::verified()
                    ->whereBetween('tanggal', [$start, $end])
                    ->select('kategori', DB::raw('SUM(jumlah) as total'))
                    ->groupBy('kategori')
                    ->get(),
                'trend' => Pengeluaran::verified()
                    ->whereBetween('tanggal', [$start, $end])
                    ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ],
        ];

        return response()->json($data);
    }
}