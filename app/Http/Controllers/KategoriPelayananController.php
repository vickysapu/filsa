<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\KategoriPelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\ResolvesBranch;

class KategoriPelayananController extends Controller
{
    use ResolvesBranch;

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $user = Auth::user();
        $branchId = $user?->branch_id ?? $user?->roles->pluck('branch_id')->filter()->first();

        $query = KategoriPelayanan::query();

        $this->scopeToBranch($query, $request);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%")->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $kategoris = $query->ordered()->get();
        $branches = $isSuperAdmin ? Branch::all() : collect();
        $defaultBranchId = $this->getBranchId();

        return view('features.kategori.pelayanan.index', compact('kategoris', 'branches', 'isSuperAdmin', 'defaultBranchId'));
    }

    public function store(Request $request)
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin();

            if ($isSuperAdmin) {
                $request->validate(['branch_id' => 'required|exists:branches,id']);
            }

            $branchId = $this->resolveBranchId($request);

            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda belum terhubung ke cabang.',
                ], 403);
            }

            $validated = $request->validate([
                'nama_kategori' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('kategori_pelayanans')->where(function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId);
                    }),
                ],
                'deskripsi' => 'nullable|string|max:500',
                'warna' => 'nullable|string|max:7',
                'is_active' => 'boolean',
                'urutan' => 'nullable|integer',
            ]);

            $kategori = KategoriPelayanan::create([
                'branch_id' => $branchId,
                'nama_kategori' => $validated['nama_kategori'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'warna' => $validated['warna'] ?? $this->generateRandomColor(),
                'is_active' => $request->boolean('is_active', true),
                'urutan' => $validated['urutan'] ?? 0,
            ]);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Kategori pelayanan berhasil ditambahkan!',
                    'data' => $kategori,
                ],
                201,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('KategoriPelayanan Store Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                ],
                500,
            );
        }
    }

    public function update(Request $request, KategoriPelayanan $kategori)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($kategori)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            if ($isSuperAdmin) {
                $request->validate(['branch_id' => 'nullable|exists:branches,id']);
            }

            $targetBranchId = $isSuperAdmin
                ? ($request->input('branch_id') ?: $kategori->branch_id)
                : $kategori->branch_id;

            $validated = $request->validate([
                'nama_kategori' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('kategori_pelayanans')
                        ->where(function ($query) use ($targetBranchId) {
                            return $query->where('branch_id', $targetBranchId);
                        })
                        ->ignore($kategori->id),
                ],
                'deskripsi' => 'nullable|string|max:500',
                'warna' => 'nullable|string|max:7',
                'is_active' => 'boolean',
                'urutan' => 'nullable|integer',
            ]);

            $updateData = [
                'nama_kategori' => $validated['nama_kategori'],
                'deskripsi' => $validated['deskripsi'] ?? $kategori->deskripsi,
                'warna' => $validated['warna'] ?? $kategori->warna,
                'is_active' => $request->boolean('is_active', true),
                'urutan' => $validated['urutan'] ?? $kategori->urutan,
            ];

            if ($isSuperAdmin) {
                $updateData['branch_id'] = $targetBranchId;
            }

            $kategori->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Kategori pelayanan berhasil diupdate!',
                'data' => $kategori,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('KategoriPelayanan Update Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem.',
                ],
                500,
            );
        }
    }

    public function destroy(KategoriPelayanan $kategori)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($kategori)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        if ($kategori->pelayanans()->count() > 0) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki data pelayanan!',
                ],
                400,
            );
        }

        try {
            $kategori->delete();
            return response()->json([
                'success' => true,
                'message' => 'Kategori pelayanan berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            Log::error('KategoriPelayanan Delete Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus data.',
                ],
                500,
            );
        }
    }

    public function apiKategoris(Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        $query = KategoriPelayanan::select('id', 'nama_kategori', 'warna', 'branch_id');

        $this->scopeToBranch($query, $request);

        $kategoris = $query->active()->ordered()->get();
        return response()->json($kategoris);
    }

    private function generateRandomColor()
    {
        $colors = ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#06b6d4', '#8b5cf6', '#ef4444', '#14b8a6', '#f97316', '#6366f1'];
        return $colors[array_rand($colors)];
    }
}
