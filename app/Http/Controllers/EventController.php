<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventKategori;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\UnifiedNotification;
use Illuminate\Support\Facades\Notification;

class EventController extends Controller
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
        $user = Auth::user();
        $branchId = $this->getBranchId();

        $query = Event::with(['kategori', 'branch']);

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

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

        $events = $query->latest()->paginate(10);

        $kategoriQuery = EventKategori::query();

        if (class_exists(\App\Models\Scopes\BranchScope::class)) {
            $kategoriQuery->withoutGlobalScope(\App\Models\Scopes\BranchScope::class);
        }

        if (!$isSuperAdmin) {
            if ($branchId) {
                $kategoriQuery->where('branch_id', $branchId);
            } else {
                $kategoriQuery->whereRaw('1=0');
            }
        } else {
            if ($request->filled('branch_id')) {
                $kategoriQuery->where('branch_id', $request->branch_id);
            }
        }

        $kategoris = $kategoriQuery->orderBy('nama_kategori')->get();
        $branches = $isSuperAdmin ? Branch::all() : collect();

        return view('features.event.index', compact('events', 'kategoris', 'branches', 'isSuperAdmin'));
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

            $validationRules = [
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
            ];

            if ($isSuperAdmin) {
                $validationRules['branch_id'] = 'required|exists:branches,id';
            }

            $validated = $request->validate($validationRules);

            $kategoriCheck = EventKategori::find($validated['kategori_id']);
            if (!$isSuperAdmin && $kategoriCheck && $kategoriCheck->branch_id != $branchId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Kategori yang dipilih tidak valid untuk cabang ini.',
                    ],
                    403,
                );
            }

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
                'branch_id' => $branchId,
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
                'status' => $validated['status'] ?? 'draft',
                'is_featured' => $request->has('is_featured'),
                'show_in_website' => $request->has('show_in_website'),
                'created_by' => Auth::id(),
            ]);

            // Kirim Notifikasi Event Baru
            $this->sendNotificationToAuthorizedUsers(
                $branchId,
                'Event Baru Dibuat',
                "Event '{$event->nama_event}' telah ditambahkan.",
                route('admin.event.index'),
                'ri-calendar-event-line',
                'text-success',
                'EVENT'
            );

            return response()->json(['success' => true, 'message' => 'Event berhasil dibuat']);
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
            Log::error('Event Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function edit(Event $event, Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $event->branch_id !== $branchId) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }
            abort(403);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $event->id,
                'branch_id' => $event->branch_id,
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

        // Fallback jika non-AJAX (meskipun biasanya langsung ke view index)
        $kategoris = EventKategori::orderBy('nama_kategori')->get();
        $branches = $isSuperAdmin ? Branch::all() : collect();
        return view('features.event.index', compact('event', 'kategoris', 'branches', 'isSuperAdmin'));
    }

    public function update(Request $request, Event $event)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $event->branch_id !== $branchId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            $targetBranchId = $isSuperAdmin ? $request->branch_id ?? $event->branch_id : $event->branch_id;

            $validationRules = [
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
            ];

            if ($isSuperAdmin) {
                $validationRules['branch_id'] = 'required|exists:branches,id';
            }

            $validated = $request->validate($validationRules);

            $kategoriCheck = EventKategori::find($validated['kategori_id']);
            if ($kategoriCheck && $kategoriCheck->branch_id != $targetBranchId) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Kategori yang dipilih tidak valid untuk cabang ini.',
                    ],
                    403,
                );
            }

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

            if ($isSuperAdmin && isset($validated['branch_id'])) {
                $updateData['branch_id'] = $validated['branch_id'];
            }

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

            // Kirim Notifikasi Update Event
            $this->sendNotificationToAuthorizedUsers(
                $targetBranchId,
                'Event Diperbarui',
                "Event '{$event->nama_event}' telah diperbarui informasinya.",
                route('admin.event.index'),
                'ri-edit-line',
                'text-warning',
                'EVENT'
            );

            return response()->json(['success' => true, 'message' => 'Event berhasil diupdate!']);
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
            Log::error('Event Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function destroy(Event $event)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $event->branch_id !== $branchId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            // Simpan info sebelum hapus
            $namaEvent = $event->nama_event;
            $targetBranchId = $event->branch_id;

            if ($event->gambar_featured) {
                Storage::disk('public')->delete($event->gambar_featured);
            }
            if ($event->gambar_galeri) {
                foreach ($event->gambar_galeri as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $event->delete();

            // Kirim Notifikasi Hapus Event
            $this->sendNotificationToAuthorizedUsers(
                $targetBranchId,
                'Event Dihapus',
                "Event '{$namaEvent}' telah dihapus dari sistem.",
                route('admin.event.index'),
                'ri-delete-bin-line',
                'text-danger',
                'EVENT'
            );

            return response()->json(['success' => true, 'message' => 'Event berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Event Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }

    private function sendNotificationToAuthorizedUsers($branchId, $title, $message, $url, $icon, $color, $source)
    {
        $query = User::where('is_active', true)->with('roles.permissions');

        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereHas('roles', function($r) {
                      $r->where('name', 'administrator') 
                        ->orWhere('name', 'Super Admin'); 
                  });
            });
        }

        $users = $query->get();

        $authorizedUsers = $users->filter(function ($user) {
            return $user->hasPermission('event.view'); 
        });

        if ($authorizedUsers->isNotEmpty()) {
            Notification::send(
                $authorizedUsers, 
                new UnifiedNotification($title, $message, $url, $icon, $color, $source)
            );
        }
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
}
