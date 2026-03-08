<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DokumentasiKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DokumentasiKategoriController extends Controller
{
    public function index()
    {
        $kategoris = DokumentasiKategori::ordered()->get();
        return view('features.kategori.dokumentasi.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:dokumentasi_kategoris,nama_kategori',
            'deskripsi' => 'nullable|string|max:500',
            'warna' => 'nullable|string|max:7',
            'urutan' => 'nullable|integer',
        ]);

        DokumentasiKategori::create([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'warna' => $validated['warna'] ?? '#4f46e5',
            'is_active' => true,
            'urutan' => $validated['urutan'] ?? 0,
            'show_in_website' => true,
        ]);

        return redirect()->route('admin.dokumentasi-kategori.index')
            ->with('success', 'Kategori dokumentasi berhasil ditambahkan!');
    }

    public function update(Request $request, DokumentasiKategori $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:dokumentasi_kategoris,nama_kategori,' . $kategori->id,
            'deskripsi' => 'nullable|string|max:500',
            'warna' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'urutan' => 'nullable|integer',
            'show_in_website' => 'boolean',
        ]);

        $kategori->update($validated);

        return redirect()->route('admin.dokumentasi-kategori.index')
            ->with('success', 'Kategori dokumentasi berhasil diupdate!');
    }

    public function destroy(DokumentasiKategori $kategori)
    {
        if ($kategori->albums()->count() > 0) {
            return redirect()->route('admin.dokumentasi-kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki album!');
        }

        $kategori->delete();

        return redirect()->route('admin.dokumentasi-kategori.index')
            ->with('success', 'Kategori dokumentasi berhasil dihapus!');
    }
}