<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PengeluaranController extends Controller
{
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

        $pengeluarans = $query->paginate(15)->appends($request->query());

        // Statistics
        $stats = [
            'total'    => (clone $query)->sum('jumlah'),
            'verified' => (clone $query)->where('status', 'verified')->sum('jumlah'),
            'pending'  => (clone $query)->where('status', 'pending')->count(),
            'count'    => (clone $query)->count(),
        ];

        if ($request->ajax() || $request->input('ajax') == 1) {
            return view('features.pengeluaran._list', compact('pengeluarans'))->render();
        }

        return view('features.pengeluaran.index', compact('pengeluarans', 'stats'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tanggal'     => 'required|date|before_or_equal:today',
                'kategori'    => 'required|in:operasional,listrik_air,gaji_karyawan,kegiatan,pemeliharaan,amal_sosial,konsumsi,lainnya',
                'keperluan'   => 'required|string|max:255',
                'penerima'    => 'required|string|max:255',
                'jumlah'      => 'required|numeric|min:1000',
                'keterangan'  => 'nullable|string|max:1000',
                'status'      => 'nullable|in:pending,verified',
                'bukti_foto'  => 'nullable|file|mimes:jpg,png,jpeg,pdf|max:5120',
            ], [
                'jumlah.min'             => 'Jumlah minimal Rp 1.000',
                'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini',
                'bukti_foto.max'         => 'Ukuran file maksimal 5MB',
            ]);

            $buktiPath = null;
            if ($request->hasFile('bukti_foto') && $request->file('bukti_foto')->isValid()) {
                $buktiPath = $request->file('bukti_foto')->store('pengeluaran/bukti', 'public');
            }

            // Wrap in transaction to ensure unique transaction number generation
            $pengeluaran = DB::transaction(function () use ($validated, $buktiPath) {
                return Pengeluaran::create([
                    'tanggal'     => $validated['tanggal'],
                    'kategori'    => $validated['kategori'],
                    'keperluan'   => $validated['keperluan'],
                    'penerima'    => $validated['penerima'],
                    'jumlah'      => $validated['jumlah'],
                    'keterangan'  => $validated['keterangan'] ?? null,
                    'status'      => $validated['status'] ?? 'pending',
                    'bukti_foto'  => $buktiPath,
                    'created_by'  => Auth::id(),
                    'verified_by' => $validated['status'] === 'verified' ? Auth::id() : null,
                    'verified_at' => $validated['status'] === 'verified' ? now() : null,
                ]);
            });

            if ($request->ajax() || $request->input('ajax') == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengeluaran berhasil ditambahkan!',
                    'id'      => $pengeluaran->id // optional
                ]);
            }

            return redirect()->route('admin.pengeluaran.index')
                ->with('success', 'Pengeluaran berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->input('ajax') == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error create pengeluaran: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($request->ajax() || $request->input('ajax') == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Pengeluaran $pengeluaran, Request $request)
    {
        if ($request->ajax() || $request->input('ajax') == 1) {
            if ($pengeluaran->status === 'verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran yang sudah verified tidak dapat diedit!'
                ], 403);
            }

            return response()->json([
                'success'     => true,
                'tanggal'     => $pengeluaran->tanggal->format('Y-m-d'),
                'kategori'    => $pengeluaran->kategori,
                'keperluan'   => $pengeluaran->keperluan,
                'penerima'    => $pengeluaran->penerima,
                'jumlah'      => $pengeluaran->jumlah,
                'keterangan'  => $pengeluaran->keterangan,
                'status'      => $pengeluaran->status,
                'bukti_foto'  => $pengeluaran->bukti_foto,
            ]);
        }

        // Non-AJAX fallback (jika perlu)
        abort(404);
    }

    /**
     * Display the specified pengeluaran (detail)
     */
    public function show(Pengeluaran $pengeluaran)
    {
        $pengeluaran->load(['creator', 'verifier']);

        // Jika request AJAX (misal untuk modal detail), return JSON
        if (request()->ajax() || request()->input('ajax') == 1) {
            return response()->json([
                'success'     => true,
                'data'        => $pengeluaran,
                'tanggal'     => $pengeluaran->tanggal->format('d F Y'),
                'nomor'       => $pengeluaran->nomor_transaksi,
                'kategori'    => $pengeluaran->kategori_label,
                'keperluan'   => $pengeluaran->keperluan,
                'penerima'    => $pengeluaran->penerima,
                'jumlah'      => number_format($pengeluaran->jumlah, 0, ',', '.'),
                'status'      => $pengeluaran->status_label,
                'creator'     => $pengeluaran->creator->name ?? '-',
                'verifier'    => $pengeluaran->verifier->name ?? '-',
                'bukti_foto'  => $pengeluaran->bukti_foto ? asset('storage/' . $pengeluaran->bukti_foto) : null,
                'keterangan'  => $pengeluaran->keterangan ?? '-',
            ]);
        }

        // Non-AJAX: tampilkan view detail
        return view('features.pengeluaran.show', compact('pengeluaran'));
    }

    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        if ($pengeluaran->status === 'verified') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran yang sudah verified tidak dapat diedit!'
                ], 403);
            }
            return redirect()->route('admin.pengeluaran.index')
                ->with('error', 'Pengeluaran yang sudah verified tidak dapat diedit!');
        }

        $validated = $request->validate([
            'tanggal'     => 'required|date|before_or_equal:today',
            'kategori'    => 'required|in:operasional,listrik_air,gaji_karyawan,kegiatan,pemeliharaan,amal_sosial,konsumsi,lainnya',
            'keperluan'   => 'required|string|max:255',
            'penerima'    => 'required|string|max:255',
            'jumlah'      => 'required|numeric|min:1000',
            'keterangan'  => 'nullable|string|max:1000',
            'bukti_foto'  => 'nullable|image|mimes:jpg,png,jpeg,pdf|max:5120',
        ], [
            'jumlah.min' => 'Jumlah minimal Rp 1.000',
        ]);

        $buktiPath = $pengeluaran->bukti_foto;
        if ($request->hasFile('bukti_foto') && $request->file('bukti_foto')->isValid()) {
            if ($buktiPath) Storage::disk('public')->delete($buktiPath);
            $buktiPath = $request->file('bukti_foto')->store('pengeluaran/bukti', 'public');
        }

        $pengeluaran->update([
            'tanggal'     => $validated['tanggal'],
            'kategori'    => $validated['kategori'],
            'keperluan'   => $validated['keperluan'],
            'penerima'    => $validated['penerima'],
            'jumlah'      => $validated['jumlah'],
            'keterangan'  => $validated['keterangan'] ?? null,
            'bukti_foto'  => $buktiPath,
        ]);

        if ($request->ajax() || $request->input('ajax') == 1) {
            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil diperbarui!'
            ]);
        }

        return redirect()->route('admin.pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    public function destroy(Pengeluaran $pengeluaran, Request $request)
    {
        if ($pengeluaran->status === 'verified') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran yang sudah verified tidak dapat dihapus!'
                ], 403);
            }
            return redirect()->route('admin.pengeluaran.index')
                ->with('error', 'Pengeluaran yang sudah verified tidak dapat dihapus!');
        }

        if ($pengeluaran->bukti_foto) {
            Storage::disk('public')->delete($pengeluaran->bukti_foto);
        }

        $pengeluaran->delete();

        if ($request->ajax() || $request->input('ajax') == 1) {
            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil dihapus!'
            ]);
        }

        return redirect()->route('admin.pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil dihapus!');
    }

    /**
     * Verify pengeluaran
     */
    public function verify(Request $request, Pengeluaran $pengeluaran)
    {
        if ($pengeluaran->status === 'verified') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran sudah terverifikasi!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Pengeluaran sudah terverifikasi!');
        }

        $pengeluaran->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil diverifikasi!'
            ]);
        }

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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran ditolak!'
            ]);
        }

        return redirect()->back()->with('success', 'Pengeluaran ditolak!');
    }

    /**
     * Export pengeluaran data to CSV/Excel
     */
    public function export(Request $request)
    {
        $query = Pengeluaran::query();

        // Apply same filters as index
        if ($request->filled('tanggal_start') && $request->filled('tanggal_end')) {
            $query->whereBetween('tanggal', [$request->tanggal_start, $request->tanggal_end]);
        } elseif ($request->filled('tanggal_start')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_start);
        } elseif ($request->filled('tanggal_end')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_end);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengeluarans = $query->with(['creator', 'verifier'])->get();

        $callback = function () use ($pengeluarans) {
            $file = fopen('php://output', 'w');

            // Header row
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

            // Data rows
            $no = 1;
            foreach ($pengeluarans as $pengeluaran) {
                fputcsv($file, [
                    $no++,
                    $pengeluaran->tanggal->format('d/m/Y'),
                    $pengeluaran->nomor_transaksi,
                    $pengeluaran->kategori,
                    $pengeluaran->keperluan,
                    $pengeluaran->penerima,
                    number_format($pengeluaran->jumlah, 0, ',', '.'),
                    $pengeluaran->status,
                    $pengeluaran->creator->name ?? '-',
                    $pengeluaran->verifier->name ?? '-',
                    $pengeluaran->keterangan ?? '-'
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
                '',
                number_format($pengeluarans->sum('jumlah'), 0, ',', '.'),
                '',
                '',
                '',
                ''
            ]);

            fclose($file);
        };

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="pengeluaran_' . now()->format('Y-m-d_His') . '.csv"',
        ];

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
            'total' => Pengeluaran::verified()
                ->whereBetween('tanggal', [$start, $end])
                ->sum('jumlah'),
            'per_kategori' => Pengeluaran::verified()
                ->whereBetween('tanggal', [$start, $end])
                ->selectRaw('kategori, SUM(jumlah) as total')
                ->groupBy('kategori')
                ->get(),
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