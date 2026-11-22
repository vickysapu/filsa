<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Jemaat;
use App\Models\KategoriPelayanan;
use App\Models\Pelayanan;
use App\Models\PelayananDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\UnifiedNotification;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Concerns\ResolvesBranch;

class PelayananController extends Controller
{
    use ResolvesBranch;

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $branchId = $this->getBranchId();

        $query = Pelayanan::with(['details.jemaat', 'details.kategori'])->latest('tanggal');

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

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

        // Load kategoris sesuai branch
        $kategoriQuery = KategoriPelayanan::active()->ordered();
        if (!$isSuperAdmin && $branchId) {
            $kategoriQuery->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $kategoriQuery->where('branch_id', $request->branch_id);
        }
        $kategoris = $kategoriQuery->get();

        if ($request->ajax() || $request->input('ajax') == 1) {
            return view('features.pelayanan._list', compact('pelayanans', 'kategoris'))->render();
        }

        return view('features.pelayanan.index', compact('pelayanans', 'kategoris'));
    }

    public function create(Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        $jemaatQuery = Jemaat::where('status_jemaat', true)->orderBy('nama_lengkap');
        if (!$isSuperAdmin && $branchId) {
            $jemaatQuery->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $jemaatQuery->where('branch_id', $request->branch_id);
        }
        $jemaats = $jemaatQuery->get(['id', 'nama_lengkap', 'no_hp']);

        $kategoriQuery = KategoriPelayanan::active()->ordered();
        if (!$isSuperAdmin && $branchId) {
            $kategoriQuery->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $kategoriQuery->where('branch_id', $request->branch_id);
        }
        $kategoris = $kategoriQuery->get();

        if ($request->ajax() || $request->input('ajax') == 1) {
            $selected = $request->input('selected', []);

            $petugas_html = view('features.pelayanan._petugas_list', [
                'jemaats' => $jemaats,
                'kategoris' => $kategoris,
                'selected' => $selected,
                'roles' => [],
                'kategori_ids' => [],
            ])->render();

            return response()->json([
                'success' => true,
                'petugas_html' => $petugas_html,
            ]);
        }

        abort(404);
    }

    public function store(Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Branch ID tidak valid.'], 403);
        }

        $validated = $request->validate([
            'nama_pelayanan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required|date_format:H:i',
            'lokasi' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:draft,active,completed,cancelled',

            'jemaat_ids' => 'required|array|min:1',
            'jemaat_ids.*' => 'exists:jemaats,id',

            'kategori_id' => 'required|array',
            'kategori_id.*' => 'required|exists:kategori_pelayanans,id',

            'roles' => 'nullable|array',
            'roles.*' => 'nullable|string|max:100',

            'branch_id' => $isSuperAdmin ? 'sometimes|exists:branches,id' : '',
        ]);

        $targetBranchId = $isSuperAdmin ? $validated['branch_id'] ?? $branchId : $branchId;

        DB::beginTransaction();
        try {
            $pelayanan = Pelayanan::create([
                'branch_id' => $targetBranchId,
                'nama_pelayanan' => $validated['nama_pelayanan'],
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'],
                'lokasi' => $validated['lokasi'],
                'keterangan' => $validated['keterangan'],
                'status' => $validated['status'],
                'created_by' => Auth::id(),
                'nomor_jadwal' => Pelayanan::generateNomorJadwalForDate($validated['tanggal']),
            ]);

            foreach ($validated['jemaat_ids'] as $jemaatId) {
                $kategoriId = $validated['kategori_id'][$jemaatId] ?? null;

                if (!$kategoriId) {
                    throw new \Exception("Kategori untuk jemaat ID {$jemaatId} wajib diisi.");
                }

                PelayananDetail::create([
                    'branch_id' => $targetBranchId,
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id' => $jemaatId,
                    'kategori_id' => $kategoriId,
                    'role' => $validated['roles'][$jemaatId] ?? null,
                    'status' => 'active',
                ]);
            }

            DB::commit();

            // Kirim Notifikasi Jadwal Baru
            $this->sendNotificationToAuthorizedUsers(
                $targetBranchId,
                'Jadwal Pelayanan Baru',
                "Jadwal pelayanan '{$pelayanan->nama_pelayanan}' telah ditambahkan untuk tanggal " . \Carbon\Carbon::parse($pelayanan->tanggal)->translatedFormat('d F Y'),
                route('admin.jadwal.index'),
                'ri-calendar-event-line',
                'text-success',
                'PELAYANAN'
            );

            return response()->json([
                'success' => true,
                'message' => "Jadwal berhasil dibuat dengan {$pelayanan->details()->count()} petugas.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                422,
            );
        }
    }

    public function edit(Pelayanan $pelayanan, Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($pelayanan)) {
            abort(403, 'Akses ditolak');
        }

        if (!$request->ajax()) {
            abort(404);
        }

        $jemaatQuery = Jemaat::where('status_jemaat', true)->orderBy('nama_lengkap');
        if (!$isSuperAdmin && $branchId) {
            $jemaatQuery->where('branch_id', $branchId);
        }
        $jemaats = $jemaatQuery->get();

        $kategoriQuery = KategoriPelayanan::active()->ordered();
        if (!$isSuperAdmin && $branchId) {
            $kategoriQuery->where('branch_id', $branchId);
        }
        $kategoris = $kategoriQuery->get();

        $selectedJemaatIds = $pelayanan->details->pluck('jemaat_id')->toArray();
        $roles = $pelayanan->details->pluck('role', 'jemaat_id')->toArray();
        $kategoriIds = $pelayanan->details->pluck('kategori_id', 'jemaat_id')->toArray();

        $petugas_html = view('features.pelayanan._petugas_list', [
            'jemaats' => $jemaats,
            'kategoris' => $kategoris,
            'selected' => $selectedJemaatIds,
            'roles' => $roles,
            'kategori_ids' => $kategoriIds,
        ])->render();

        return response()->json([
            'success' => true,
            'nama_pelayanan' => $pelayanan->nama_pelayanan,
            'tanggal' => $pelayanan->tanggal?->format('Y-m-d'),
            'waktu' => $pelayanan->waktu?->format('H:i'),
            'lokasi' => $pelayanan->lokasi,
            'keterangan' => $pelayanan->keterangan,
            'status' => $pelayanan->status,
            'kategori_ids' => $kategoriIds,
            'selected_jemaat_ids' => $selectedJemaatIds,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, Pelayanan $pelayanan)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($pelayanan)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        if (in_array($pelayanan->status, ['completed', 'cancelled'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Pelayanan yang sudah selesai/dibatalkan tidak dapat diedit!',
                ],
                403,
            );
        }

        $validated = $request->validate([
            'nama_pelayanan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required|date_format:H:i',
            'lokasi' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,active,completed,cancelled',

            'jemaat_ids' => 'required|array|min:1',
            'jemaat_ids.*' => 'exists:jemaats,id',

            'kategori_id' => 'required|array',
            'kategori_id.*' => 'required|exists:kategori_pelayanans,id',

            'roles' => 'nullable|array',
            'roles.*' => 'nullable|string|max:100',

            'branch_id' => $isSuperAdmin ? 'sometimes|exists:branches,id' : '',
        ]);

        $targetBranchId = $isSuperAdmin ? $validated['branch_id'] ?? $pelayanan->branch_id : $pelayanan->branch_id;

        DB::beginTransaction();
        try {
            $pelayanan->update([
                'nama_pelayanan' => $validated['nama_pelayanan'],
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'],
                'lokasi' => $validated['lokasi'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'],
                'branch_id' => $targetBranchId,
            ]);

            $pelayanan->details()->delete();

            $details = [];
            foreach ($validated['jemaat_ids'] as $jemaatId) {
                $kategoriId = $validated['kategori_id'][$jemaatId] ?? null;

                if (!$kategoriId) {
                    throw new \Exception('Kategori pelayanan wajib dipilih untuk setiap petugas.');
                }

                $details[] = [
                    'id' => Str::uuid(),
                    'branch_id' => $targetBranchId,
                    'pelayanan_id' => $pelayanan->id,
                    'jemaat_id' => $jemaatId,
                    'kategori_id' => $kategoriId,
                    'role' => $validated['roles'][$jemaatId] ?? null,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            PelayananDetail::insert($details);

            DB::commit();

            $this->sendNotificationToAuthorizedUsers(
                $targetBranchId,
                'Jadwal Pelayanan Diperbarui',
                "Jadwal pelayanan '{$pelayanan->nama_pelayanan}' telah diperbarui.",
                route('admin.jadwal.index'),
                'ri-edit-line',
                'text-warning',
                'PELAYANAN'
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelayanan berhasil diperbarui!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                422,
            );
        }
    }

    public function destroy(Pelayanan $pelayanan, Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($pelayanan)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }
            abort(403);
        }

        $namaPelayanan = $pelayanan->nama_pelayanan;
        $targetBranchId = $pelayanan->branch_id;

        $pelayanan->delete();

        $this->sendNotificationToAuthorizedUsers(
            $targetBranchId,
            'Jadwal Pelayanan Dihapus',
            "Jadwal pelayanan '{$namaPelayanan}' telah dihapus dari sistem.",
            route('admin.jadwal.index'),
            'ri-delete-bin-line',
            'text-danger',
            'PELAYANAN'
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelayanan berhasil dihapus!',
            ]);
        }

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelayanan berhasil dihapus!');
    }

    public function show(Pelayanan $pelayanan, Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($pelayanan)) {
            abort(403, 'Akses ditolak');
        }

        $pelayanan->load(['details.jemaat', 'details.kategori', 'creator']);

        if ($request->ajax() || $request->input('ajax') == 1) {
            return response()->json([
                'success' => true,
                'id' => $pelayanan->id,
                'nama_pelayanan' => $pelayanan->nama_pelayanan,
                'nomor_jadwal' => $pelayanan->nomor_jadwal,
                'tanggal' => $pelayanan->tanggal?->format('Y-m-d'),
                'tanggal_label' => $pelayanan->tanggal?->translatedFormat('l, d F Y'),
                'waktu' => $pelayanan->waktu?->format('H:i'),
                'lokasi' => $pelayanan->lokasi,
                'keterangan' => $pelayanan->keterangan,
                'status' => $pelayanan->status,
                'status_label' => $pelayanan->status_label,
                'creator' => $pelayanan->creator?->name,
                'created_at' => $pelayanan->created_at?->format('d M Y, H:i'),
                'updated_at' => $pelayanan->updated_at?->format('d M Y, H:i'),
                'total_petugas' => $pelayanan->details->count(),
                'total_aktif' => $pelayanan->details->where('status', 'active')->count(),
                'total_off' => $pelayanan->details->where('status', 'off')->count(),
                'details' => $pelayanan->details->map(
                    fn($d) => [
                        'id' => $d->id,
                        'jemaat_id' => $d->jemaat_id,
                        'nama' => $d->jemaat?->nama_lengkap,
                        'no_hp' => $d->jemaat?->no_hp,
                        'kategori' => $d->kategori?->nama_kategori,
                        'role' => $d->role,
                        'status' => $d->status,
                        'alasan_off' => $d->alasan_off,
                    ],
                ),
            ]);
        }

        return view('features.pelayanan.show', compact('pelayanan'));
    }

    private function sendNotificationToAuthorizedUsers($branchId, $title, $message, $url, $icon, $color, $source)
    {
        $query = User::where('is_active', true)->with('roles.permissions');

        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereHas('roles', function($r) {
                      $r->where('name', 'administrator') 
                        ->orWhere('name', 'Super Admin'); 
                  });
            });
        }

        $users = $query->get();

        $authorizedUsers = $users->filter(function ($user) {
            return $user->hasPermission('pelayanan.view'); 
        });

        if ($authorizedUsers->isNotEmpty()) {
            Notification::send(
                $authorizedUsers, 
                new UnifiedNotification($title, $message, $url, $icon, $color, $source)
            );
        }
    }
}
