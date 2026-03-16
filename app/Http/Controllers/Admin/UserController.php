<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $users = User::with('roles', 'branch')->paginate(10);
        $roles = Role::active()
            ->when(!$currentUser->isSuperAdmin(), fn($q) => $q->where('branch_id', $currentUser->branch_id))
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.index', compact('users', 'roles'))->render(),
            ]);
        }

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function edit(User $user, Request $request)
    {
        /** @var \App\Models\User $currentUser */
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
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

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

        $user = User::create([
            'branch_id' => $currentUser->branch_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);
        $user->assignRole($validated['roles']);

        return response()->json(['success' => true, 'message' => 'User berhasil dibuat!']);
    }

    public function update(Request $request, User $user)
    {
        /** @var \App\Models\User $currentUser */
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
        /** @var \App\Models\User $currentUser */
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
