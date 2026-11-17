<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $roles = Role::withCount(['permissions', 'users'])
            ->with('branch')
            ->when(!$currentUser->isSuperAdmin(), function ($query) use ($currentUser) {
                $query->where(function ($q) use ($currentUser) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $currentUser->branch_id);
                });
            })
            ->orderBy('is_active', 'desc')
            ->orderBy('display_name')
            ->paginate(15);

        $permissions = Permission::orderBy('group')->orderBy('display_name')->get();
        $permissionGroups = $permissions->groupBy('group');

        return view('admin.roles.index', compact('roles', 'permissionGroups'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:50',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean',
        ]);

        $role = Role::create([
            'branch_id' => $currentUser->isSuperAdmin() ? $validated['branch_id'] ?? null : $currentUser->branch_id,
            'name' => strtolower(str_replace(' ', '_', $validated['name'])),
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['permissions'])) {
            $role->givePermission($validated['permissions']);
        }

        return response()->json(['success' => true, 'message' => 'Role berhasil dibuat!']);
    }

    public function update(Request $request, Role $role)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && !$role->isAccessibleBy($currentUser)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($role, $validated, $currentUser) {
            $updateData = [
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ];

            if ($currentUser->isSuperAdmin() && array_key_exists('branch_id', $validated)) {
                $updateData['branch_id'] = $validated['branch_id'];
            }

            $role->update($updateData);
            $role->givePermission($validated['permissions'] ?? []);
        });

        return response()->json(['success' => true, 'message' => 'Role berhasil diupdate!']);
    }

    public function destroy(Role $role)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && $role->branch_id !== $currentUser->branch_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if (in_array($role->name, ['administrator', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Role sistem tidak dapat dihapus.'], 403);
        }

        $roleName = $role->display_name;
        $role->delete();

        return response()->json(['success' => true, 'message' => "Role \"{$roleName}\" berhasil dihapus!"]);
    }
}
