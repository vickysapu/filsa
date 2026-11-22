<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DokumentasiAlbum;
use App\Models\DokumentasiPhoto;
use App\Models\DokumentasiKategori;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Controllers\Concerns\ResolvesBranch;

class DokumentasiController extends Controller
{
    use ResolvesBranch;

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $branchId = $this->getBranchId();

        $query = DokumentasiAlbum::with(['kategori', 'event', 'creator']);

        // Filter Branch
        $this->scopeToBranch($query, $request);

        // Filter Lainnya
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

        $albums = $query->latest()->paginate(12);

        // Data untuk Filter & Form
        $kategoriQuery = DokumentasiKategori::query();
        $this->scopeToBranch($kategoriQuery, $request);
        $kategoris = $kategoriQuery->active()->ordered()->get();

        $events = Event::published()->orderBy('tanggal_mulai', 'desc')->get();

        $branches = $isSuperAdmin ? Branch::all() : collect();

        if ($request->ajax() || $request->query('ajax')) {
            return view('features.dokumentasi._grid', compact('albums'))->render();
        }

        return view('features.dokumentasi.index', compact('albums', 'kategoris', 'events', 'branches', 'isSuperAdmin'));
    }

    public function show(DokumentasiAlbum $album)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($album)) {
            abort(403, 'Akses ditolak');
        }

        $album->load(['kategori', 'event', 'creator', 'photos']);

        return view('features.dokumentasi.show', compact('album'));
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
                'photos.*' => 'required|image|mimes:jpg,png,jpeg|max:10240',
                'captions.*' => 'nullable|string|max:500',
            ]);

            if ($isSuperAdmin) {
                $request->validate(['branch_id' => 'required|exists:branches,id']);
            }

            // Validasi Kategori milik branch yang sama
            if (!empty($validated['kategori_id'])) {
                $kat = DokumentasiKategori::find($validated['kategori_id']);
                if ($kat && $kat->branch_id != $branchId) {
                    return response()->json(['success' => false, 'message' => 'Kategori tidak valid untuk cabang ini.'], 403);
                }
            }

            $album = DokumentasiAlbum::create([
                'branch_id' => $branchId,
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

                    $metadata = [];
                    try {
                        $imagePath = storage_path('app/public/' . $filePath);
                        if (file_exists($imagePath)) {
                            $manager = new ImageManager(new Driver());
                            $image = $manager->read($imagePath);
                            $metadata = [
                                'width' => $image->width(),
                                'height' => $image->height(),
                                'mime_type' => $photo->getMimeType(),
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::error('Image processing error: ' . $e->getMessage());
                    }

                    DokumentasiPhoto::create([
                        'branch_id' => $branchId,
                        'album_id' => $album->id,
                        'file_path' => $filePath,
                        'file_name' => $photo->hashName(),
                        'file_type' => 'image',
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'caption' => $validated['captions'][$index] ?? null,
                        'urutan' => $urutan++,
                        'is_cover' => $urutan == 2, // Foto pertama jadi cover logic sederhana
                        'metadata' => $metadata,
                        'uploaded_by' => Auth::id(),
                    ]);
                }
                $album->updateFotoCount();
            }

            return response()->json(['success' => true, 'message' => "Album berhasil dibuat dengan {$album->jumlah_foto} foto!"]);
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
            Log::error('Dokumentasi Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function edit(DokumentasiAlbum $album, Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($album)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }
            abort(403);
        }

        if ($request->ajax() || $request->query('ajax')) {
            return response()->json([
                'success' => true,
                'id' => $album->id,
                'branch_id' => $album->branch_id,
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
                'photos' => $album->photos->pluck('file_path')->toArray(),
            ]);
        }

        $kategoris = DokumentasiKategori::active()->ordered()->get();
        $events = Event::published()->orderBy('tanggal_mulai', 'desc')->get();
        return view('features.dokumentasi.edit', compact('album', 'kategoris', 'events'));
    }

    public function update(Request $request, DokumentasiAlbum $album)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($album)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            $targetBranchId = $isSuperAdmin ? $request->branch_id ?? $album->branch_id : $album->branch_id;

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
            ]);

            if ($isSuperAdmin) {
                $request->validate(['branch_id' => 'required|exists:branches,id']);
            }

            // Validasi Kategori
            if (!empty($validated['kategori_id'])) {
                $kat = DokumentasiKategori::find($validated['kategori_id']);
                if ($kat && $kat->branch_id != $targetBranchId) {
                    return response()->json(['success' => false, 'message' => 'Kategori tidak valid untuk cabang ini.'], 403);
                }
            }

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

            if ($isSuperAdmin && isset($validated['branch_id'])) {
                $updateData['branch_id'] = $validated['branch_id'];
            }

            $album->update($updateData);

            if ($request->hasFile('photos')) {
                $maxUrutan = $album->photos()->max('urutan') ?? 0;
                $urutan = $maxUrutan + 1;

                foreach ($request->file('photos') as $index => $photo) {
                    $filePath = $photo->store('dokumentasi/' . $album->slug, 'public');

                    $metadata = [];
                    try {
                        $imagePath = storage_path('app/public/' . $filePath);
                        if (file_exists($imagePath)) {
                            $manager = new ImageManager(new Driver());
                            $image = $manager->read($imagePath);
                            $metadata = [
                                'width' => $image->width(),
                                'height' => $image->height(),
                                'mime_type' => $photo->getMimeType(),
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::error('Image processing error: ' . $e->getMessage());
                    }

                    DokumentasiPhoto::create([
                        'branch_id' => $targetBranchId,
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

            return response()->json(['success' => true, 'message' => 'Album berhasil diupdate!']);
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
            Log::error('Dokumentasi Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function destroy(DokumentasiAlbum $album)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$this->ownsBranch($album)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            foreach ($album->photos as $photo) {
                Storage::disk('public')->delete($photo->file_path);
            }
            $album->delete();
            return response()->json(['success' => true, 'message' => 'Album berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Dokumentasi Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }

    public function uploadPhotos(Request $request, DokumentasiAlbum $album)
    {
        if (!$this->ownsBranch($album)) {
            abort(403, 'Akses ditolak');
        }

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
