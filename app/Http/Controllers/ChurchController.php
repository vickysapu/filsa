<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChurchController extends Controller
{
    // Penanda baris "item" yang disimpan di kolom pic_name
    private const ITEM_PREFIX = 'item::';

    private function branchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    private function isItem(Church $church): bool
    {
        return str_starts_with((string) $church->pic_name, self::ITEM_PREFIX);
    }

    private function parentIdOf(Church $item): ?string
    {
        $id = substr((string) $item->pic_name, strlen(self::ITEM_PREFIX));
        return $id === '' ? null : $id;
    }

    // Pastikan gereja induk valid, satu cabang, dan bukan item
    private function validParent(?string $churchId): ?string
    {
        if (!$churchId) {
            return null;
        }

        $parent = Church::where('branch_id', $this->branchId())->where('id', $churchId)->first();

        if (!$parent || $this->isItem($parent)) {
            abort(422, 'Gereja induk tidak valid');
        }

        return (string) $parent->id;
    }

    public function index()
    {
        $base = Church::where('branch_id', $this->branchId());

        $churches = (clone $base)
            ->where(function ($q) {
                $q->whereNull('pic_name')->orWhere('pic_name', 'not like', self::ITEM_PREFIX . '%');
            })
            ->orderBy('name')
            ->paginate(20, ['*'], 'church_page');

        $items = (clone $base)
            ->where('pic_name', 'like', self::ITEM_PREFIX . '%')
            ->orderBy('name')
            ->paginate(20, ['*'], 'item_page');

        // Daftar gereja untuk dropdown & nama gereja induk
        $churchOptions = (clone $base)
            ->where(function ($q) {
                $q->whereNull('pic_name')->orWhere('pic_name', 'not like', self::ITEM_PREFIX . '%');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $parentNames = $churchOptions->pluck('name', 'id');

        $items->getCollection()->transform(function ($item) use ($parentNames) {
            $parentId = $this->parentIdOf($item);
            $item->parent_id = $parentId;
            $item->parent_name = $parentId ? $parentNames[$parentId] ?? null : null;
            return $item;
        });

        return view('features.finance.churches.index', compact('churches', 'items', 'churchOptions'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type') === 'item' ? 'item' : 'church';

        if ($type === 'item') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'church_id' => 'nullable|string|max:64',
            ]);

            Church::create([
                'branch_id' => $this->branchId(),
                'name' => $validated['name'],
                'pic_name' => self::ITEM_PREFIX . ($this->validParent($validated['church_id'] ?? null) ?? ''),
                'is_active' => true,
            ]);

            return response()->json(['success' => true, 'message' => 'Item berhasil ditambahkan']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:255',
        ]);

        Church::create([
            'branch_id' => $this->branchId(),
            'name' => $validated['name'],
            'pic_name' => $validated['pic_name'] ?? null,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Gereja/Pendeta berhasil ditambahkan']);
    }

    public function update(Request $request, Church $church)
    {
        if ($church->branch_id != $this->branchId()) {
            abort(403);
        }

        // Tipe ditentukan dari data yang tersimpan, bukan dari request
        if ($this->isItem($church)) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'church_id' => 'nullable|string|max:64',
            ]);

            $church->update([
                'name' => $validated['name'],
                'pic_name' => self::ITEM_PREFIX . ($this->validParent($validated['church_id'] ?? null) ?? ''),
            ]);

            return response()->json(['success' => true, 'message' => 'Item berhasil diperbarui']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:255',
        ]);

        $church->update($validated);

        return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);
    }

    public function destroy(Church $church)
    {
        if ($church->branch_id != $this->branchId()) {
            abort(403);
        }

        $church->delete();

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
    }
}
