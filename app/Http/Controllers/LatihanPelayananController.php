<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LatihanPelayanan;
use App\Models\Pelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LatihanPelayananController extends Controller
{
    public function index(Request $request)
    {
        $query = LatihanPelayanan::with(['pelayanan', 'creator']);

        if ($request->filled('tanggal_start')) {
            $query->where('tanggal', '>=', $request->tanggal_start);
        }
        if ($request->filled('tanggal_end')) {
            $query->where('tanggal', '<=', $request->tanggal_end);
        }
        if ($request->filled('pelayanan_id')) {
            $query->where('pelayanan_id', $request->pelayanan_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_latihan', 'like', "%{$search}%")
                  ->orWhereHas('pelayanan', fn($q) => $q->where('nama_pelayanan', 'like', "%{$search}%"));
            });
        }

        $latihans = $query->latest()->paginate(12);

        $pelayanans = Pelayanan::where('status', 'active')->get();

        if ($request->ajax()) {
            return view('features.latihan-pelayanan._list', compact('latihans'));
        }

        return view('features.latihan-pelayanan.index', compact('latihans', 'pelayanans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelayanan_id' => 'required|exists:pelayanans,id',
            'nama_latihan' => 'required|string|max:255',
            'tanggal'      => 'required|date',
            'waktu'        => 'required',
            'lokasi'       => 'nullable|string|max:255',
            'keterangan'   => 'nullable|string',
            'status'       => 'in:draft,active,completed,cancelled',
        ]);

        LatihanPelayanan::create([
            'pelayanan_id' => $request->pelayanan_id,
            'nama_latihan' => $request->nama_latihan,
            'tanggal'      => $request->tanggal,
            'waktu'        => $request->waktu,
            'lokasi'       => $request->lokasi ?? 'Gedung Utama',
            'keterangan'   => $request->keterangan,
            'status'       => $request->status ?? 'active',
            'created_by'   => Auth::id(),
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Jadwal latihan pelayanan berhasil dibuat.'
        ]);
    }

    public function edit(LatihanPelayanan $latihan)
    {
        $pelayanans = Pelayanan::where('status', 'active')->get();

        // Format tanggal dan waktu secara eksplisit untuk input HTML
        $latihanData = $latihan->toArray();
        $latihanData['tanggal'] = $latihan->tanggal 
            ? \Carbon\Carbon::parse($latihan->tanggal)->format('Y-m-d') 
            : '';

        $latihanData['waktu'] = $latihan->waktu 
            ? \Carbon\Carbon::parse($latihan->waktu)->format('H:i') 
            : '';

        return response()->json([
            'success'    => true,
            'latihan'    => $latihanData,   // kirim array yang sudah diformat
            'pelayanans' => $pelayanans
        ]);
    }

    public function update(Request $request, LatihanPelayanan $latihan)
    {
        $request->validate([
            'pelayanan_id' => 'required|exists:pelayanans,id',
            'nama_latihan' => 'required|string|max:255',
            'tanggal'      => 'required|date',
            'waktu'        => 'required',
            'lokasi'       => 'nullable|string|max:255',
            'keterangan'   => 'nullable|string',
            'status'       => 'in:draft,active,completed,cancelled',
        ]);

        $latihan->update($request->only([
            'pelayanan_id', 'nama_latihan', 'tanggal', 'waktu', 
            'lokasi', 'keterangan', 'status'
        ]));

        return response()->json([
            'success' => true, 
            'message' => 'Jadwal latihan berhasil diperbarui.'
        ]);
    }

    public function destroy(LatihanPelayanan $latihan)
    {
        $latihan->delete();

        return response()->json([
            'success' => true, 
            'message' => 'Jadwal latihan berhasil dihapus.'
        ]);
    }
}