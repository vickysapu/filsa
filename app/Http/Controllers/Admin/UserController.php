<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jemaat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        $users = User::with('roles', 'branch')->paginate(10);

        $roles = Role::active()
            ->when(!$currentUser->isSuperAdmin(), fn($q) => $q->where('branch_id', $currentUser->branch_id))
            ->get();

        $jemaats = collect();
        if (!$request->ajax()) {
            $jemaats = Jemaat::whereDoesntHave('user')
                ->when(!$currentUser->isSuperAdmin(), fn($q) => $q->where('branch_id', $currentUser->branch_id))
                ->orderBy('nama_lengkap')
                ->get();
        }

        if ($request->ajax()) {
            $html = view('admin.users.index', compact('users', 'roles'))->render();
            preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $matches);
            return response()->json(['html' => $matches[1] ?? '']);
        }

        return view('admin.users.index', compact('users', 'roles', 'jemaats'));
    }

    public function edit(User $user, Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && $user->branch_id !== $currentUser->branch_id) {
            abort(403);
        }

        $roles = Role::active()
            ->when(!$currentUser->isSuperAdmin(), fn($q) => $q->where('branch_id', $currentUser->branch_id))
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'user' => $user,
                'userRoles' => $user->roles->pluck('id')->toArray(),
            ]);
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jemaat_id' => 'required|uuid|exists:jemaats,id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $currentUser = Auth::user();
        $jemaat = Jemaat::findOrFail($validated['jemaat_id']);

        if (!$currentUser->isSuperAdmin() && $jemaat->branch_id !== $currentUser->branch_id) {
            return response()->json(['success' => false, 'message' => 'Anda hanya dapat membuat user dari cabang Anda.'], 403);
        }

        if (User::find($validated['jemaat_id'])) {
            return response()->json(['success' => false, 'message' => 'User untuk jemaat ini sudah terdaftar.'], 409);
        }

        if (!$currentUser->isSuperAdmin()) {
            $adminRole = Role::where('name', 'administrator')->first();
            if ($adminRole && in_array($adminRole->id, $validated['roles'])) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat memberikan role Administrator.'], 403);
            }
            $invalidRoles = collect($validated['roles'])->filter(fn($id) => !Role::find($id)?->isAccessibleBy($currentUser));
            if ($invalidRoles->isNotEmpty()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat memberikan role yang bukan dari cabang Anda.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'id' => $validated['jemaat_id'],
                'branch_id' => $jemaat->branch_id,
                'name' => $jemaat->nama_lengkap,
                'email' => $jemaat->email ?? $validated['jemaat_id'] . '@system.local',
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'] ?? true,
            ]);
            $user->assignRole($validated['roles']);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'User berhasil dibuat!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membuat user: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && $user->branch_id !== $currentUser->branch_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if (!$currentUser->isSuperAdmin()) {
            $adminRole = Role::where('name', 'administrator')->first();
            if ($adminRole && in_array($adminRole->id, $validated['roles'])) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat memberikan role Administrator.'], 403);
            }
            $invalidRoles = collect($validated['roles'])->filter(fn($id) => !Role::find($id)?->isAccessibleBy($currentUser));
            if ($invalidRoles->isNotEmpty()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat memberikan role yang bukan dari cabang Anda.'], 403);
            }
        }

        $updateData = ['name' => $validated['name'], 'email' => $validated['email'], 'is_active' => $validated['is_active'] ?? true];
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }
        $user->update($updateData);
        $user->assignRole($validated['roles']);

        return response()->json(['success' => true, 'message' => 'User berhasil diupdate!']);
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && $user->branch_id !== $currentUser->branch_id) {
            abort(403);
        }
        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus akun sendiri!'], 403);
        }
        if (!$currentUser->isSuperAdmin() && $user->hasRole('administrator')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus user Administrator.'], 403);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User berhasil dihapus!']);
    }
}
