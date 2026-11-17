<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\KategoriPelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class KategoriPelayananController extends Controller
{
    protected function getBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    protected function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && ($user->hasRole('administrator') || ($user->branch && $user->branch->is_headquarter));
    }

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $user = Auth::user();
        $branchId = $user?->branch_id ?? $user?->roles->pluck('branch_id')->filter()->first();

        $query = KategoriPelayanan::query();

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%")->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $kategoris = $query->ordered()->get();
        $branches = $isSuperAdmin ? Branch::all() : collect();
        $defaultBranchId = Auth::user()?->roles->first()?->branch_id;

        return view('features.kategori.pelayanan.index', compact('kategoris', 'branches', 'isSuperAdmin', 'defaultBranchId'));
    }

    public function store(Request $request)
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin();
            $branchId = $isSuperAdmin ? $request->branch_id : $this->getBranchId();

            if (!$branchId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses Branch yang valid.',
                    ],
                    403,
                );
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

        if (!$isSuperAdmin && $branchId && $kategori->branch_id !== $branchId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            $targetBranchId = $isSuperAdmin ? $request->branch_id : $kategori->branch_id;

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

            if ($isSuperAdmin && isset($validated['branch_id'])) {
                $updateData['branch_id'] = $validated['branch_id'];
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

        if (!$isSuperAdmin && $branchId && $kategori->branch_id !== $branchId) {
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

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id') && $isSuperAdmin) {
            $query->where('branch_id', $request->branch_id);
        }

        $kategoris = $query->active()->ordered()->get();
        return response()->json($kategoris);
    }

    private function generateRandomColor()
    {
        $colors = ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#06b6d4', '#8b5cf6', '#ef4444', '#14b8a6', '#f97316', '#6366f1'];
        return $colors[array_rand($colors)];
    }
}
