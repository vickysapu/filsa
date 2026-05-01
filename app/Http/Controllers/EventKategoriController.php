<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\EventKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventKategoriController extends Controller
{
    protected function getBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id ?? Auth::user()?->branch_id;
    }

    protected function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && ($user->hasRole('administrator') || ($user->branch && $user->branch->is_headquarter));
    }

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $branchId = $this->getBranchId();

        $query = EventKategori::query();

        // Bypass global scope jika ada untuk kontrol manual
        if (class_exists(\App\Models\Scopes\BranchScope::class)) {
            $query->withoutGlobalScope(\App\Models\Scopes\BranchScope::class);
        }

        if (!$isSuperAdmin) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            } else {
                $query->whereRaw('1=0');
            }
        } else {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%")->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $kategoris = $query->orderBy('urutan')->orderBy('nama_kategori')->get();
        $branches = $isSuperAdmin ? Branch::all() : collect();
        $defaultBranchId = $this->getBranchId();

        return view('features.kategori.event.index', compact('kategoris', 'branches', 'isSuperAdmin', 'defaultBranchId'));
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

            // Validasi nama_kategori unik HANYA di dalam branch_id yang sama
            $validated = $request->validate([
                'nama_kategori' => [
                    'required',
                ],
                'deskripsi' => 'nullable|string|max:500',
                'warna' => 'nullable|string|max:7',
                'icon' => 'nullable|string|max:50',
                'urutan' => 'nullable|integer',
                'is_active' => 'boolean',
                'show_in_website' => 'boolean',
            ]);

            if ($isSuperAdmin) {
                $request->validate([
                    'branch_id' => 'required|exists:branches,id',
                ]);
                $branchId = $request->branch_id;
            }

            // STRATEGI BARU: Slug = {slug-nama}-{branch_id}
            // Ini menjamin unik global karena branch_id berbeda, meskipun nama sama.
            $baseSlug = Str::slug($validated['nama_kategori']);
            $slug = $baseSlug . '-' . $branchId;

            $kategori = EventKategori::create([
                'branch_id' => $branchId,
                'nama_kategori' => $validated['nama_kategori'],
                'slug' => $slug,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'warna' => $validated['warna'] ?? '#4f46e5',
                'icon' => $validated['icon'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'urutan' => $validated['urutan'] ?? 0,
                'show_in_website' => $request->boolean('show_in_website', true),
            ]);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Kategori event berhasil ditambahkan!',
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
            Log::error('EventKategori Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function update(Request $request, EventKategori $kategori)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $kategori->branch_id !== $branchId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            $targetBranchId = $isSuperAdmin ? $request->branch_id ?? $kategori->branch_id : $kategori->branch_id;

            // Validasi nama_kategori unik HANYA di dalam branch_id target
            $validated = $request->validate([
                'nama_kategori' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('event_kategoris')
                        ->where(function ($query) use ($targetBranchId) {
                            return $query->where('branch_id', $targetBranchId);
                        })
                        ->ignore($kategori->id),
                ],
                'deskripsi' => 'nullable|string|max:500',
                'warna' => 'nullable|string|max:7',
                'icon' => 'nullable|string|max:50',
                'is_active' => 'boolean',
                'urutan' => 'nullable|integer',
                'show_in_website' => 'boolean',
            ]);

            if ($isSuperAdmin) {
                $request->validate([
                    'branch_id' => 'required|exists:branches,id',
                ]);
                $targetBranchId = $request->branch_id;
            }

            // Update Slug dengan strategi baru: {slug-nama}-{branch_id}
            $baseSlug = Str::slug($validated['nama_kategori']);
            $slug = $baseSlug . '-' . $targetBranchId;

            $kategori->update([
                'branch_id' => $targetBranchId,
                'nama_kategori' => $validated['nama_kategori'],
                'slug' => $slug,
                'deskripsi' => $validated['deskripsi'] ?? $kategori->deskripsi,
                'warna' => $validated['warna'] ?? $kategori->warna,
                'icon' => $validated['icon'] ?? $kategori->icon,
                'is_active' => $request->boolean('is_active', true),
                'urutan' => $validated['urutan'] ?? $kategori->urutan,
                'show_in_website' => $request->boolean('show_in_website', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori event berhasil diupdate!',
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
            Log::error('EventKategori Update Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem.',
                ],
                500,
            );
        }
    }

    public function destroy(EventKategori $kategori)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $kategori->branch_id !== $branchId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        if (method_exists($kategori, 'events') && $kategori->events()->count() > 0) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki event!',
                ],
                400,
            );
        }

        try {
            $kategori->delete();
            return response()->json([
                'success' => true,
                'message' => 'Kategori event berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            Log::error('EventKategori Delete Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus data.',
                ],
                500,
            );
        }
    }
}
