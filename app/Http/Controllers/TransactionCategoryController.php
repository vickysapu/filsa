<?php

namespace App\Http\Controllers;

use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionCategoryController extends Controller
{
    private function getUserBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    public function index(Request $request)
    {
        $query = TransactionCategory::with('branch');

        $branchId = $this->getUserBranchId();
        if ($branchId) {
            $query->forBranch($branchId);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->latest()->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('features.finance.categories._table', compact('categories'))->render(),
                'pagination' => $categories->withQueryString()->links('pagination::bootstrap-5')->toHtml(),
            ]);
        }

        return view('features.finance.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $branchId = $this->getUserBranchId();

        try {
            TransactionCategory::create([
                'type' => $validated['type'],
                'branch_id' => $branchId,
                'name' => $validated['name'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Kategori dengan nama ini sudah ada',
                    ],
                    422,
                );
            }
            throw $e;
        }
    }

    public function update(Request $request, TransactionCategory $category)
    {
        $branchId = $this->getUserBranchId();
        if ($branchId && $category->branch_id !== $branchId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        try {
            $category->update([
                'name' => $validated['name'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diupdate',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Kategori dengan nama ini sudah ada',
                    ],
                    422,
                );
            }
            throw $e;
        }
    }

    public function toggleActive(TransactionCategory $category)
    {
        $branchId = $this->getUserBranchId();
        if ($branchId && $category->branch_id !== $branchId) {
            abort(403);
        }

        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Status kategori berhasil diubah',
        ]);
    }

    public function destroy(TransactionCategory $category)
    {
        $branchId = $this->getUserBranchId();
        if ($branchId && $category->branch_id !== $branchId) {
            abort(403);
        }

        if ($category->isGlobal()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Kategori global tidak dapat dihapus',
                ],
                403,
            );
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus',
        ]);
    }
}
