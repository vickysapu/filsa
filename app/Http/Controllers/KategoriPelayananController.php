<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriPelayananController extends Controller
{
    public function index()
    {
        $kategoris = KategoriPelayanan::ordered()->get();
        return view('features.kategori.pelayanan.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_pelayanans,nama_kategori',
            'deskripsi' => 'nullable|string|max:500',
            'urutan' => 'nullable|integer',
        ]);

        // Generate warna random jika tidak diisi
        $warna = $request->warna ?? $this->generateRandomColor();

        KategoriPelayanan::create([
            'nama_kategori' => $validated['nama_kategori'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'warna' => $warna,
            'is_active' => true,
            'urutan' => $validated['urutan'] ?? 0,
        ]);

        return redirect()->route('admin.kategori.pelayanan.index')->with('success', 'Kategori pelayanan berhasil ditambahkan!');
    }

    public function update(Request $request, KategoriPelayanan $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori_pelayanans,nama_kategori,' . $kategori->id,
            'deskripsi' => 'nullable|string|max:500',
            'warna' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'urutan' => 'nullable|integer',
        ]);

        $kategori->update([
            'nama_kategori' => $validated['nama_kategori'],
            'deskripsi' => $validated['deskripsi'] ?? $kategori->deskripsi,
            'warna' => $validated['warna'] ?? $kategori->warna,
            'is_active' => $validated['is_active'] ?? true,
            'urutan' => $validated['urutan'] ?? $kategori->urutan,
        ]);

        return redirect()->route('admin.kategori.pelayanan.index')->with('success', 'Kategori pelayanan berhasil diupdate!');
    }

    public function destroy(KategoriPelayanan $kategori)
    {
        if ($kategori->pelayanans()->count() > 0) {
            return redirect()->route('admin.kategori.pelayanan.index')->with('error', 'Kategori tidak dapat dihapus karena masih memiliki data pelayanan!');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.pelayanan.index')->with('success', 'Kategori pelayanan berhasil dihapus!');
    }

    /**
     * API: Get kategori for autocomplete
     */
    public function apiKategoris()
    {
        $kategoris = KategoriPelayanan::active()
            ->ordered()
            ->get(['id', 'nama_kategori', 'warna']);
        return response()->json($kategoris);
    }

    /**
     * Generate random color hex
     */
    private function generateRandomColor()
    {
        $colors = [
            '#4f46e5', // Indigo
            '#10b981', // Emerald
            '#f59e0b', // Amber
            '#ec4899', // Pink
            '#06b6d4', // Cyan
            '#8b5cf6', // Violet
            '#ef4444', // Red
            '#14b8a6', // Teal
            '#f97316', // Orange
            '#6366f1', // Indigo Light
        ];
        return $colors[array_rand($colors)];
    }
}
