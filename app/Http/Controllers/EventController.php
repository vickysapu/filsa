<?php
// app/Http/Controllers/EventController.php

namespace App\Http\Controllers;

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
        $kategoris = EventKategori::orderBy('nama_kategori')->get();
        $query = Event::with('kategori')->latest();

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

        $events = $query->paginate(9);

        if ($request->ajax()) {
            return view('features.event._grid', compact('events'))->render();
        }

        return view('features.event.index', compact('events', 'kategoris'));
    }

    public function create()
    {
        $kategoris = EventKategori::where('is_active', true)->orderBy('urutan')->orderBy('nama_kategori')->get();
        return view('features.event.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:500',
            'deskripsi_lengkap' => 'nullable|string',
            'kategori_id' => 'required|exists:event_kategoris,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
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

        $gambarFeaturedPath = null;
        if ($request->hasFile('gambar_featured')) {
            $gambarFeaturedPath = $request->file('gambar_featured')->store('events/featured', 'public');
        }

        $gambarGaleriPaths = [];
        if ($request->hasFile('gambar_galeri')) {
            foreach ($request->file('gambar_galeri') as $image) {
                $gambarGaleriPaths[] = $image->store('events/gallery', 'public');
            }
        }

        $event = Event::create([
            'nama_event' => $validated['nama_event'],
            'slug' => $this->generateUniqueSlug($validated['nama_event']),
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
            'kuota_peserta' => '0',
            'status' => $validated['status'] ?? 'draft',
            'is_featured' => $request->has('is_featured'),
            'show_in_website' => $request->has('show_in_website'),
            'created_by' => Auth::id(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Event berhasil dibuat', 'data' => $event]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event, Request $request)
    {
        $event->load(['kategori', 'creator']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'event' => [
                    'id' => $event->id,
                    'nama_event' => $event->nama_event,
                    'kategori' => $event->kategori,
                    'status_label' => $event->status_label,
                    'tanggal_mulai' => $event->tanggal_mulai,
                    'tanggal_selesai' => $event->tanggal_selesai,
                    'lokasi' => $event->lokasi,
                    'alamat_lengkap' => $event->alamat_lengkap,
                    'deskripsi' => $event->deskripsi,
                    'deskripsi_lengkap' => $event->deskripsi_lengkap,
                    'pembicara' => $event->pembicara,
                    'kontak_person' => $event->kontak_person,
                    'email_kontak' => $event->email_kontak,
                    'whatsapp_kontak' => $event->whatsapp_kontak,
                    'harga_tiket' => $event->harga_tiket,
                    'kuota_peserta' => $event->kuota_peserta,
                    'gambar_featured' => $event->gambar_featured,
                    'is_featured' => $event->is_featured,
                    'show_in_website' => $event->show_in_website,
                ],
            ]);
        }

        return view('features.event.index', compact('event'));
    }
    public function edit(Event $event, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $event->id,
                'nama_event' => $event->nama_event,
                'kategori_id' => $event->kategori_id,
                'deskripsi' => $event->deskripsi,
                'deskripsi_lengkap' => $event->deskripsi_lengkap,
                'tanggal_mulai' => $event->tanggal_mulai?->format('Y-m-d\TH:i'),
                'tanggal_selesai' => $event->tanggal_selesai?->format('Y-m-d\TH:i'),
                'lokasi' => $event->lokasi,
                'alamat_lengkap' => $event->alamat_lengkap,
                'pembicara' => $event->pembicara,
                'kontak_person' => $event->kontak_person,
                'whatsapp_kontak' => $event->whatsapp_kontak,
                'email_kontak' => $event->email_kontak,
                'harga_tiket' => $event->harga_tiket,
                'status' => $event->status,
                'is_featured' => $event->is_featured,
                'show_in_website' => $event->show_in_website,
                'gambar_featured' => $event->gambar_featured,
                'gambar_galeri' => $event->gambar_galeri ?? [],
            ]);
        }

        $kategoris = EventKategori::orderBy('nama_kategori')->get();
        return view('features.event.edit', compact('event', 'kategoris'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:500',
            'deskripsi_lengkap' => 'nullable|string',
            'kategori_id' => 'required|exists:event_kategoris,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'required|string|max:255',
            'alamat_lengkap' => 'nullable|string|max:500',
            'gambar_featured' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'gambar_galeri.*' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'pembicara' => 'nullable|string|max:255',
            'kontak_person' => 'nullable|string|max:255',
            'email_kontak' => 'nullable|email|max:255',
            'whatsapp_kontak' => 'nullable|string|max:20',
            'harga_tiket' => 'nullable|numeric|min:0',
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
            'status' => $validated['status'],
            'is_featured' => $request->has('is_featured'),
            'show_in_website' => $request->has('show_in_website'),
            'updated_by' => Auth::id(),
        ];

        if ($request->hasFile('gambar_featured')) {
            if ($event->gambar_featured) {
                Storage::disk('public')->delete($event->gambar_featured);
            }
            $updateData['gambar_featured'] = $request->file('gambar_featured')->store('events/featured', 'public');
        }

        if ($request->hasFile('gambar_galeri')) {
            $gambarGaleriPaths = $event->gambar_galeri ?? [];
            foreach ($request->file('gambar_galeri') as $image) {
                $gambarGaleriPaths[] = $image->store('events/gallery', 'public');
            }
            $updateData['gambar_galeri'] = $gambarGaleriPaths;
        }

        $event->update($updateData);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Event berhasil diupdate!', 'data' => $event]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil diupdate!');
    }

    public function destroy(Event $event)
    {
        if ($event->gambar_featured) {
            Storage::disk('public')->delete($event->gambar_featured);
        }
        if ($event->gambar_galeri) {
            foreach ($event->gambar_galeri as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $event->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Event berhasil dihapus!']);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus!');
    }

    protected function generateUniqueSlug($name, $count = 0)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        if ($count > 0) {
            $slug .= '-' . $count;
        }
        if (Event::where('slug', $slug)->exists()) {
            return $this->generateUniqueSlug($name, $count + 1);
        }
        return $slug;
    }

    public function apiUpcoming()
    {
        $events = Event::where('status', 'published')->where('show_in_website', true)->where('tanggal_mulai', '>=', now())->with('kategori')->orderBy('tanggal_mulai')->limit(10)->get();
        return response()->json($events);
    }

    public function apiFeatured()
    {
        $events = Event::where('status', 'published')->where('show_in_website', true)->where('is_featured', true)->with('kategori')->orderBy('tanggal_mulai')->limit(6)->get();
        return response()->json($events);
    }
}
