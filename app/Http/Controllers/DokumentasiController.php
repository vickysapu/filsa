<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DokumentasiAlbum;
use App\Models\DokumentasiPhoto;
use App\Models\DokumentasiKategori;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DokumentasiController extends Controller
{
    public function index(Request $request)
    {
        $query = DokumentasiAlbum::with(['kategori', 'event', 'creator'])->latest();

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul_album', 'LIKE', "%{$request->search}%")->orWhere('fotografer', 'LIKE', "%{$request->search}%");
            });
        }

        $albums = $query->paginate(12);

        $kategoris = DokumentasiKategori::active()->ordered()->get();
        $events = Event::published()->orderBy('tanggal_mulai', 'desc')->get();

        if ($request->ajax() || $request->query('ajax')) {
            return view('features.dokumentasi._grid', compact('albums'))->render();
        }

        return view('features.dokumentasi.index', compact('albums', 'kategoris', 'events'));
    }

    public function create()
    {
        $kategoris = DokumentasiKategori::active()->ordered()->get();
        $events = Event::published()->orderBy('tanggal_mulai', 'desc')->get();
        return view('features.dokumentasi.create', compact('kategoris', 'events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_album' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori_id' => 'nullable|exists:dokumentasi_kategoris,id',
            'event_id' => 'nullable|exists:events,id',
            'fotografer' => 'nullable|string|max:255',
            'tanggal_album' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'in:draft,published,archived',
            'is_featured' => 'boolean',
            'allow_download' => 'boolean',
            'show_in_website' => 'boolean',
            'photos.*' => 'required|image|mimes:jpg,png,jpeg|max:10240', // Max 10MB per photo
            'captions.*' => 'nullable|string|max:500',
        ]);

        // Create album
        $album = DokumentasiAlbum::create([
            'judul_album' => $validated['judul_album'],
            'slug' => DokumentasiAlbum::generateUniqueSlug($validated['judul_album']),
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kategori_id' => $validated['kategori_id'] ?? null,
            'event_id' => $validated['event_id'] ?? null,
            'fotografer' => $validated['fotografer'] ?? null,
            'tanggal_album' => $validated['tanggal_album'],
            'lokasi' => $validated['lokasi'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'is_featured' => $request->has('is_featured'),
            'allow_download' => $request->has('allow_download'),
            'show_in_website' => $request->has('show_in_website'),
            'created_by' => Auth::id(),
        ]);

        // Upload photos
        if ($request->hasFile('photos')) {
            $urutan = 1;
            foreach ($request->file('photos') as $index => $photo) {
                $filePath = $photo->store('dokumentasi/' . $album->slug, 'public');

                // Get image metadata
                $imagePath = storage_path('app/public/' . $filePath);
                $metadata = [];
                if (file_exists($imagePath)) {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($imagePath);
                    $metadata = [
                        'width' => $image->width(),
                        'height' => $image->height(),
                        'mime_type' => $photo->getMimeType(),
                    ];
                }

                DokumentasiPhoto::create([
                    'album_id' => $album->id,
                    'file_path' => $filePath,
                    'file_name' => $photo->hashName(),
                    'file_type' => 'image',
                    'mime_type' => $photo->getMimeType(),
                    'file_size' => $photo->getSize(),
                    'caption' => $validated['captions'][$index] ?? null,
                    'urutan' => $urutan++,
                    'is_cover' => $urutan == 2, // Second photo as cover
                    'metadata' => $metadata,
                    'uploaded_by' => Auth::id(),
                ]);
            }

            // Update album photo count and cover
            $album->updateFotoCount();
        }

        return redirect()
            ->route('admin.dokumentasi.index')
            ->with('success', "Album berhasil dibuat dengan {$album->jumlah_foto} foto!");
    }

    public function show(DokumentasiAlbum $album)
    {
        $album->load(['kategori', 'event', 'creator', 'photos']);
        return view('features.dokumentasi.show', compact('album'));
    }

    public function edit(DokumentasiAlbum $album, Request $request)
    {
        if ($request->ajax() || $request->query('ajax')) {
            return response()->json([
                'success' => true,
                'judul_album' => $album->judul_album,
                'deskripsi' => $album->deskripsi,
                'kategori_id' => $album->kategori_id,
                'event_id' => $album->event_id,
                'fotografer' => $album->fotografer,
                'tanggal_album' => $album->tanggal_album?->format('Y-m-d'),
                'lokasi' => $album->lokasi,
                'status' => $album->status,
                'is_featured' => $album->is_featured,
                'allow_download' => $album->allow_download,
                'show_in_website' => $album->show_in_website,
                'photos' => $album->photos->pluck('file_path')->toArray(), // array path foto
            ]);
        }

        // mode normal jika masih ada
        $kategoris = DokumentasiKategori::active()->ordered()->get();
        $events = Event::published()->orderBy('tanggal_mulai', 'desc')->get();
        return view('features.dokumentasi.edit', compact('album', 'kategoris', 'events'));
    }

    public function update(Request $request, DokumentasiAlbum $album)
    {
        $validated = $request->validate([
            'judul_album' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori_id' => 'nullable|exists:dokumentasi_kategoris,id',
            'event_id' => 'nullable|exists:events,id',
            'fotografer' => 'nullable|string|max:255',
            'tanggal_album' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'in:draft,published,archived',
            'is_featured' => 'boolean',
            'allow_download' => 'boolean',
            'show_in_website' => 'boolean',
            'photos.*' => 'nullable|image|mimes:jpg,png,jpeg|max:10240',
            'captions.*' => 'nullable|string|max:500',
            'delete_photos' => 'nullable|array',
            'delete_photos.*' => 'exists:dokumentasi_photos,id',
        ]);

        $updateData = [
            'judul_album' => $validated['judul_album'],
            'deskripsi' => $validated['deskripsi'] ?? $album->deskripsi,
            'kategori_id' => $validated['kategori_id'] ?? $album->kategori_id,
            'event_id' => $validated['event_id'] ?? $album->event_id,
            'fotografer' => $validated['fotografer'] ?? $album->fotografer,
            'tanggal_album' => $validated['tanggal_album'],
            'lokasi' => $validated['lokasi'] ?? $album->lokasi,
            'status' => $validated['status'],
            'is_featured' => $request->has('is_featured'),
            'allow_download' => $request->has('allow_download'),
            'show_in_website' => $request->has('show_in_website'),
        ];

        $album->update($updateData);

        // Delete photos
        if (!empty($validated['delete_photos'])) {
            foreach ($validated['delete_photos'] as $photoId) {
                $photo = DokumentasiPhoto::find($photoId);
                if ($photo) {
                    Storage::disk('public')->delete($photo->file_path);
                    $photo->delete();
                }
            }
            $album->updateFotoCount();
        }

        // Upload new photos
        if ($request->hasFile('photos')) {
            $maxUrutan = $album->photos()->max('urutan') ?? 0;
            $urutan = $maxUrutan + 1;

            foreach ($request->file('photos') as $index => $photo) {
                $filePath = $photo->store('dokumentasi/' . $album->slug, 'public');

                $imagePath = storage_path('app/public/' . $filePath);
                $metadata = [];
                if (file_exists($imagePath)) {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($imagePath);
                    $metadata = [
                        'width' => $image->width(),
                        'height' => $image->height(),
                        'mime_type' => $photo->getMimeType(),
                    ];
                }

                DokumentasiPhoto::create([
                    'album_id' => $album->id,
                    'file_path' => $filePath,
                    'file_name' => $photo->hashName(),
                    'file_type' => 'image',
                    'mime_type' => $photo->getMimeType(),
                    'file_size' => $photo->getSize(),
                    'caption' => $validated['captions'][$index] ?? null,
                    'urutan' => $urutan++,
                    'is_cover' => false,
                    'metadata' => $metadata,
                    'uploaded_by' => Auth::id(),
                ]);
            }

            $album->updateFotoCount();
        }

        return redirect()->route('admin.dokumentasi.index')->with('success', 'Album berhasil diupdate!');
    }

    public function destroy(DokumentasiAlbum $album)
    {
        // Delete all photos
        foreach ($album->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $album->delete();

        return redirect()->route('admin.dokumentasi.index')->with('success', 'Album berhasil dihapus!');
    }

    /**
     * Upload photos to existing album
     */
    public function uploadPhotos(Request $request, DokumentasiAlbum $album)
    {
        $validated = $request->validate([
            'photos.*' => 'required|image|mimes:jpg,png,jpeg|max:10240',
            'captions.*' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('photos')) {
            $maxUrutan = $album->photos()->max('urutan') ?? 0;
            $urutan = $maxUrutan + 1;

            foreach ($request->file('photos') as $index => $photo) {
                $filePath = $photo->store('dokumentasi/' . $album->slug, 'public');

                $imagePath = storage_path('app/public/' . $filePath);
                $metadata = [];
                if (file_exists($imagePath)) {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($imagePath);
                    $metadata = [
                        'width' => $image->width(),
                        'height' => $image->height(),
                    ];
                }

                DokumentasiPhoto::create([
                    'album_id' => $album->id,
                    'file_path' => $filePath,
                    'file_name' => $photo->hashName(),
                    'file_type' => 'image',
                    'mime_type' => $photo->getMimeType(),
                    'file_size' => $photo->getSize(),
                    'caption' => $validated['captions'][$index] ?? null,
                    'urutan' => $urutan++,
                    'is_cover' => false,
                    'metadata' => $metadata,
                    'uploaded_by' => Auth::id(),
                ]);
            }

            $album->updateFotoCount();
        }

        return redirect()->back()->with('success', 'Foto berhasil diupload!');
    }

    /**
     * API for public website
     */
    public function apiFeatured()
    {
        $albums = DokumentasiAlbum::published()
            ->featured()
            ->with(['kategori', 'event'])
            ->orderBy('tanggal_album', 'desc')
            ->limit(6)
            ->get();

        return response()->json($albums);
    }

    public function apiByCategory($slug)
    {
        $kategori = DokumentasiKategori::where('slug', $slug)->firstOrFail();

        $albums = DokumentasiAlbum::published()
            ->where('kategori_id', $kategori->id)
            ->with(['kategori', 'event'])
            ->orderBy('tanggal_album', 'desc')
            ->paginate(12);

        return response()->json($albums);
    }
}
