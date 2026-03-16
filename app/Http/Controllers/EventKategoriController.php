<?php
namespace App\Http\Controllers;

use App\Models\EventKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventKategoriController extends Controller
{
    public function index()
    {
        $kategoris = EventKategori::orderBy('urutan')->orderBy('nama_kategori')->get();
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

        $kategori = EventKategori::create([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'warna' => $validated['warna'] ?? '#4f46e5',
            'icon' => $validated['icon'] ?? null,
            'is_active' => true,
            'urutan' => $validated['urutan'] ?? 0,
            'show_in_website' => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Kategori event berhasil ditambahkan!', 'data' => $kategori]);
        }

        return redirect()->route('admin.event-kategori.index')->with('success', 'Kategori event berhasil ditambahkan!');
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

        $kategori->update([
            'nama_kategori' => $validated['nama_kategori'],
            'slug' => Str::slug($validated['nama_kategori']),
            'deskripsi' => $validated['deskripsi'] ?? $kategori->deskripsi,
            'warna' => $validated['warna'] ?? $kategori->warna,
            'icon' => $validated['icon'] ?? $kategori->icon,
            'is_active' => $validated['is_active'] ?? true,
            'urutan' => $validated['urutan'] ?? $kategori->urutan,
            'show_in_website' => $validated['show_in_website'] ?? true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Kategori event berhasil diupdate!', 'data' => $kategori]);
        }

        return redirect()->route('admin.event-kategori.index')->with('success', 'Kategori event berhasil diupdate!');
    }

    public function destroy(EventKategori $kategori)
    {
        if ($kategori->events()->count() > 0) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Kategori tidak dapat dihapus karena masih memiliki event!'], 400);
            }
            return redirect()->route('admin.event-kategori.index')->with('error', 'Kategori tidak dapat dihapus karena masih memiliki event!');
        }

        $kategori->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Kategori event berhasil dihapus!']);
        }

        return redirect()->route('admin.event-kategori.index')->with('success', 'Kategori event berhasil dihapus!');
    }
}
