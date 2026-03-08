<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pelayanan;
use App\Models\KategoriPelayanan;
use App\Models\Jemaat;
use App\Models\PelayananDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelayananController extends Controller
{
    /**
     * Display listing of jadwal pelayanan
     */
    public function index(Request $request)
    {
        $query = Pelayanan::with(['kategori', 'creator', 'details.jemaat'])->latest();

        // Filter Kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Tanggal Range
        if ($request->filled('tanggal_start') && $request->filled('tanggal_end')) {
            $query->whereBetween('tanggal', [$request->tanggal_start, $request->tanggal_end]);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelayanan', 'LIKE', "%{$request->search}%")
                    ->orWhere('nomor_jadwal', 'LIKE', "%{$request->search}%")
                    ->orWhere('lokasi', 'LIKE', "%{$request->search}%");
            });
        }

        $pelayanans = $query->paginate(12);
        $kategoris = KategoriPelayanan::active()->ordered()->get();

        return view('features.pelayanan.index', compact('pelayanans', 'kategoris'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $kategoris = KategoriPelayanan::active()->ordered()->get();
        $jemaats = Jemaat::where('status_jemaat', true)->orderBy('nama_lengkap')->get();

        return view('features.pelayanan.create', compact('kategoris', 'jemaats'));
    }

    /**
     * Store new jadwal pelayanan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_pelayanans,id',
            'nama_pelayanan' => 'required|string|max:255',
            'tanggal' => 'required|date|after_or_equal:today',
            'waktu' => 'required',
            'lokasi' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'in:draft,active',
            'jemaat_ids' => 'required|array|min:1',
            'jemaat_ids.*' => 'exists:jemaats,id',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Create jadwal pelayanan
            $pelayanan = Pelayanan::create([
                'kategori_id' => $validated['kategori_id'],
                'nama_pelayanan' => $validated['nama_pelayanan'],
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'],
                'lokasi' => $validated['lokasi'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'created_by' => Auth::id(),
            ]);

            // Bulk insert detail pelayan
            $details = [];
            foreach ($validated['jemaat_ids'] as $index => $jemaatId) {
                $details[] = [
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id' => $jemaatId,
                    'role' => $validated['roles'][$index] ?? null,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            PelayananDetail::insert($details);

            DB::commit();

            return redirect()
                ->route('admin.jadwal.index')
                ->with('success', "Jadwal pelayanan berhasil dibuat dengan {$pelayanan->details->count()} petugas!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display detail jadwal
     */
    public function show(Pelayanan $pelayanan)
    {
        $pelayanan->load(['kategori', 'creator', 'details.jemaat']);
        return view('features.pelayanan.show', compact('pelayanan'));
    }

    /**
     * Show edit form
     */
    public function edit(Pelayanan $pelayanan)
    {
        if ($pelayanan->status === 'completed' || $pelayanan->status === 'cancelled') {
            return redirect()->route('admin.jadwal.index')->with('error', 'Pelayanan yang sudah selesai/dibatalkan tidak dapat diedit!');
        }

        $kategoris = KategoriPelayanan::active()->ordered()->get();
        $jemaats = Jemaat::where('status_jemaat', true)->orderBy('nama_lengkap')->get();

        $selectedJemaats = $pelayanan->details()->where('status', 'active')->pluck('jemaat_id', 'role')->toArray();

        return view('features.pelayanan.edit', compact('pelayanan', 'kategoris', 'jemaats', 'selectedJemaats'));
    }

    /**
     * Update jadwal pelayanan
     */
    public function update(Request $request, Pelayanan $pelayanan)
    {
        if ($pelayanan->status === 'completed' || $pelayanan->status === 'cancelled') {
            return redirect()->route('admin.jadwal.index')->with('error', 'Pelayanan yang sudah selesai/dibatalkan tidak dapat diedit!');
        }

        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori_pelayanans,id',
            'nama_pelayanan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'lokasi' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'in:draft,active,completed,cancelled',
            'jemaat_ids' => 'required|array|min:1',
            'jemaat_ids.*' => 'exists:jemaats,id',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $pelayanan->update([
                'kategori_id' => $validated['kategori_id'],
                'nama_pelayanan' => $validated['nama_pelayanan'],
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'],
                'lokasi' => $validated['lokasi'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'],
            ]);

            // Sync detail pelayan (remove old, insert new)
            $pelayanan->details()->delete();

            $details = [];
            foreach ($validated['jemaat_ids'] as $index => $jemaatId) {
                $details[] = [
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id' => $jemaatId,
                    'role' => $validated['roles'][$index] ?? null,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            PelayananDetail::insert($details);

            DB::commit();

            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelayanan berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete jadwal
     */
    public function destroy(Pelayanan $pelayanan)
    {
        $pelayanan->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelayanan berhasil dihapus!');
    }

    /**
     * Set pelayan to OFF
     */
    public function setOff(Request $request, PelayananDetail $detail)
    {
        $validated = $request->validate([
            'alasan_off' => 'required|string|max:500',
        ]);

        $detail->update([
            'status' => 'off',
            'tanggal_off' => now(),
            'alasan_off' => $validated['alasan_off'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Petugas berhasil ditandai sebagai OFF!');
    }

    /**
     * Set pelayan back to ACTIVE
     */
    public function setActive(PelayananDetail $detail)
    {
        $detail->update([
            'status' => 'active',
            'tanggal_off' => null,
            'alasan_off' => null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Petugas berhasil diaktifkan kembali!');
    }

    /**
     * Search jemaat for autocomplete
     */
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
