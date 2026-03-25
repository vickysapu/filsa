<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Jemaat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class JemaatController extends Controller
{
    public function index(Request $request)
    {
        $query = Jemaat::query();

        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                    ->orWhere('NIK', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->jenis_kelamin !== null && $request->jenis_kelamin !== '') {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->status_pernikahan) {
            $query->where('status_pernikahan', $request->status_pernikahan);
        }

        if ($request->status_jemaat !== null && $request->status_jemaat !== '') {
            $query->where('status_jemaat', $request->status_jemaat);
        }

        if (!$branchId && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $jemaats = $query->latest()->paginate(15);

        if ($request->ajax()) {
            return view('features.jemaat._table', compact('jemaats'))->render();
        }

        return view('features.jemaat.index', compact('jemaats'));
    }

    public function create()
    {
        return view('features.jemaat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'NIK' => 'required|numeric|digits:16|unique:jemaats,NIK',
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|boolean',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date|before:today',
                'alamat' => 'required|string',
                'no_hp' => 'required|numeric',
                'email' => 'nullable|email|max:255|unique:jemaats,email',
                'status_pernikahan' => ['required', Rule::in(['belum menikah', 'menikah', 'janda', 'duda'])],
                'pekerjaan' => 'nullable|string|max:100',
                'pendidikan' => 'nullable|string|max:50',
                'tanggal_bergabung' => 'required|date',
                'status_jemaat' => 'boolean',
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            ],
            [
                'NIK.digits' => 'NIK harus 16 digit',
                'NIK.unique' => 'NIK sudah terdaftar',
                'no_hp.digits_between' => 'Nomor HP harus antara 10-15 digit',
                'tanggal_lahir.before' => 'Tanggal lahir harus di masa lalu',
            ],
        );

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('jemaat/foto', 'public');
        }

        Jemaat::create([
            'branch_id' => Auth::user()?->roles->first()?->branch_id,
            'NIK' => $validated['NIK'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'],
            'email' => $validated['email'] ?? null,
            'status_pernikahan' => $validated['status_pernikahan'],
            'pekerjaan' => $validated['pekerjaan'] ?? null,
            'pendidikan' => $validated['pendidikan'] ?? null,
            'tanggal_bergabung' => $validated['tanggal_bergabung'],
            'status_jemaat' => $validated['status_jemaat'] ?? true,
            'foto' => $fotoPath,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data jemaat berhasil disimpan',
            ]);
        }

        return redirect()->route('admin.jemaat.index')->with('success', 'Jemaat berhasil ditambahkan!');
    }

    public function show(Jemaat $jemaat, Request $request)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $jemaat->branch_id !== $branchId) {
            abort(403);
        }

        if ($request->ajax()) {
            return response()->json($jemaat);
        }

        return view('features.jemaat.show', compact('jemaat'));
    }

    public function edit(Jemaat $jemaat, Request $request)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $jemaat->branch_id !== $branchId) {
            abort(403);
        }

        if ($request->ajax()) {
            return response()->json($jemaat);
        }

        return view('features.jemaat.edit', compact('jemaat'));
    }

    public function update(Request $request, Jemaat $jemaat)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $jemaat->branch_id !== $branchId) {
            abort(403);
        }

        $validated = $request->validate(
            [
                'NIK' => 'sometimes|required|numeric|digits:16|unique:jemaats,NIK,' . $jemaat->id,
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|boolean',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date|before:today',
                'alamat' => 'required|string',
                'no_hp' => 'required|numeric|digits_between:10,15',
                'email' => 'nullable|email|max:255|unique:jemaats,email,' . $jemaat->id,
                'status_pernikahan' => ['required', Rule::in(['belum menikah', 'menikah', 'janda', 'duda'])],
                'pekerjaan' => 'nullable|string|max:100',
                'pendidikan' => 'nullable|string|max:50',
                'tanggal_bergabung' => 'required|date',
                'status_jemaat' => 'boolean',
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            ],
            [
                'NIK.digits' => 'NIK harus 16 digit',
                'NIK.unique' => 'NIK sudah terdaftar',
                'no_hp.digits_between' => 'Nomor HP harus antara 10-15 digit',
                'tanggal_lahir.before' => 'Tanggal lahir harus di masa lalu',
            ],
        );

        $fotoPath = $jemaat->foto;
        if ($request->hasFile('foto')) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('jemaat/foto', 'public');
        }

        $jemaat->update([
            'NIK' => $validated['NIK'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'],
            'email' => $validated['email'] ?? null,
            'status_pernikahan' => $validated['status_pernikahan'],
            'pekerjaan' => $validated['pekerjaan'] ?? null,
            'pendidikan' => $validated['pendidikan'] ?? null,
            'tanggal_bergabung' => $validated['tanggal_bergabung'],
            'status_jemaat' => $validated['status_jemaat'] ?? true,
            'foto' => $fotoPath,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data jemaat berhasil disimpan',
            ]);
        }

        return redirect()->route('admin.jemaat.index')->with('success', 'Data jemaat berhasil diupdate!');
    }

    public function destroy(Jemaat $jemaat)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $jemaat->branch_id !== $branchId) {
            abort(403);
        }

        $jemaat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data jemaat berhasil dihapus',
        ]);
    }

    public function export()
    {
        return back()->with('info', 'Fitur export akan segera hadir!');
    }
}