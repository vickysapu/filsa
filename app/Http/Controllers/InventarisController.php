<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventaris::with('branch')->orderByDesc('created_at');

        if ($request->user()?->roles->first()?->branch_id) {
            $query->where('branch_id', $request->user()->roles->first()->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('nama_barang', 'like', '%' . $request->search . '%')
                    ->orWhere('kategori', 'like', '%' . $request->search . '%');
            });
        }

        $inventaris = $query->get();

        if ($request->ajax() || $request->query('ajax')) {
            return view('features.inventaris._list', compact('inventaris'))->render();
        }

        return view('features.inventaris.index', compact('inventaris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'   => ['required', 'uuid', Rule::exists('branches', 'id')],
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:255'],
            'kondisi'     => ['required', 'string', Rule::in(['Baik', 'Rusak Ringan', 'Rusak Berat'])],
        ]);

        Inventaris::create([
            'id'          => Str::uuid(),
            'branch_id'   => $validated['branch_id'],
            'nama_barang' => $validated['nama_barang'],
            'kategori'    => $validated['kategori'],
            'kondisi'     => $validated['kondisi'],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inventaris berhasil ditambahkan.'
            ]);
        }

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Inventaris berhasil ditambahkan.');
    }

    public function edit(Request $request, Inventaris $inventaris)
    {
        if ($request->ajax() || $request->query('ajax')) {
            return response()->json([
                'success' => true,
                'data'    => $inventaris
            ]);
        }

        // Fallback jika diakses langsung
        return redirect()->route('admin.inventaris.index');
    }

    public function update(Request $request, Inventaris $inventaris)
    {
        $validated = $request->validate([
            'branch_id'   => ['required', 'uuid', Rule::exists('branches', 'id')],
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori'    => ['required', 'string', 'max:255'],
            'kondisi'     => ['required', 'string', Rule::in(['Baik', 'Rusak Ringan', 'Rusak Berat'])],
        ]);

        $inventaris->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inventaris berhasil diperbarui.'
            ]);
        }

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Inventaris berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $item = Inventaris::findOrFail($id);
        $item->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data inventaris berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.inventaris.index')
            ->with('success', 'Data inventaris berhasil dihapus.');
    }
}