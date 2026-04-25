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
    public function index(Request $request)
    {
        $query = Pelayanan::with(['details.jemaat'])
            ->latest('tanggal');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal_start')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_start);
        }
        if ($request->filled('tanggal_end')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_end);
        }
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

        if ($request->ajax() || $request->input('ajax') == 1) {
            return view('features.pelayanan._list', compact('pelayanans', 'kategoris'))->render();
        }

        return view('features.pelayanan.index', compact('pelayanans', 'kategoris'));
    }

    public function create(Request $request)
    {
        $jemaats = Jemaat::where('status_jemaat', true)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'no_hp']);

        $kategoris = KategoriPelayanan::active()->ordered()->get();

        if ($request->ajax() || $request->input('ajax') == 1) {
            $selected = $request->input('selected', []);

            $petugas_html = view('features.pelayanan._petugas_list', [
                'jemaats'      => $jemaats,
                'kategoris'    => $kategoris,
                'selected'     => $selected,
                'roles'        => [],
                'kategori_ids' => [],
            ])->render();

            return response()->json([
                'success'      => true,
                'petugas_html' => $petugas_html,
            ]);
        }

        abort(404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelayanan' => 'required|string|max:255',
            'tanggal'        => 'required|date',
            'waktu'          => 'required|date_format:H:i',
            'lokasi'         => 'required|string|max:255',
            'keterangan'     => 'nullable|string',
            'status'         => 'required|in:draft,active,completed,cancelled',

            'jemaat_ids'     => 'required|array|min:1',
            'jemaat_ids.*'   => 'exists:jemaats,id',

            'kategori_id'    => 'required|array',
            'kategori_id.*'  => 'required|exists:kategori_pelayanans,id',

            'roles'          => 'nullable|array',
            'roles.*'        => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $pelayanan = Pelayanan::create([
                'branch_id'      => Auth::user()->branch_id,
                'nama_pelayanan' => $validated['nama_pelayanan'],
                'tanggal'        => $validated['tanggal'],
                'waktu'          => $validated['waktu'],
                'lokasi'         => $validated['lokasi'],
                'keterangan'     => $validated['keterangan'],
                'status'         => $validated['status'],
                'created_by'     => Auth::id(),
                'nomor_jadwal'   => Pelayanan::generateNomorJadwalForDate($validated['tanggal']),
            ]);

            foreach ($validated['jemaat_ids'] as $jemaatId) {
                $kategoriId = $validated['kategori_id'][$jemaatId] ?? null;

                if (!$kategoriId) {
                    throw new \Exception("Kategori untuk jemaat ID {$jemaatId} wajib diisi.");
                }

                PelayananDetail::create([
                    'branch_id'    => Auth::user()->branch_id,
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id'    => $jemaatId,
                    'kategori_id'  => $kategoriId,
                    'role'         => $validated['roles'][$jemaatId] ?? null,
                    'status'       => 'active',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Jadwal berhasil dibuat dengan {$pelayanan->details()->count()} petugas."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function edit(Pelayanan $pelayanan, Request $request)
    {
        if (!$request->ajax()) abort(404);

        $jemaats = Jemaat::where('status_jemaat', true)
            ->orderBy('nama_lengkap')
            ->get();

        $kategoris = KategoriPelayanan::active()->ordered()->get();

        $selectedJemaatIds = $pelayanan->details->pluck('jemaat_id')->toArray();
        $roles         = $pelayanan->details->pluck('role', 'jemaat_id')->toArray();
        $kategoriIds   = $pelayanan->details->pluck('kategori_id', 'jemaat_id')->toArray();

        $petugas_html = view('features.pelayanan._petugas_list', [
            'jemaats'      => $jemaats,
            'kategoris'    => $kategoris,
            'selected'     => $selectedJemaatIds,
            'roles'        => $roles,
            'kategori_ids' => $kategoriIds,
        ])->render();

        return response()->json([
            'success'             => true,
            'nama_pelayanan'      => $pelayanan->nama_pelayanan,
            'tanggal'             => $pelayanan->tanggal?->format('Y-m-d'),
            'waktu'               => $pelayanan->waktu?->format('H:i'),
            'lokasi'              => $pelayanan->lokasi,
            'keterangan'          => $pelayanan->keterangan,
            'status'              => $pelayanan->status,
            'kategori_ids'        => $kategoriIds,
            'selected_jemaat_ids' => $selectedJemaatIds,
            'roles'               => $roles,
        ]);
    }

    public function update(Request $request, Pelayanan $pelayanan)
    {
        if (in_array($pelayanan->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pelayanan yang sudah selesai/dibatalkan tidak dapat diedit!'
            ], 403);
        }

        $validated = $request->validate([
            'nama_pelayanan' => 'required|string|max:255',
            'tanggal'        => 'required|date',
            'waktu'          => 'required|date_format:H:i',
            'lokasi'         => 'required|string|max:255',
            'keterangan'     => 'nullable|string|max:1000',
            'status'         => 'required|in:draft,active,completed,cancelled',

            'jemaat_ids'     => 'required|array|min:1',
            'jemaat_ids.*'   => 'exists:jemaats,id',

            // VALIDASI YANG BENAR
            'kategori_id'    => 'required|array',
            'kategori_id.*'  => 'required|exists:kategori_pelayanans,id',

            'roles'          => 'nullable|array',
            'roles.*'        => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $pelayanan->update([
                'nama_pelayanan' => $validated['nama_pelayanan'],
                'tanggal'        => $validated['tanggal'],
                'waktu'          => $validated['waktu'],
                'lokasi'         => $validated['lokasi'],
                'keterangan'     => $validated['keterangan'] ?? null,
                'status'         => $validated['status'],
            ]);

            // Hapus detail lama
            $pelayanan->details()->delete();

            $details = [];
            foreach ($validated['jemaat_ids'] as $jemaatId) {
                $kategoriId = $validated['kategori_id'][$jemaatId] ?? null;

                if (!$kategoriId) {
                    throw new \Exception("Kategori pelayanan wajib dipilih untuk setiap petugas.");
                }

                $details[] = [
                    'id'           => Str::uuid(),
                    'branch_id'    => Auth::user()->branch_id,
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id'    => $jemaatId,
                    'kategori_id'  => $kategoriId,
                    'role'         => $validated['roles'][$jemaatId] ?? null,
                    'status'       => 'active',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            PelayananDetail::insert($details);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelayanan berhasil diperbarui!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

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

    public function show(Pelayanan $pelayanan, Request $request)
    {
        // Load semua relasi yang dibutuhkan oleh view
        $pelayanan->load([
            'details.jemaat',
            'details.kategori',
            'creator',
        ]);
    
        if ($request->ajax() || $request->input('ajax') == 1) {
            return response()->json([
                'success'        => true,
                'id'             => $pelayanan->id,
                'nama_pelayanan' => $pelayanan->nama_pelayanan,
                'nomor_jadwal'   => $pelayanan->nomor_jadwal,
                'tanggal'        => $pelayanan->tanggal?->format('Y-m-d'),
                'tanggal_label'  => $pelayanan->tanggal?->translatedFormat('l, d F Y'),
                'waktu'          => $pelayanan->waktu?->format('H:i'),
                'lokasi'         => $pelayanan->lokasi,
                'keterangan'     => $pelayanan->keterangan,
                'status'         => $pelayanan->status,
                'status_label'   => $pelayanan->status_label,
                'creator'        => $pelayanan->creator?->name,
                'created_at'     => $pelayanan->created_at?->format('d M Y, H:i'),
                'updated_at'     => $pelayanan->updated_at?->format('d M Y, H:i'),
                'total_petugas'  => $pelayanan->details->count(),
                'total_aktif'    => $pelayanan->details->where('status', 'active')->count(),
                'total_off'      => $pelayanan->details->where('status', 'off')->count(),
                'details'        => $pelayanan->details->map(fn($d) => [
                    'id'         => $d->id,
                    'jemaat_id'  => $d->jemaat_id,
                    'nama'       => $d->jemaat?->nama_lengkap,
                    'no_hp'      => $d->jemaat?->no_hp,
                    'kategori'   => $d->kategori?->nama_kategori,
                    'role'       => $d->role,
                    'status'     => $d->status,
                    'alasan_off' => $d->alasan_off,
                ]),
            ]);
        }
    
        return view('features.pelayanan.show', compact('pelayanan'));
    }
}