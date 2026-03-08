<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventKategoriController extends Controller
{
    public function index()
    {
        $kategoris = EventKategori::ordered()->get();
        return view('features.kategori.event.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:event_kategoris,nama_kategori',
            'deskripsi' => 'nullable|string|max:500',
            'warna' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer',
        ]);

        EventKategori::create([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'warna' => $validated['warna'] ?? '#4f46e5',
            'icon' => $validated['icon'] ?? null,
            'is_active' => true,
            'urutan' => $validated['urutan'] ?? 0,
            'show_in_website' => true,
        ]);

        return redirect()->route('admin.event-kategori.index')
            ->with('success', 'Kategori event berhasil ditambahkan!');
    }

    public function update(Request $request, EventKategori $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:event_kategoris,nama_kategori,' . $kategori->id,
            'deskripsi' => 'nullable|string|max:500',
            'warna' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'urutan' => 'nullable|integer',
            'show_in_website' => 'boolean',
        ]);

        $kategori->update($validated);

        return redirect()->route('admin.event-kategori.index')
            ->with('success', 'Kategori event berhasil diupdate!');
    }

    public function destroy(EventKategori $kategori)
    {
        if ($kategori->events()->count() > 0) {
            return redirect()->route('admin.event-kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki event!');
        }

        $kategori->delete();

        return redirect()->route('admin.event-kategori.index')
            ->with('success', 'Kategori event berhasil dihapus!');
    }
}