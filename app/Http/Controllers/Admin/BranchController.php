<?php
// app/Http/Controllers/Admin/BranchController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['users'])
            ->orderBy('is_headquarter', 'desc')
            ->orderBy('name')
            ->paginate(15);
        return view('admin.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            Branch::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'is_headquarter' => false,
                'is_active' => $validated['is_active'] ?? true,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Cabang berhasil ditambahkan!']);
    }

    public function destroy(Branch $branch)
    {
        if ($branch->is_headquarter) {
            return response()->json(['success' => false, 'message' => 'Administrator tidak dapat dihapus.'], 403);
        }

        $currentUser = Auth::user();
        if ($currentUser->branch_id === $branch->id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus cabang tempat Anda terdaftar.'], 403);
        }

        $branchName = $branch->name;

        DB::transaction(function () use ($branch) {
            $branch->delete();
        });

        return response()->json(['success' => true, 'message' => "Cabang \"{$branchName}\" beserta semua datanya berhasil dihapus!"]);
    }

    public function toggleActive(Branch $branch)
    {
        if ($branch->is_headquarter) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Administrator tidak dapat dinonaktifkan.',
                ],
                403,
            );
        }

        $newStatus = !$branch->is_active;

        $branch->update(['is_active' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => $newStatus ? 'Cabang diaktifkan.' : 'Cabang dinonaktifkan.',
            'is_active' => $newStatus,
        ]);
    }
}
