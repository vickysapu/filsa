<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LatihanPelayanan;
use App\Models\Pelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\UnifiedNotification;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Concerns\ResolvesBranch;

class LatihanPelayananController extends Controller
{
    use ResolvesBranch;

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $branchId = $this->getBranchId();

        $query = LatihanPelayanan::with(['pelayanan', 'creator']);

        // Filter Branch
        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter Lainnya
        if ($request->filled('tanggal_start')) {
            $query->where('tanggal', '>=', $request->tanggal_start);
        }
        if ($request->filled('tanggal_end')) {
            $query->where('tanggal', '<=', $request->tanggal_end);
        }
        if ($request->filled('pelayanan_id')) {
            $query->where('pelayanan_id', $request->pelayanan_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_latihan', 'like', "%{$search}%")->orWhereHas('pelayanan', fn($q) => $q->where('nama_pelayanan', 'like', "%{$search}%"));
            });
        }

        $latihans = $query->latest()->paginate(12);

        // Load Pelayanan untuk filter dropdown (hanya yang aktif dan sesuai branch jika bukan super admin)
        $pelayananQuery = Pelayanan::where('status', 'active');
        if (!$isSuperAdmin && $branchId) {
            $pelayananQuery->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $pelayananQuery->where('branch_id', $request->branch_id);
        }
        $pelayanans = $pelayananQuery->get();

        if ($request->ajax()) {
            return view('features.latihan-pelayanan._list', compact('latihans'));
        }

        return view('features.latihan-pelayanan.index', compact('latihans', 'pelayanans'));
    }

    public function store(Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Branch ID tidak valid.'], 403);
        }

        $validated = $request->validate([
            'pelayanan_id' => 'required|exists:pelayanans,id',
            'nama_latihan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'lokasi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'status' => 'in:draft,active,completed,cancelled',
            'branch_id' => $isSuperAdmin ? 'sometimes|exists:branches,id' : '',
        ]);

        // Cek kepemilikan pelayanan jika bukan super admin
        $pelayanan = Pelayanan::find($validated['pelayanan_id']);
        if ($pelayanan && !$this->ownsBranch($pelayanan)) {
            return response()->json(['success' => false, 'message' => 'Pelayanan tersebut tidak berada di branch Anda.'], 403);
        }

        $targetBranchId = $isSuperAdmin ? $validated['branch_id'] ?? $branchId : $branchId;

        try {
            $latihan = LatihanPelayanan::create([
                'branch_id' => $targetBranchId,
                'pelayanan_id' => $validated['pelayanan_id'],
                'nama_latihan' => $validated['nama_latihan'],
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'],
                'lokasi' => $validated['lokasi'] ?? 'Gedung Utama',
                'keterangan' => $validated['keterangan'],
                'status' => $validated['status'] ?? 'active',
                'created_by' => Auth::id(),
            ]);

            // Kirim Notifikasi Jadwal Latihan Baru
            $this->sendNotificationToAuthorizedUsers(
                $targetBranchId,
                'Jadwal Latihan Baru',
                "Jadwal latihan '{$latihan->nama_latihan}' telah ditambahkan.",
                route('admin.latihan-pelayanan.index'),
                'ri-music-2-line', // Ikon musik/latihan
                'text-success',
                'LATIHAN'
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal latihan pelayanan berhasil dibuat.',
            ]);
        } catch (\Exception $e) {
            Log::error('LatihanPelayanan Store Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem.',
                ],
                500,
            );
        }
    }

    public function edit(LatihanPelayanan $latihan)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($latihan)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $pelayananQuery = Pelayanan::where('status', 'active');
        if (!$isSuperAdmin && $branchId) {
            $pelayananQuery->where('branch_id', $branchId);
        }
        $pelayanans = $pelayananQuery->get();

        $latihanData = $latihan->toArray();
        $latihanData['tanggal'] = $latihan->tanggal ? \Carbon\Carbon::parse($latihan->tanggal)->format('Y-m-d') : '';
        $latihanData['waktu'] = $latihan->waktu ? \Carbon\Carbon::parse($latihan->waktu)->format('H:i') : '';

        return response()->json([
            'success' => true,
            'latihan' => $latihanData,
            'pelayanans' => $pelayanans,
        ]);
    }

    public function update(Request $request, LatihanPelayanan $latihan)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($latihan)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'pelayanan_id' => 'required|exists:pelayanans,id',
            'nama_latihan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'lokasi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'status' => 'in:draft,active,completed,cancelled',
            'branch_id' => $isSuperAdmin ? 'sometimes|exists:branches,id' : '',
        ]);

        // Cek kepemilikan pelayanan baru jika diubah
        $pelayanan = Pelayanan::find($validated['pelayanan_id']);
        if ($pelayanan && !$this->ownsBranch($pelayanan)) {
            return response()->json(['success' => false, 'message' => 'Pelayanan tersebut tidak berada di branch Anda.'], 403);
        }

        $targetBranchId = $isSuperAdmin ? $validated['branch_id'] ?? $latihan->branch_id : $latihan->branch_id;

        try {
            $latihan->update([
                'branch_id' => $targetBranchId,
                'pelayanan_id' => $validated['pelayanan_id'],
                'nama_latihan' => $validated['nama_latihan'],
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'],
                'lokasi' => $validated['lokasi'],
                'keterangan' => $validated['keterangan'],
                'status' => $validated['status'],
            ]);

            // Kirim Notifikasi Update Jadwal Latihan
            $this->sendNotificationToAuthorizedUsers(
                $targetBranchId,
                'Jadwal Latihan Diperbarui',
                "Jadwal latihan '{$latihan->nama_latihan}' telah diperbarui.",
                route('admin.latihan-pelayanan.index'),
                'ri-edit-line',
                'text-warning',
                'LATIHAN'
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal latihan berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            Log::error('LatihanPelayanan Update Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem.',
                ],
                500,
            );
        }
    }

    public function destroy(LatihanPelayanan $latihan)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($latihan)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            $namaLatihan = $latihan->nama_latihan;
            $targetBranchId = $latihan->branch_id;

            $latihan->delete();

            $this->sendNotificationToAuthorizedUsers(
                $targetBranchId,
                'Jadwal Latihan Dihapus',
                "Jadwal latihan '{$namaLatihan}' telah dihapus dari sistem.",
                route('admin.latihan-pelayanan.index'),
                'ri-delete-bin-line',
                'text-danger',
                'LATIHAN'
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal latihan berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            Log::error('LatihanPelayanan Delete Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus data.',
                ],
                500,
            );
        }
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
            return $user->hasPermission('latihan.view') || $user->hasPermission('pelayanan.view'); 
        });

        if ($authorizedUsers->isNotEmpty()) {
            Notification::send(
                $authorizedUsers, 
                new UnifiedNotification($title, $message, $url, $icon, $color, $source)
            );
        }
    }
}
