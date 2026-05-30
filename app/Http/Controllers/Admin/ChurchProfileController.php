<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchProfile;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ChurchProfileController extends Controller
{
    protected function getBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    protected function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && ($user->hasRole('administrator') || ($user->branch && $user->branch->is_headquarter));
    }

    public function index(Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        $churchProfile = ChurchProfile::when($branchId && !$isSuperAdmin, function ($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })->first();

        if (!$churchProfile) {
            return redirect()->route('admin.church_profile.create');
        }

        return view('admin.church_profile.index', compact('churchProfile'));
    }

    public function create()
    {
        $branchId = $this->getBranchId();
        $branches = $branchId 
            ? Branch::where('id', $branchId)->get() 
            : Branch::orderBy('name')->get();

        return view('admin.church_profile.form', [
            'churchProfile' => null,
            'branches' => $branches,
            'defaultBranchId' => $defaultBranchId,
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeChurchProfileRequest($request);

        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        $existing = ChurchProfile::when($branchId && !$isSuperAdmin, fn($q) => $q->where('branch_id', $branchId))->first();
        if ($existing) {
            return back()->with('error', 'Profile gereja sudah ada. Silakan edit data yang tersedia.');
        }

        $validated = $request->validate($this->churchProfileRules());

        $data = $this->prepareChurchProfileData($request, $validated);

        $data['branch_id'] = $branchId ?? ($validated['branch_id'] ?? null);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('church-profiles/logos', 'public');
        }

        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];

            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('church-profiles/gallery', 'public');
            }

            $data['gallery_images'] = $galleryPaths;
        }

        if ($request->hasFile('documents')) {
            $docPaths = [];

            foreach ($request->file('documents') as $file) {
                $docPaths[] = $file->store('church-profiles/documents', 'public');
            }

            $data['documents'] = $docPaths;
        }

        ChurchProfile::create($data);

        return redirect()
            ->route('admin.church_profile.index')
            ->with('success', 'Profile gereja berhasil ditambahkan!');
    }

    public function edit(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $churchProfile->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        $branches = $isSuperAdmin ? Branch::orderBy('name')->get() : collect();

        return view('admin.church_profile.form', compact('churchProfile', 'branches'));
    }

    public function update(Request $request, ChurchProfile $churchProfile)
    {
        $this->normalizeChurchProfileRequest($request);

        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $churchProfile->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        $validated = $request->validate($this->churchProfileRules());

        $data = $this->prepareChurchProfileData(
            $request,
            $validated,
            $churchProfile
        );

        if ($request->hasFile('logo')) {

            if ($churchProfile->logo_path) {
                Storage::disk('public')->delete($churchProfile->logo_path);
            }

            $data['logo_path'] = $request->file('logo')
                ->store('church-profiles/logos', 'public');
        }

        if ($request->hasFile('gallery_images')) {

            $galleryPaths = [];

            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file
                    ->store('church-profiles/gallery', 'public');
            }

            $data['gallery_images'] = array_merge(
                $churchProfile->gallery_images ?? [],
                $galleryPaths
            );
        }

        if ($request->hasFile('documents')) {

            $docPaths = [];

            foreach ($request->file('documents') as $file) {
                $docPaths[] = $file
                    ->store('church-profiles/documents', 'public');
            }

            $data['documents'] = array_merge(
                $churchProfile->documents ?? [],
                $docPaths
            );
        }

        $churchProfile->update($data);

        return redirect()
            ->route('admin.church_profile.index')
            ->with('success', 'Profile gereja berhasil diupdate!');
    }


    protected function churchProfileRules(): array
    {
        return [
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'church_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'address' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'history' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:5120',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx|max:10240',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'meta_keywords.*' => 'nullable|string|max:100',
            'map_embed_code' => 'nullable|string|max:2000',
            'map_latitude' => 'nullable|numeric',
            'map_longitude' => 'nullable|numeric',
            'is_published' => 'boolean',
        ];
    }

    protected function prepareChurchProfileData(Request $request, array $validated, ChurchProfile $churchProfile = null): array
    {
        $data = $validated;
        $data['is_published'] = $request->has('is_published');

        if ($request->hasFile('logo') && $churchProfile?->logo_path) {
            Storage::disk('public')->delete($churchProfile->logo_path);
        }

        $data['logo_path'] = $churchProfile?->logo_path ?? null;

        if (empty($data['meta_title'])) {
            $data['meta_title'] = $data['church_name'];
        }

        $data['published_at'] = $data['is_published'] ? now() : null;

        return $data;
    }

    public function destroy(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $churchProfile->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        if ($churchProfile->logo_path && Storage::disk('public')->exists($churchProfile->logo_path)) {
            Storage::disk('public')->delete($churchProfile->logo_path);
        }

        if ($churchProfile->documents) {
            foreach ($churchProfile->documents as $doc) {
                if (Storage::disk('public')->exists($doc)) {
                    Storage::disk('public')->delete($doc);
                }
            }
        }

        $churchProfile->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Profile gereja berhasil dihapus']);
        }

        return redirect()->route('admin.church_profile.index')->with('success', 'Profile gereja berhasil dihapus!');
    }

    public function removeDocument(Request $request, ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $churchProfile->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        $request->validate(['doc_path' => 'required|string']);
        $docPath = $request->doc_path;

        $documents = $churchProfile->documents ?? [];
        if (($key = array_search($docPath, $documents)) !== false) {
            unset($documents[$key]);
            if (Storage::disk('public')->exists($docPath)) {
                Storage::disk('public')->delete($docPath);
            }
            $churchProfile->update(['documents' => array_values($documents)]);
        }

        return response()->json(['success' => true, 'message' => 'Dokumen berhasil dihapus']);
    }

    public function preview(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $churchProfile->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        return view('admin.church_profile.preview', compact('churchProfile'));
    }

    public function togglePublish(ChurchProfile $churchProfile)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $churchProfile->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
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
    
    protected function normalizeChurchProfileRequest(Request $request): void
    {
        $request->merge([
            'meta_keywords' => $request->meta_keywords
                ? array_filter(array_map('trim', explode(',', $request->meta_keywords)))
                : [],
        ]);
    }

}
