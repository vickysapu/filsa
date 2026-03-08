<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->withCount('roles')->get();

        $permissionGroups = $permissions->groupBy('group');

        return view('admin.permissions.index', compact('permissions', 'permissionGroups'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:100',
            'display_name' => 'required|string|max:100',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        Permission::create($validated);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission berhasil dibuat!');
    }

    public function destroy(Permission $permission)
    {
        // Debug log
        Log::info('🗑️ Permission destroy called', [
            'permission_id' => $permission->id,
            'permission_name' => $permission->display_name,
            'roles_count_before_load' => $permission->roles_count, // Akan null
        ]);

        $permission->loadCount('roles');

        Log::info('📊 Roles count loaded', [
            'roles_count' => $permission->roles_count,
        ]);

        if ($permission->roles_count > 0) {
            Log::warning('⚠️ Delete blocked: permission in use');
            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'Permission tidak dapat dihapus karena masih digunakan oleh ' . $permission->roles_count . ' role.');
        }

        Log::info('✅ Deleting permission', ['id' => $permission->id]);
        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission "' . $permission->display_name . '" berhasil dihapus!');
    }
}
