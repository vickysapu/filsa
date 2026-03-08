<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PengeluaranController extends Controller
{
    /**
     * Display listing with filters
     */
    public function index(Request $request)
    {
        $query = Pengeluaran::with(['creator', 'verifier'])->latest();

        // Filter Tanggal
        if ($request->filled('tanggal_start') && $request->filled('tanggal_end')) {
            $query->whereBetween('tanggal', [$request->tanggal_start, $request->tanggal_end]);
        } elseif ($request->filled('tanggal_start')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_start);
        } elseif ($request->filled('tanggal_end')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_end);
        }

        // Filter Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_transaksi', 'LIKE', "%{$search}%")
                  ->orWhere('keperluan', 'LIKE', "%{$search}%")
                  ->orWhere('penerima', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        $pengeluarans = $query->paginate(15);

        // Statistics for current filter
        $stats = [
            'total' => (clone $query)->sum('jumlah'),
            'verified' => (clone $query)->where('status', 'verified')->sum('jumlah'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'count' => (clone $query)->count(),
        ];

        return view('features.pengeluaran.index', compact('pengeluarans', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('features.pengeluaran.create');
    }

    /**
     * Store new pengeluaran
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'kategori' => 'required|in:operasional,listrik_air,gaji_karyawan,kegiatan,pemeliharaan,amal_sosial,konsumsi,lainnya',
            'keperluan' => 'required|string|max:255',
            'penerima' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'in:pending,verified',
            'bukti_foto' => 'nullable|image|mimes:jpg,png,jpeg,pdf|max:5120',
        ], [
            'jumlah.min' => 'Jumlah minimal Rp 1.000',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini',
        ]);

        // Handle upload bukti foto
        $buktiPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiPath = $request->file('bukti_foto')->store('pengeluaran/bukti', 'public');
        }

        // Create pengeluaran
        Pengeluaran::create([
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'],
            'keperluan' => $validated['keperluan'],
            'penerima' => $validated['penerima'],
            'jumlah' => $validated['jumlah'],
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'bukti_foto' => $buktiPath,
            'created_by' => Auth::id(),
            'verified_by' => $validated['status'] === 'verified' ? Auth::id() : null,
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
        ]);

        return redirect()->route('admin.pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    /**
     * Display detail
     */
    public function show(Pengeluaran $pengeluaran)
    {
        $pengeluaran->load(['creator', 'verifier']);
        return view('features.pengeluaran.show', compact('pengeluaran'));
    }

    /**
     * Show edit form
     */
    public function edit(Pengeluaran $pengeluaran)
    {
        if ($pengeluaran->status === 'verified') {
            return redirect()->route('admin.pengeluaran.index')
                ->with('error', 'Pengeluaran yang sudah verified tidak dapat diedit!');
        }

        return view('features.pengeluaran.edit', compact('pengeluaran'));
    }

    /**
     * Update pengeluaran
     */
    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        if ($pengeluaran->status === 'verified') {
            return redirect()->route('admin.pengeluaran.index')
                ->with('error', 'Pengeluaran yang sudah verified tidak dapat diedit!');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'kategori' => 'required|in:operasional,listrik_air,gaji_karyawan,kegiatan,pemeliharaan,amal_sosial,konsumsi,lainnya',
            'keperluan' => 'required|string|max:255',
            'penerima' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:1000',
            'bukti_foto' => 'nullable|image|mimes:jpg,png,jpeg,pdf|max:5120',
        ], [
            'jumlah.min' => 'Jumlah minimal Rp 1.000',
        ]);

        // Handle upload bukti foto baru
        $buktiPath = $pengeluaran->bukti_foto;
        if ($request->hasFile('bukti_foto')) {
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }
            $buktiPath = $request->file('bukti_foto')->store('pengeluaran/bukti', 'public');
        }

        $pengeluaran->update([
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'],
            'keperluan' => $validated['keperluan'],
            'penerima' => $validated['penerima'],
            'jumlah' => $validated['jumlah'],
            'keterangan' => $validated['keterangan'] ?? null,
            'bukti_foto' => $buktiPath,
        ]);

        return redirect()->route('admin.pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil diupdate!');
    }

    /**
     * Verify pengeluaran
     */
    public function verify(Pengeluaran $pengeluaran)
    {
        if ($pengeluaran->status === 'verified') {
            return redirect()->back()->with('error', 'Pengeluaran sudah verified!');
        }

        $pengeluaran->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pengeluaran berhasil diverifikasi!');
    }

    /**
     * Reject pengeluaran
     */
    public function reject(Request $request, Pengeluaran $pengeluaran)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|max:500',
        ]);

        $pengeluaran->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'keterangan' => ($pengeluaran->keterangan ?? '') . "\n[REJECTED: " . $validated['alasan_reject'] . "]",
        ]);

        return redirect()->back()->with('success', 'Pengeluaran ditolak!');
    }

    /**
     * Delete pengeluaran
     */
    public function destroy(Pengeluaran $pengeluaran)
    {
        if ($pengeluaran->status === 'verified') {
            return redirect()->route('admin.pengeluaran.index')
                ->with('error', 'Pengeluaran yang sudah verified tidak dapat dihapus!');
        }

        if ($pengeluaran->bukti_foto) {
            Storage::disk('public')->delete($pengeluaran->bukti_foto);
        }

        $pengeluaran->delete();

        return redirect()->route('admin.pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil dihapus!');
    }

    /**
     * Export to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = Pengeluaran::with(['creator', 'verifier'])->verified();

        if ($request->filled('tanggal_start') && $request->filled('tanggal_end')) {
            $query->whereBetween('tanggal', [$request->tanggal_start, $request->tanggal_end]);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $pengeluarans = $query->orderBy('tanggal', 'desc')->get();

        $filename = 'Laporan_Pengeluaran_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pengeluarans) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'No',
                'Tanggal',
                'No. Transaksi',
                'Kategori',
                'Keperluan',
                'Penerima',
                'Jumlah (Rp)',
                'Status',
                'Dibuat Oleh',
                'Diverifikasi Oleh',
                'Keterangan'
            ]);

            $no = 1;
            foreach ($pengeluarans as $pengeluaran) {
                fputcsv($file, [
                    $no++,
                    $pengeluaran->tanggal->format('d/m/Y'),
                    $pengeluaran->nomor_transaksi,
                    $pengeluaran->kategori_label,
                    $pengeluaran->keperluan,
                    $pengeluaran->penerima,
                    number_format($pengeluaran->jumlah, 0, ',', '.'),
                    $pengeluaran->status_label,
                    $pengeluaran->creator->name ?? '-',
                    $pengeluaran->verifier->name ?? '-',
                    $pengeluaran->keterangan ?? '-'
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                number_format($pengeluarans->sum('jumlah'), 0, ',', '.'),
                '',
                '',
                '',
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics for dashboard
     */
    public function statistics(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());

        $stats = [
            'total' => Pengeluaran::getTotalVerified($start, $end),
            'per_kategori' => Pengeluaran::getTotalPerKategori($start, $end),
            'trend' => Pengeluaran::verified()
                ->whereBetween('tanggal', [$start, $end])
                ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($stats);
    }
}