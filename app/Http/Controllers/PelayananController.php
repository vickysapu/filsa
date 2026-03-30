<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Jemaat;
use App\Models\KategoriPelayanan;
use App\Models\Pelayanan;
use App\Models\PelayananDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PelayananController extends Controller
{
    /**
     * Display a listing of the jadwal pelayanan
     */
    public function index(Request $request)
    {
        $query = Pelayanan::with(['details.jemaat'])
            ->latest('tanggal');

        // Filter Kategori
        if ($request->filled('kategori_id')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Tanggal Range
        if ($request->filled('tanggal_start')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_start);
        }
        if ($request->filled('tanggal_end')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_end);
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_pelayanan', 'LIKE', "%{$search}%")
                    ->orWhere('lokasi', 'LIKE', "%{$search}%")
                    ->orWhere('nomor_jadwal', 'LIKE', "%{$search}%");
            });
        }

        $pelayanans = $query->paginate(12)->appends($request->query());

        $kategoris = KategoriPelayanan::active()->ordered()->get();

        // AJAX untuk reload list (filter & pagination)
        if ($request->ajax() || $request->input('ajax') == 1) {
            return view('features.pelayanan._list', compact('pelayanans', 'kategoris'))->render();
        }

        return view('features.pelayanan.index', compact('pelayanans', 'kategoris'));
    }

    /**
     * Show the form for creating a new jadwal (opsional, jika pakai halaman terpisah)
     * Untuk modal, tidak perlu method ini karena create via modal
     */
    public function create(Request $request)
    {
        $jemaats = Jemaat::where('status_jemaat', true)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'no_hp']);
        $kategoris = KategoriPelayanan::active()->ordered()->get();

        if ($request->ajax() || $request->input('ajax') == 1) {
            $selected = $request->input('selected', []); // array ID yang sudah dipilih (kosong saat create)

            $petugas_html = view('features.pelayanan._petugas_list', [
                'jemaats'  => $jemaats,
                'kategoris' => $kategoris,
                'selected' => $selected,   // untuk pre-check checkbox
                'roles'    => [],          // kosong saat create
            ])->render();

            return response()->json([
                'success'      => true,
                'petugas_html' => $petugas_html,
            ]);
        }

        // Non-AJAX (jika dibutuhkan halaman create terpisah)
        return view('features.pelayanan.create', compact('kategoris', 'jemaats'));
    }

    /**
     * Store a newly created jadwal pelayanan in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelayanan'  => 'required|string|max:255',
            'tanggal'         => 'required|date|after_or_equal:2026-03-01',
            'waktu'           => 'required',
            'lokasi'          => 'required|string|max:255',
            'keterangan'      => 'nullable|string|max:1000',
            'status'          => 'required|in:draft,active',
            'jemaat_ids'      => 'required|array|min:1',
            'jemaat_ids.*'    => 'exists:jemaats,id',
            'kategori_id'     => 'required|array',
            'kategori_id.*'   => 'required|exists:kategori_pelayanans,id',
            'roles'           => 'nullable|array',
            'roles.*'         => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $pelayanan = Pelayanan::create([
                'branch_id' => Auth::user()->branch_id,
                'kategori_id'     => collect($validated['kategori_id'])->first(),
                'nama_pelayanan'  => $validated['nama_pelayanan'],
                'tanggal'         => $validated['tanggal'],
                'waktu'           => \Carbon\Carbon::createFromFormat('H:i', $validated['waktu']),
                'lokasi'          => $validated['lokasi'],
                'keterangan'      => $validated['keterangan'] ?? null,
                'status'          => $validated['status'] ?? 'active',
                'created_by'      => Auth::id(),
                'nomor_jadwal'    => Pelayanan::generateNomorJadwalForDate($validated['tanggal']),
            ]);

            // Create details individually
            foreach ($validated['jemaat_ids'] as $jemaatId) {
                PelayananDetail::create([
                    'branch_id' => Auth::user()->branch_id,
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id'    => $jemaatId,
                    'kategori_id'  => $validated['kategori_id'][$jemaatId],
                    'role'         => $validated['roles'][$jemaatId] ?? null,
                    'status'       => 'active',
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Jadwal pelayanan berhasil dibuat dengan {$pelayanan->details->count()} petugas!"
                ]);
            }

            return redirect()->route('admin.jadwal.index')
                ->with('success', 'Jadwal pelayanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified jadwal (untuk modal AJAX)
     */
    public function edit(Pelayanan $pelayanan, Request $request)
    {
        if ($request->ajax() || $request->input('ajax') == 1) {
            $jemaats = Jemaat::where('status_jemaat', true)
                ->orderBy('nama_lengkap')
                ->get();
            $kategoris = KategoriPelayanan::active()->ordered()->get();

            $petugas_html = view('features.pelayanan._petugas_list', [
                'jemaats'  => $jemaats,
                'kategoris' => $kategoris,
                'selected' => $pelayanan->details->pluck('jemaat_id')->toArray(),
                'roles'    => $pelayanan->details->pluck('role', 'jemaat_id')->toArray(),
                'kategori_ids' => $pelayanan->details->pluck('kategori_id', 'jemaat_id')->toArray(),
            ])->render();

            return response()->json([
                'success'        => true,
                // 'kategori_id'    => $pelayanan->kategori_id,
                'nama_pelayanan' => $pelayanan->nama_pelayanan,
                'tanggal'        => $pelayanan->tanggal->format('Y-m-d'),
                'waktu'          => $pelayanan->waktu ? \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') : '08:00', // FIX: hanya H:i
                'lokasi'         => $pelayanan->lokasi,
                'keterangan'     => $pelayanan->keterangan ?? '',
                'status'         => $pelayanan->status,
                'selected_jemaat_ids' => $pelayanan->details->pluck('jemaat_id')->toArray(),
                'roles'          => $pelayanan->details->pluck('role', 'jemaat_id')->toArray(),
                'petugas_html'   => $petugas_html,
            ]);
        }

        // Non-AJAX fallback
        abort(404);
    }

    /**
     * Update the specified jadwal pelayanan
     */
    public function update(Request $request, Pelayanan $pelayanan)
    {
        if (in_array($pelayanan->status, ['completed', 'cancelled'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelayanan yang sudah selesai/dibatalkan tidak dapat diedit!'
                ], 403);
            }
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Pelayanan yang sudah selesai/dibatalkan tidak dapat diedit!');
        }

        $validated = $request->validate([
            'nama_pelayanan'  => 'required|string|max:255',
            'tanggal'         => 'required|date',
            'waktu'           => 'required',
            'lokasi'          => 'required|string|max:255',
            'keterangan'      => 'nullable|string|max:1000',
            'status'          => 'required|in:draft,active,completed,cancelled',
            'jemaat_ids'      => 'required|array|min:1',
            'jemaat_ids.*'    => 'exists:jemaats,id',
            'kategori_id'     => 'required|array',
            'kategori_id.*'   => 'required|exists:kategori_pelayanans,id',
            'roles'           => 'nullable|array',
            'roles.*'         => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $pelayanan->update([
                'kategori_id'     => collect($validated['kategori_id'])->first(),
                'nama_pelayanan'  => $validated['nama_pelayanan'],
                'tanggal'         => $validated['tanggal'],
                'waktu'           => \Carbon\Carbon::createFromFormat('H:i', $validated['waktu']),
                'lokasi'          => $validated['lokasi'],
                'keterangan'      => $validated['keterangan'] ?? null,
                'status'          => $validated['status'],
            ]);

            // Hapus detail lama, insert baru
            $pelayanan->details()->delete();

            $details = [];
            foreach ($validated['jemaat_ids'] as $jemaatId) {
                $details[] = [
                    'id'           => Str::uuid(),
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id'    => $jemaatId,
                    'role'         => $validated['roles'][$jemaatId] ?? null,
                    'status'       => 'active',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            PelayananDetail::insert($details);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal pelayanan berhasil diperbarui!'
                ]);
            }

            return redirect()->route('admin.jadwal.index')
                ->with('success', 'Jadwal pelayanan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified jadwal pelayanan
     */
    public function destroy(Pelayanan $pelayanan, Request $request)
    {
        $pelayanan->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelayanan berhasil dihapus!'
            ]);
        }

        return redirect()->route('admin.jadwal.index')
            ->with('success', 'Jadwal pelayanan berhasil dihapus!');
    }

    // Method lain tetap dipertahankan
    public function setOff(Request $request, PelayananDetail $detail)
    {
        $validated = $request->validate([
            'alasan_off' => 'required|string|max:500',
        ]);

        $detail->update([
            'status'      => 'off',
            'tanggal_off' => now(),
            'alasan_off'  => $validated['alasan_off'],
            'updated_by'  => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Petugas berhasil ditandai sebagai OFF!');
    }

    public function setActive(PelayananDetail $detail)
    {
        $detail->update([
            'status'      => 'active',
            'tanggal_off' => null,
            'alasan_off'  => null,
            'updated_by'  => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Petugas berhasil diaktifkan kembali!');
    }

    public function searchJemaat(Request $request)
    {
        $search = $request->get('q', '');

        $jemaats = Jemaat::where('status_jemaat', true)
            ->where('nama_lengkap', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'nama_lengkap', 'no_hp']);

        return response()->json($jemaats);
    }
}
