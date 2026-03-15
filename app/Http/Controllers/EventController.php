<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Fetch semua kategori (untuk dropdown filter & form modal)
        $kategoris = EventKategori::all(); // atau Kategori::where('type', 'event')->get(); dll

        // Query events dengan filter
        $query = Event::with('kategori') // eager load relasi kategori
            ->latest();

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_event', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(9); // atau berapa pun jumlah per halaman

        // Handle AJAX request untuk reload grid saja
        if ($request->ajax() || $request->input('ajax')) {
            return view('features.event._grid', compact('events'));
        }

        // Untuk request normal (full page load)
        return view('features.event.index', compact('events', 'kategoris'));
    }

    public function create()
    {
        $kategoris = EventKategori::active()->ordered()->get();
        return view('features.event.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:500',
            'deskripsi_lengkap' => 'nullable|string',
            'kategori_id' => 'required|exists:event_kategoris,id',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'nullable|after_or_equal:tanggal_mulai',
            'lokasi' => 'required|string|max:255',
            'alamat_lengkap' => 'nullable|string|max:500',
            'gambar_featured' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'gambar_galeri.*' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'pembicara' => 'nullable|string|max:255',
            'kontak_person' => 'nullable|string|max:255',
            'email_kontak' => 'nullable|email|max:255',
            'whatsapp_kontak' => 'nullable|string|max:20',
            'harga_tiket' => 'nullable|numeric|min:0',
            'kuota_peserta' => 'nullable|integer|min:1',
            'status' => 'in:draft,published',
            'is_featured' => 'boolean',
            'show_in_website' => 'boolean',
        ]);

        // Upload featured image
        $gambarFeaturedPath = null;
        if ($request->hasFile('gambar_featured')) {
            $gambarFeaturedPath = $request->file('gambar_featured')->store('events/featured', 'public');
        }

        // Upload gallery
        $gambarGaleriPaths = [];
        if ($request->hasFile('gambar_galeri')) {
            foreach ($request->file('gambar_galeri') as $image) {
                $gambarGaleriPaths[] = $image->store('events/gallery', 'public');
            }
        }

        Event::create([
            'nama_event' => $validated['nama_event'],
            'slug' => Event::generateUniqueSlug($validated['nama_event']),
            'deskripsi' => $validated['deskripsi'],
            'deskripsi_lengkap' => $validated['deskripsi_lengkap'] ?? null,
            'kategori_id' => $validated['kategori_id'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'lokasi' => $validated['lokasi'],
            'alamat_lengkap' => $validated['alamat_lengkap'] ?? null,
            'gambar_featured' => $gambarFeaturedPath,
            'gambar_galeri' => !empty($gambarGaleriPaths) ? $gambarGaleriPaths : null,
            'pembicara' => $validated['pembicara'] ?? null,
            'kontak_person' => $validated['kontak_person'] ?? null,
            'email_kontak' => $validated['email_kontak'] ?? null,
            'whatsapp_kontak' => $validated['whatsapp_kontak'] ?? null,
            'harga_tiket' => $validated['harga_tiket'] ?? 0,
            'kuota_peserta' => $validated['kuota_peserta'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'is_featured' => $request->has('is_featured'),
            'show_in_website' => $request->has('show_in_website'),
            'created_by' => Auth::id(),
        ]);

        // Jika AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event berhasil dibuat'
            ]);
        }

        // Jika normal form
        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event)
    {
        $event->load(['kategori', 'creator', 'dokumentasiAlbums']);
        return view('features.event.show', compact('event'));
    }

    public function edit(Event $event, Request $request)
    {
        $kategoris = EventKategori::all(); // atau model kategori kamu

        if ($request->ajax() || $request->query('ajax')) {
            return response()->json([
                'success' => true,
                'nama_event' => $event->nama_event,
                'kategori_id' => $event->kategori_id,
                'deskripsi' => $event->deskripsi,
                'deskripsi_lengkap' => $event->deskripsi_lengkap,
                'tanggal_mulai' => $event->tanggal_mulai?->format('Y-m-d H:i:s'),
                'tanggal_selesai' => $event->tanggal_selesai?->format('Y-m-d H:i:s'),
                'lokasi' => $event->lokasi,
                'alamat_lengkap' => $event->alamat_lengkap,
                'pembicara' => $event->pembicara,
                'kontak_person' => $event->kontak_person,
                'whatsapp_kontak' => $event->whatsapp_kontak,
                'email_kontak' => $event->email_kontak,
                'harga_tiket' => $event->harga_tiket,
                'kuota_peserta' => $event->kuota_peserta,
                'status' => $event->status,
                'is_featured' => $event->is_featured,
                'show_in_website' => $event->show_in_website,
                'gambar_featured' => $event->gambar_featured,
                'gambar_galeri' => $event->gambar_galeri ?? [], // array path gambar galeri
            ]);
        }

        // Untuk mode normal (halaman edit terpisah, jika masih dipakai)
        return view('features.event.edit', compact('event', 'kategoris'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:500',
            'deskripsi_lengkap' => 'nullable|string',
            'kategori_id' => 'required|exists:event_kategoris,id',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'nullable|after_or_equal:tanggal_mulai',
            'lokasi' => 'required|string|max:255',
            'alamat_lengkap' => 'nullable|string|max:500',
            'gambar_featured' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'gambar_galeri.*' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'pembicara' => 'nullable|string|max:255',
            'kontak_person' => 'nullable|string|max:255',
            'email_kontak' => 'nullable|email|max:255',
            'whatsapp_kontak' => 'nullable|string|max:20',
            'harga_tiket' => 'nullable|numeric|min:0',
            'kuota_peserta' => 'nullable|integer|min:1',
            'status' => 'in:draft,published,ongoing,completed,cancelled',
            'is_featured' => 'boolean',
            'show_in_website' => 'boolean',
        ]);

        $updateData = [
            'nama_event' => $validated['nama_event'],
            'deskripsi' => $validated['deskripsi'],
            'deskripsi_lengkap' => $validated['deskripsi_lengkap'] ?? $event->deskripsi_lengkap,
            'kategori_id' => $validated['kategori_id'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? $event->tanggal_selesai,
            'lokasi' => $validated['lokasi'],
            'alamat_lengkap' => $validated['alamat_lengkap'] ?? $event->alamat_lengkap,
            'pembicara' => $validated['pembicara'] ?? $event->pembicara,
            'kontak_person' => $validated['kontak_person'] ?? $event->kontak_person,
            'email_kontak' => $validated['email_kontak'] ?? $event->email_kontak,
            'whatsapp_kontak' => $validated['whatsapp_kontak'] ?? $event->whatsapp_kontak,
            'harga_tiket' => $validated['harga_tiket'] ?? $event->harga_tiket,
            'kuota_peserta' => $validated['kuota_peserta'] ?? $event->kuota_peserta,
            'status' => $validated['status'],
            'is_featured' => $request->has('is_featured'),
            'show_in_website' => $request->has('show_in_website'),
            'updated_by' => Auth::id(),
        ];

        // Upload new featured image
        if ($request->hasFile('gambar_featured')) {
            if ($event->gambar_featured) {
                Storage::disk('public')->delete($event->gambar_featured);
            }
            $updateData['gambar_featured'] = $request->file('gambar_featured')->store('events/featured', 'public');
        }

        // Upload new gallery images
        if ($request->hasFile('gambar_galeri')) {
            $gambarGaleriPaths = $event->gambar_galeri ?? [];
            foreach ($request->file('gambar_galeri') as $image) {
                $gambarGaleriPaths[] = $image->store('events/gallery', 'public');
            }
            $updateData['gambar_galeri'] = $gambarGaleriPaths;
        }

        $event->update($updateData);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diupdate!');
    }

    public function destroy(Event $event)
    {
        // Delete featured image
        if ($event->gambar_featured) {
            Storage::disk('public')->delete($event->gambar_featured);
        }

        // Delete gallery images
        if ($event->gambar_galeri) {
            foreach ($event->gambar_galeri as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    /**
     * API for public website
     */
    public function apiUpcoming()
    {
        $events = Event::published()
            ->upcoming()
            ->with('kategori')
            ->orderBy('tanggal_mulai')
            ->limit(10)
            ->get();

        return response()->json($events);
    }

    public function apiFeatured()
    {
        $events = Event::published()
            ->featured()
            ->with('kategori')
            ->orderBy('tanggal_mulai')
            ->limit(6)
            ->get();

        return response()->json($events);
    }
}