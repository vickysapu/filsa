<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LatihanPelayanan;
use App\Models\KategoriPelayanan;
use App\Models\Jemaat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LatihanPelayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LatihanPelayanan::with(['kategori', 'details.jemaat', 'creator'])
                    ->latest('tanggal');

        // Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_latihan', 'LIKE', "%{$search}%")
                  ->orWhere('lokasi', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        $latihans = $query->paginate(12)->appends($request->query());

        $kategoris = KategoriPelayanan::active()->ordered()->get();

        // AJAX untuk reload list (card grid)
        if ($request->ajax() || $request->input('ajax') == 1) {
            return view('features.latihan-pelayanan._list', compact('latihans'))->render();
        }

        return view('features.latihan-pelayanan.index', compact('latihans', 'kategoris'));
    }

    /**
     * Show form untuk AJAX (load daftar petugas)
     */
    public function create(Request $request)
    {
        $jemaats = Jemaat::where('status_jemaat', true)
            ->orderBy('nama_lengkap')
            ->get();

        $selected = $request->json('selected') ?? [];

        $petugas_html = view('features.latihan-pelayanan._petugas_list', [
            'jemaats'  => $jemaats,
            'selected' => $selected,
            'roles'    => [],
        ])->render();

        return response()->json([
            'success'      => true,
            'petugas_html' => $petugas_html,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id'   => 'required|exists:kategori_pelayanans,id',
            'nama_latihan'  => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'waktu'         => 'required',
            'lokasi'        => 'nullable|string|max:255',
            'keterangan'    => 'nullable|string|max:1000',
            'status'        => 'in:active,cancelled',
            'jemaat_ids'    => 'required|array|min:1',
            'jemaat_ids.*'  => 'exists:jemaats,id',
            'roles'         => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $latihan = LatihanPelayanan::create([
                'pelayanan_id' => $request->pelayanan_id ?? null,   // jika terhubung dengan jadwal utama
                'kategori_id'  => $validated['kategori_id'],
                'nama_latihan' => $validated['nama_latihan'],
                'tanggal'      => $validated['tanggal'],
                'waktu'        => $validated['waktu'],
                'lokasi'       => $validated['lokasi'],
                'keterangan'   => $validated['keterangan'],
                'status'       => $validated['status'] ?? 'active',
                'created_by'   => Auth::id(),
            ]);

            // Simpan peserta latihan
            foreach ($validated['jemaat_ids'] as $jemaatId) {
                $latihan->details()->create([
                    'jemaat_id' => $jemaatId,
                    'role'      => $validated['roles'][$jemaatId] ?? null,
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->input('ajax') == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal latihan berhasil dibuat!'
                ]);
            }

            return redirect()->route('admin.latihan-pelayanan.index')
                ->with('success', 'Jadwal latihan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified resource (AJAX).
     */
    public function edit(LatihanPelayanan $latihan, Request $request)
    {
        if ($request->ajax() || $request->input('ajax') == 1) {
            $jemaats = Jemaat::where('status_jemaat', true)
                ->orderBy('nama_lengkap')
                ->get();

            $petugas_html = view('features.latihan-pelayanan._petugas_list', [
                'jemaats'  => $jemaats,
                'selected' => $latihan->details->pluck('jemaat_id')->toArray(),
                'roles'    => $latihan->details->pluck('role', 'jemaat_id')->toArray(),
            ])->render();

            return response()->json([
                'success'        => true,
                'kategori_id'    => $latihan->kategori_id,
                'nama_latihan'   => $latihan->nama_latihan,
                'tanggal'        => $latihan->tanggal->format('Y-m-d'),
                'waktu'          => $latihan->waktu,
                'lokasi'         => $latihan->lokasi,
                'keterangan'     => $latihan->keterangan,
                'status'         => $latihan->status,
                'petugas_html'   => $petugas_html,
            ]);
        }

        abort(404);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, LatihanPelayanan $latihan)
    {
        $validated = $request->validate([
            'kategori_id'   => 'required|exists:kategori_pelayanans,id',
            'nama_latihan'  => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'waktu'         => 'required',
            'lokasi'        => 'nullable|string|max:255',
            'keterangan'    => 'nullable|string|max:1000',
            'status'        => 'in:active,cancelled',
            'jemaat_ids'    => 'required|array|min:1',
            'jemaat_ids.*'  => 'exists:jemaats,id',
            'roles'         => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $latihan->update([
                'kategori_id'  => $validated['kategori_id'],
                'nama_latihan' => $validated['nama_latihan'],
                'tanggal'      => $validated['tanggal'],
                'waktu'        => $validated['waktu'],
                'lokasi'       => $validated['lokasi'],
                'keterangan'   => $validated['keterangan'],
                'status'       => $validated['status'],
            ]);

            // Hapus semua detail lama lalu insert ulang
            $latihan->details()->delete();

            foreach ($validated['jemaat_ids'] as $jemaatId) {
                $latihan->details()->create([
                    'jemaat_id' => $jemaatId,
                    'role'      => $validated['roles'][$jemaatId] ?? null,
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->input('ajax') == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal latihan berhasil diperbarui!'
                ]);
            }

            return redirect()->route('admin.latihan-pelayanan.index')
                ->with('success', 'Jadwal latihan berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat update.'
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(LatihanPelayanan $latihan, Request $request)
    {
        DB::beginTransaction();

        try {
            $latihan->details()->delete();
            $latihan->delete();

            DB::commit();

            if ($request->ajax() || $request->input('ajax') == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal latihan berhasil dihapus!'
                ]);
            }

            return redirect()->route('admin.latihan-pelayanan.index')
                ->with('success', 'Jadwal latihan berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus jadwal latihan.'
                ], 500);
            }
            throw $e;
        }
    }
}