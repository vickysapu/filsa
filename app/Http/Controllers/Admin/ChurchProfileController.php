<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchProfile;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChurchProfileController extends Controller
{
    protected function getBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    public function index(Request $request)
    {
        $branchId = $this->getBranchId();

        $query = ChurchProfile::query()->with('branch');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->search) {
            $query->where('church_name', 'like', "%{$request->search}%");
        }

        $profiles = $query->latest()->paginate(15);

        return view('admin.church_profile.index', compact('profiles'));
    }

    public function create()
    {
        $branchId = $this->getBranchId();
        $branches = $branchId ? Branch::where('id', $branchId)->get() : Branch::orderBy('name')->get();

        return view('admin.church_profile.form', [
            'churchProfile' => null, 
            'branches' => $branches
        ]);
    }

    public function store(Request $request)
    {
        $branchId = $this->getBranchId();

        $validated = $request->validate([
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'church_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'history' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'values' => 'nullable|array',
            'values.*.label' => 'required_with:values|string|max:100',
            'values.*.description' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:5120',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpg,png,jpeg,webp|max:5120',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx|max:10240',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'map_embed_code' => 'nullable|string|max:2000',
            'map_latitude' => 'nullable|numeric',
            'map_longitude' => 'nullable|numeric',
            'service_time_sunday' => 'nullable|date_format:H:i',
            'service_time_wednesday' => 'nullable|date_format:H:i',
            'service_time_friday' => 'nullable|date_format:H:i',
            'service_note' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        $data = $validated;

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('church-profiles/logos', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('church-profiles/gallery', 'public');
            }
            $data['gallery_images'] = array_merge($data['gallery_images'] ?? [], $galleryPaths);
        }

        if ($request->hasFile('documents')) {
            $docPaths = [];
            foreach ($request->file('documents') as $file) {
                $docPaths[] = $file->store('church-profiles/documents', 'public');
            }
            $data['documents'] = array_merge($data['documents'] ?? [], $docPaths);
        }

        if (empty($data['meta_title'])) {
            $data['meta_title'] = $data['church_name'];
        }

        if ($data['is_published'] ?? false) {
            $data['published_at'] = now();
        }

        $data['branch_id'] = $branchId ?? ($data['branch_id'] ?? null);

        ChurchProfile::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Profile gereja berhasil disimpan']);
        }

        return redirect()->route('admin.church_profile.index')->with('success', 'Profile gereja berhasil ditambahkan!');
    }

    public function edit(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }

        $branches = $branchId ? Branch::where('id', $branchId)->get() : Branch::orderBy('name')->get();

        return view('admin.church_profile.form', compact('churchProfile', 'branches'));
    }

    public function update(Request $request, ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'church_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'history' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'values' => 'nullable|array',
            'values.*.label' => 'required_with:values|string|max:100',
            'values.*.description' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:5120',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpg,png,jpeg,webp|max:5120',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx|max:10240',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'map_embed_code' => 'nullable|string|max:2000',
            'map_latitude' => 'nullable|numeric',
            'map_longitude' => 'nullable|numeric',
            'service_time_sunday' => 'nullable|date_format:H:i',
            'service_time_wednesday' => 'nullable|date_format:H:i',
            'service_time_friday' => 'nullable|date_format:H:i',
            'service_note' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        $data = $validated;

        if ($request->hasFile('logo')) {
            if ($churchProfile->logo_path) {
                Storage::disk('public')->delete($churchProfile->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('church-profiles/logos', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('church-profiles/gallery', 'public');
            }
            $data['gallery_images'] = array_merge($churchProfile->gallery_images ?? [], $galleryPaths);
        }

        if ($request->hasFile('documents')) {
            $docPaths = [];
            foreach ($request->file('documents') as $file) {
                $docPaths[] = $file->store('church-profiles/documents', 'public');
            }
            $data['documents'] = array_merge($churchProfile->documents ?? [], $docPaths);
        }

        if (empty($data['meta_title'])) {
            $data['meta_title'] = $data['church_name'];
        }

        if (isset($data['is_published'])) {
            $data['published_at'] = $data['is_published'] ? now() : null;
        }

        $churchProfile->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Profile gereja berhasil diupdate']);
        }

        return redirect()->route('admin.church_profile.index')->with('success', 'Profile gereja berhasil diupdate!');
    }

    public function destroy(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }

        if ($churchProfile->logo_path) {
            Storage::disk('public')->delete($churchProfile->logo_path);
        }

        if ($churchProfile->gallery_images) {
            foreach ($churchProfile->gallery_images as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        if ($churchProfile->documents) {
            foreach ($churchProfile->documents as $doc) {
                Storage::disk('public')->delete($doc);
            }
        }

        $churchProfile->delete();

        return response()->json(['success' => true, 'message' => 'Profile gereja berhasil dihapus']);
    }

    public function removeGalleryImage(Request $request, ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }

        $request->validate(['image_path' => 'required|string']);

        $galleryImages = $churchProfile->gallery_images ?? [];
        $imagePath = $request->image_path;

        if (($key = array_search($imagePath, $galleryImages)) !== false) {
            unset($galleryImages[$key]);
            Storage::disk('public')->delete($imagePath);

            $churchProfile->update(['gallery_images' => array_values($galleryImages)]);
        }

        return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus']);
    }

    public function removeDocument(Request $request, ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }

        $request->validate(['doc_path' => 'required|string']);

        $documents = $churchProfile->documents ?? [];
        $docPath = $request->doc_path;

        if (($key = array_search($docPath, $documents)) !== false) {
            unset($documents[$key]);
            Storage::disk('public')->delete($docPath);

            $churchProfile->update(['documents' => array_values($documents)]);
        }

        return response()->json(['success' => true, 'message' => 'Dokumen berhasil dihapus']);
    }

    public function preview(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }

        return view('admin.church_profile.preview', compact('churchProfile'));
    }

    public function togglePublish(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }

        $churchProfile->update([
            'is_published' => !$churchProfile->is_published,
            'published_at' => !$churchProfile->is_published ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $churchProfile->is_published ? 'Profile telah dipublikasikan' : 'Profile telah di-unpublish',
            'is_published' => $churchProfile->is_published,
        ]);
    }
}
