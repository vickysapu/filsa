<?php
// app/Http/Controllers/Admin/DataTableController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataTable;
use Illuminate\Http\Request;

class DataTableController extends Controller
{
    public function index(Request $request)
    {
        $query = DataTable::latest();

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('table_name', 'like', "%{$search}%");
        }

        $dataTables = $query->paginate(10);

        if ($request->ajax()) {
            return view('admin.dataTable._table', compact('dataTables'))->render();
        }

        return view('admin.dataTable.index', compact('dataTables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'table_name' => 'required|string|max:100|unique:data_tables,table_name',
            ],
            [
                'table_name.required' => 'Nama tabel wajib diisi.',
                'table_name.unique' => 'Nama tabel sudah digunakan.',
            ],
        );

        DataTable::create([
            'table_name' => $validated['table_name'],
            'status' => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Tabel berhasil ditambahkan!']);
        }

        return redirect()->route('admin.dataTable.index')->with('success', 'Tabel berhasil ditambahkan!');
    }

    public function update(Request $request, DataTable $dataTable)
    {
        $validated = $request->validate(
            [
                'table_name' => 'required|string|max:100|unique:data_tables,table_name,' . $dataTable->id,
            ],
            [
                'table_name.required' => 'Nama tabel wajib diisi.',
                'table_name.unique' => 'Nama tabel sudah digunakan.',
            ],
        );

        $dataTable->update(['table_name' => $validated['table_name']]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tabel berhasil diupdate!',
                'data' => $dataTable,
            ]);
        }

        return redirect()->route('admin.dataTable.index')->with('success', 'Tabel berhasil diupdate!');
    }

    public function toggleStatus(DataTable $dataTable)
    {
        $dataTable->update(['status' => !$dataTable->status]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $dataTable->status ? 'Tabel diaktifkan!' : 'Tabel dinonaktifkan!',
                'status' => $dataTable->status,
            ]);
        }

        return redirect()->route('admin.dataTable.index')->with('success', 'Status tabel berhasil diubah!');
    }

    public function destroy(DataTable $dataTable)
    {
        $dataTable->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Tabel berhasil dihapus!']);
        }

        return redirect()->route('admin.dataTable.index')->with('success', 'Tabel berhasil dihapus!');
    }
}
