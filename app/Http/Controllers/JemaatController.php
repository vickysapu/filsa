<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Jemaat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class JemaatController extends Controller
{
    /**
     * Display a listing of jemaats with search and filter
     */
    public function index(Request $request)
    {
        $query = Jemaat::query();

        // Search Filter (Nama, NIK, Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                    ->orWhere('NIK', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter Jenis Kelamin
        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        // Filter Status Pernikahan
        if ($request->filled('status_pernikahan')) {
            $query->where('status_pernikahan', $request->status_pernikahan);
        }

        // Filter Status Jemaat
        if ($request->filled('status_jemaat')) {
            $query->where('status_jemaat', $request->status_jemaat);
        }

        // Order by terbaru
        $jemaats = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('features.jemaat.index', compact('jemaats'));
    }

    /**
     * Show the form for creating a new jemaat
     */
    public function create()
    {
        return view('features.jemaat.create');
    }

    /**
     * Store a newly created jemaat in storage
     */
    public function store(Request $request)
    {
        // Validasi Input
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
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048', // Max 2MB
            ],
            [
                'NIK.digits' => 'NIK harus 16 digit',
                'NIK.unique' => 'NIK sudah terdaftar',
                'no_hp.digits_between' => 'Nomor HP harus antara 10-15 digit',
                'tanggal_lahir.before' => 'Tanggal lahir harus di masa lalu',
            ],
        );

        // Handle Upload Foto
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('jemaat/foto', 'public');
        }

        // Create Jemaat
        Jemaat::create([
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

        return redirect()->route('admin.jemaat.index')->with('success', 'Jemaat berhasil ditambahkan!');
    }

    /**
     * Display the specified jemaat
     */
    public function show(Jemaat $jemaat)
    {
        return view('features.jemaat.show', compact('jemaat'));
    }

    /**
     * Show the form for editing the specified jemaat
     */
    public function edit(Jemaat $jemaat)
    {
        return view('features.jemaat.edit', compact('jemaat'));
    }

    /**
     * Update the specified jemaat in storage
     */
    public function update(Request $request, Jemaat $jemaat)
    {
        // Validasi Input
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
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048', // Max 2MB
            ],
            [
                'NIK.digits' => 'NIK harus 16 digit',
                'NIK.unique' => 'NIK sudah terdaftar',
                'no_hp.digits_between' => 'Nomor HP harus antara 10-15 digit',
                'tanggal_lahir.before' => 'Tanggal lahir harus di masa lalu',
            ],
        );

        // Handle Upload Foto Baru
        $fotoPath = $jemaat->foto; // Keep existing photo
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            // Upload new photo
            $fotoPath = $request->file('foto')->store('jemaat/foto', 'public');
        }

        // Update Jemaat
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

        return redirect()->route('admin.jemaat.index')->with('success', 'Data jemaat berhasil diupdate!');
    }

    /**
     * Remove the specified jemaat from storage
     */
    public function destroy(Jemaat $jemaat)
    {
        if ($jemaat->foto) {
            Storage::disk('public')->delete($jemaat->foto);
        }

        $jemaat->delete();

        return redirect()->admin('features.jemaat.index')->with('success', 'Data jemaat berhasil dihapus!');
    }

    public function export()
    {
        return back()->with('info', 'Fitur export akan segera hadir!');
    }
}
