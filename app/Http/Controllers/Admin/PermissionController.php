<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->withCount('roles')->get();

        $permissionGroups = $permissions->groupBy('group');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $permissionGroups
                    ->map(function ($group, $groupName) {
                        return [
                            'group' => ucfirst($groupName),
                            'count' => $group->count(),
                            'permissions' => $group
                                ->map(function ($perm) {
                                    return [
                                        'id' => $perm->id,
                                        'name' => $perm->name,
                                        'display_name' => $perm->display_name,
                                        'description' => $perm->description,
                                        'roles_count' => $perm->roles_count,
                                    ];
                                })
                                ->values(),
                        ];
                    })
                    ->values(),
            ]);
        }

        return view('admin.permissions.index', compact('permissionGroups'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:permissions,name|max:100',
                'display_name' => 'required|string|max:100',
                'group' => 'required|string|max:50',
                'description' => 'nullable|string|max:255',
            ]);

            $permission = Permission::create($validated);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Permission berhasil dibuat!',
                    'permission' => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'group' => $permission->group,
                        'description' => $permission->description,
                        'roles_count' => 0,
                    ],
                ],
                201,
            );
        } catch (ValidationException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            Log::error('Permission store failed', ['error' => $e->getMessage()]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan server',
                ],
                500,
            );
        }
    }

    public function destroy(Permission $permission)
    {
        $permission->loadCount('roles');

        if ($permission->roles_count > 0) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Permission tidak dapat dihapus karena masih digunakan oleh {$permission->roles_count} role.",
                ],
                422,
            );
        }

        $displayName = $permission->display_name;
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => "Permission \"{$displayName}\" berhasil dihapus!",
            'id' => $permission->id,
        ]);
    }
}
