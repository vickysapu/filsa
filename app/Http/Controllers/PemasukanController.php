<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pemasukan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PemasukanController extends Controller
{
    /**
     * Display listing with filters
     */
    public function index(Request $request)
    {
        $query = Pemasukan::with(['creator', 'verifier'])->latest();

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
                  ->orWhere('sumber_dana', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        $pemasukans = $query->paginate(15);

        // Statistics for current filter
        $stats = [
            'total' => (clone $query)->sum('jumlah'),
            'verified' => (clone $query)->where('status', 'verified')->sum('jumlah'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'count' => (clone $query)->count(),
        ];

        return view('features.pemasukan.index', compact('pemasukans', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('features.pemasukan.create');
    }

    /**
     * Store new pemasukan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'kategori' => 'required|in:persembahan_minggu,persembahan_khusus,donasi,kegiatan,usaha_mandiri,lainnya',
            'sumber_dana' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'in:pending,verified',
            'bukti_foto' => 'nullable|image|mimes:jpg,png,jpeg,pdf|max:5120', // Max 5MB
        ], [
            'jumlah.min' => 'Jumlah minimal Rp 1.000',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini',
        ]);

        // Handle upload bukti foto
        $buktiPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiPath = $request->file('bukti_foto')->store('pemasukan/bukti', 'public');
        }

        // Create pemasukan
        Pemasukan::create([
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'],
            'sumber_dana' => $validated['sumber_dana'],
            'jumlah' => $validated['jumlah'],
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'bukti_foto' => $buktiPath,
            'created_by' => Auth::id(),
            'verified_by' => $validated['status'] === 'verified' ? Auth::id() : null,
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
        ]);

        return redirect()->route('admin.pemasukan.index')
            ->with('success', 'Pemasukan berhasil ditambahkan!');
    }

    /**
     * Display detail
     */
    public function show(Pemasukan $pemasukan)
    {
        $pemasukan->load(['creator', 'verifier']);
        return view('features.pemasukan.show', compact('pemasukan'));
    }

    /**
     * Show edit form
     */
    public function edit(Pemasukan $pemasukan)
    {
        // Only pending can be edited
        if ($pemasukan->status === 'verified') {
            return redirect()->route('admin.pemasukan.index')
                ->with('error', 'Pemasukan yang sudah verified tidak dapat diedit!');
        }

        return view('features.pemasukan.edit', compact('pemasukan'));
    }

    /**
     * Update pemasukan
     */
    public function update(Request $request, Pemasukan $pemasukan)
    {
        // Only pending can be updated
        if ($pemasukan->status === 'verified') {
            return redirect()->route('admin.pemasukan.index')
                ->with('error', 'Pemasukan yang sudah verified tidak dapat diedit!');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'kategori' => 'required|in:persembahan_minggu,persembahan_khusus,donasi,kegiatan,usaha_mandiri,lainnya',
            'sumber_dana' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:1000',
            'bukti_foto' => 'nullable|image|mimes:jpg,png,jpeg,pdf|max:5120',
        ], [
            'jumlah.min' => 'Jumlah minimal Rp 1.000',
        ]);

        // Handle upload bukti foto baru
        $buktiPath = $pemasukan->bukti_foto;
        if ($request->hasFile('bukti_foto')) {
            // Delete old file
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }
            $buktiPath = $request->file('bukti_foto')->store('pemasukan/bukti', 'public');
        }

        $pemasukan->update([
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'],
            'sumber_dana' => $validated['sumber_dana'],
            'jumlah' => $validated['jumlah'],
            'keterangan' => $validated['keterangan'] ?? null,
            'bukti_foto' => $buktiPath,
        ]);

        return redirect()->route('features.pemasukan.index')
            ->with('success', 'Pemasukan berhasil diupdate!');
    }

    /**
     * Verify pemasukan
     */
    public function verify(Pemasukan $pemasukan)
    {
        if ($pemasukan->status === 'verified') {
            return redirect()->back()->with('error', 'Pemasukan sudah verified!');
        }

        $pemasukan->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pemasukan berhasil diverifikasi!');
    }

    /**
     * Reject pemasukan
     */
    public function reject(Request $request, Pemasukan $pemasukan)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|max:500',
        ]);

        $pemasukan->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'keterangan' => ($pemasukan->keterangan ?? '') . "\n[REJECTED: " . $validated['alasan_reject'] . "]",
        ]);

        return redirect()->back()->with('success', 'Pemasukan ditolak!');
    }

    /**
     * Delete pemasukan
     */
    public function destroy(Pemasukan $pemasukan)
    {
        // Only pending can be deleted
        if ($pemasukan->status === 'verified') {
            return redirect()->route('admin.pemasukan.index')
                ->with('error', 'Pemasukan yang sudah verified tidak dapat dihapus!');
        }

        // Delete bukti foto
        if ($pemasukan->bukti_foto) {
            Storage::disk('public')->delete($pemasukan->bukti_foto);
        }

        $pemasukan->delete();

        return redirect()->route('admin.pemasukan.index')
            ->with('success', 'Pemasukan berhasil dihapus!');
    }

    /**
     * Export to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = Pemasukan::with(['creator', 'verifier'])->verified();

        // Apply same filters as index
        if ($request->filled('tanggal_start') && $request->filled('tanggal_end')) {
            $query->whereBetween('tanggal', [$request->tanggal_start, $request->tanggal_end]);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $pemasukans = $query->orderBy('tanggal', 'desc')->get();

        // Generate CSV
        $filename = 'Laporan_Pemasukan_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pemasukans) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'No',
                'Tanggal',
                'No. Transaksi',
                'Kategori',
                'Sumber Dana',
                'Jumlah (Rp)',
                'Status',
                'Dibuat Oleh',
                'Diverifikasi Oleh',
                'Keterangan'
            ]);

            // Data rows
            $no = 1;
            foreach ($pemasukans as $pemasukan) {
                fputcsv($file, [
                    $no++,
                    $pemasukan->tanggal->format('d/m/Y'),
                    $pemasukan->nomor_transaksi,
                    $pemasukan->kategori_label,
                    $pemasukan->sumber_dana,
                    number_format($pemasukan->jumlah, 0, ',', '.'),
                    $pemasukan->status_label,
                    $pemasukan->creator->name ?? '-',
                    $pemasukan->verifier->name ?? '-',
                    $pemasukan->keterangan ?? '-'
                ]);
            }

            // Total row
            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL',
                '',
                '',
                '',
                '',
                number_format($pemasukans->sum('jumlah'), 0, ',', '.'),
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
            'total' => Pemasukan::getTotalVerified($start, $end),
            'per_kategori' => Pemasukan::getTotalPerKategori($start, $end),
            'trend' => Pemasukan::verified()
                ->whereBetween('tanggal', [$start, $end])
                ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($stats);
    }
}