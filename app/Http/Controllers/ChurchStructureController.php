<?php

namespace App\Http\Controllers;

use App\Models\ChurchStructure;
use App\Models\Jemaat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChurchStructureController extends Controller
{
    protected function getBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    public function index(Request $request)
    {
        $branchId = $this->getBranchId();
        $query = ChurchStructure::query()->with(['jemaat', 'children.jemaat']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('position_name', 'like', "%{$request->search}%")->orWhereHas('jemaat', function ($qj) use ($request) {
                    $qj->where('nama_lengkap', 'like', "%{$request->search}%");
                });
            });
        }

        if ($request->filled('position_level')) {
            $query->where('position_level', $request->position_level);
        }

        $structures = $query->whereNull('parent_id')->orderBy('sort_order')->paginate(20);
        $jemaats = Jemaat::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'NIK']);

        if ($request->ajax()) {
            return view('features.church_structure._table', compact('structures'))->render();
        }

        return view('features.church_structure.index', compact('structures', 'jemaats', 'branchId'));
    }

    public function apiIndex(Request $request)
    {
        $branchId = $this->getBranchId();
        $jemaats = Jemaat::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);
        return response()->json($jemaats);
    }

    public function create()
    {
        $branchId = $this->getBranchId();
        $jemaats = Jemaat::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);
        $parents = ChurchStructure::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereNull('parent_id')
            ->orderBy('position_name')
            ->get(['id', 'position_name']);
        return view('features.church_structure.create', compact('jemaats', 'parents'));
    }

    public function store(Request $request)
    {
        $branchId = $this->getBranchId();
        $validated = $request->validate([
            'position_name' => 'required|string|max:100',
            'position_level' => ['required', Rule::in(['gembala', 'pengurus', 'pelayan', 'staff', 'lainnya'])],
            'jemaat_id' => 'nullable|exists:jemaats,id',
            'parent_id' => 'nullable|exists:church_structures,id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if ($validated['parent_id'] && $branchId) {
            $parent = ChurchStructure::find($validated['parent_id']);
            if ($parent && $parent->branch_id !== $branchId) {
                abort(403);
            }
        }

        $maxOrder =
            ChurchStructure::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->where('parent_id', $validated['parent_id'] ?? null)
                ->max('sort_order') ?? 0;

        ChurchStructure::create([
            'branch_id' => $branchId,
            'position_name' => $validated['position_name'],
            'position_level' => $validated['position_level'],
            'jemaat_id' => $validated['jemaat_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => $maxOrder + 1,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Struktur berhasil ditambahkan']);
        }
        return redirect()->route('admin.church_structure.index')->with('success', 'Struktur gereja berhasil ditambahkan!');
    }

    public function show(ChurchStructure $churchStructure, Request $request)
    {
        $branchId = $this->getBranchId();
        if ($branchId && $churchStructure->branch_id !== $branchId) {
            abort(403);
        }
        $churchStructure->load(['jemaat', 'parent', 'children.jemaat']);
        if ($request->ajax()) {
            return response()->json($churchStructure);
        }
        return view('features.church_structure.show', compact('churchStructure'));
    }

    public function edit(ChurchStructure $churchStructure, Request $request)
    {
        $branchId = $this->getBranchId();
        if ($branchId && $churchStructure->branch_id !== $branchId) {
            abort(403);
        }
        $jemaats = Jemaat::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);
        $parents = ChurchStructure::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('id', '!=', $churchStructure->id)
            ->whereNull('parent_id')
            ->orderBy('position_name')
            ->get(['id', 'position_name']);
        if ($request->ajax()) {
            return response()->json($churchStructure);
        }
        return view('features.church_structure.edit', compact('churchStructure', 'jemaats', 'parents'));
    }

    public function update(Request $request, ChurchStructure $churchStructure)
    {
        $branchId = $this->getBranchId();
        if ($branchId && $churchStructure->branch_id !== $branchId) {
            abort(403);
        }

        $validated = $request->validate([
            'position_name' => 'required|string|max:100',
            'position_level' => ['required', Rule::in(['gembala', 'pengurus', 'pelayan', 'staff', 'lainnya'])],
            'jemaat_id' => 'nullable|exists:jemaats,id',
            'parent_id' => 'nullable|exists:church_structures,id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if ($validated['parent_id'] && $validated['parent_id'] !== $churchStructure->parent_id) {
            $parent = ChurchStructure::find($validated['parent_id']);
            if ($parent && ($parent->branch_id !== $branchId || $this->isDescendant($parent->id, $churchStructure->id))) {
                abort(403, 'Tidak dapat memindahkan ke child sendiri atau branch berbeda');
            }
        }

        $churchStructure->update([
            'position_name' => $validated['position_name'],
            'position_level' => $validated['position_level'],
            'jemaat_id' => $validated['jemaat_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Struktur berhasil diupdate']);
        }
        return redirect()->route('admin.church_structure.index')->with('success', 'Data struktur berhasil diupdate!');
    }

    public function destroy(ChurchStructure $churchStructure)
    {
        $branchId = $this->getBranchId();
        if ($branchId && $churchStructure->branch_id !== $branchId) {
            abort(403);
        }
        $this->deleteWithChildren($churchStructure);
        return response()->json(['success' => true, 'message' => 'Struktur berhasil dihapus']);
    }

    public function reorder(Request $request)
    {
        $branchId = $this->getBranchId();
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:church_structures,id',
            'items.*.parent_id' => 'nullable|exists:church_structures,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                $structure = ChurchStructure::find($item['id']);
                if ($branchId && $structure->branch_id !== $branchId) {
                    continue;
                }
                if ($item['parent_id']) {
                    $parent = ChurchStructure::find($item['parent_id']);
                    if ($parent && $parent->branch_id !== $branchId) {
                        continue;
                    }
                }
                $structure->update(['parent_id' => $item['parent_id'] ?? null, 'sort_order' => $item['sort_order']]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Urutan struktur berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan urutan'], 500);
        }
    }

    public function apiTree(Request $request)
    {
        $branchId = $this->getBranchId();
        $query = ChurchStructure::with(['jemaat', 'children.jemaat'])->whereNull('parent_id');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $roots = $query->orderBy('sort_order')->get();

        $buildTree = function ($items) use (&$buildTree, $branchId) {
            return $items->map(function ($item) use (&$buildTree, $branchId) {
                $children = $item->children->filter(fn($c) => !$branchId || $c->branch_id === $branchId);
                return [
                    'id' => $item->id,
                    'position_name' => $item->position_name,
                    'position_level' => $item->position_level,
                    'jemaat' => $item->jemaat ? ['id' => $item->jemaat->id, 'nama_lengkap' => $item->jemaat->nama_lengkap] : null,
                    'description' => $item->description,
                    'is_active' => $item->is_active,
                    'children' => $buildTree($children),
                ];
            });
        };

        return response()->json(['tree' => $buildTree($roots)]);
    }

    private function isDescendant($parentId, $childId, $visited = [])
    {
        if ($parentId === $childId) {
            return true;
        }
        if (in_array($parentId, $visited)) {
            return false;
        }
        $visited[] = $parentId;
        $children = ChurchStructure::where('parent_id', $parentId)->get();
        foreach ($children as $child) {
            if ($this->isDescendant($child->id, $childId, $visited)) {
                return true;
            }
        }
        return false;
    }

    private function deleteWithChildren(ChurchStructure $structure)
    {
        foreach ($structure->children as $child) {
            $this->deleteWithChildren($child);
        }
        $structure->delete();
    }
}
