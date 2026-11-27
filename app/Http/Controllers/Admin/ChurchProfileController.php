<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChurchProfileController extends Controller
{
    protected function getBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    protected function authorizeBranch(ChurchProfile $churchProfile): void
    {
        $branchId = $this->getBranchId();

        if ($branchId && $churchProfile->branch_id !== $branchId) {
            abort(403);
        }
    }

    protected function rules(): array
    {
        return [
            'church_name' => 'required|string|max:255',
            'address'     => 'nullable|string|max:1000',
            'city'        => 'nullable|string|max:100',
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'history'     => 'nullable|string',
            'vision'      => 'nullable|string',
            'mission'     => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpg,png,jpeg,webp|max:5120',
            'facebook'    => 'nullable|url|max:255',
            'instagram'   => 'nullable|url|max:255',
            'youtube'     => 'nullable|url|max:255',
            'tiktok'      => 'nullable|url|max:255',
        ];
    }

    public function index(Request $request)
    {
        $query = ChurchProfile::query();

        if ($branchId = $this->getBranchId()) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('search')) {
            $query->where('church_name', 'like', "%{$request->search}%");
        }

        $profiles = $query->latest()->paginate(15);

        return view('admin.church_profile.index', compact('profiles'));
    }

    public function create()
    {
        return view('admin.church_profile.form', ['churchProfile' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $data['branch_id']    = $this->getBranchId(); // otomatis dari user
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('church-profiles/logos', 'public');
        }

        ChurchProfile::create($data);

        return redirect()
            ->route('admin.church_profile.index')
            ->with('success', 'Profile gereja berhasil ditambahkan!');
    }

    public function edit(ChurchProfile $churchProfile)
    {
        $this->authorizeBranch($churchProfile);

        return view('admin.church_profile.form', compact('churchProfile'));
    }

    public function update(Request $request, ChurchProfile $churchProfile)
    {
        $this->authorizeBranch($churchProfile);

        $data = $request->validate($this->rules());

        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published']
            ? ($churchProfile->published_at ?? now())
            : null;

        if ($request->hasFile('logo')) {
            if ($churchProfile->logo_path) {
                Storage::disk('public')->delete($churchProfile->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('church-profiles/logos', 'public');
        }

        $churchProfile->update($data);

        return redirect()
            ->route('admin.church_profile.index')
            ->with('success', 'Profile gereja berhasil diupdate!');
    }

    public function destroy(ChurchProfile $churchProfile)
    {
        $this->authorizeBranch($churchProfile);

        if ($churchProfile->logo_path) {
            Storage::disk('public')->delete($churchProfile->logo_path);
        }

        $churchProfile->delete();

        return response()->json(['success' => true, 'message' => 'Profile gereja berhasil dihapus']);
    }

    public function preview(ChurchProfile $churchProfile)
    {
        $this->authorizeBranch($churchProfile);

        return view('admin.church_profile.preview', compact('churchProfile'));
    }

    public function togglePublish(ChurchProfile $churchProfile)
    {
        $this->authorizeBranch($churchProfile);

        $publish = !$churchProfile->is_published;

        $churchProfile->update([
            'is_published' => $publish,
            'published_at' => $publish ? now() : null,
        ]);

        return response()->json([
            'success'      => true,
            'message'      => $publish ? 'Profile telah dipublikasikan' : 'Profile telah di-unpublish',
            'is_published' => $publish,
        ]);
    }
}