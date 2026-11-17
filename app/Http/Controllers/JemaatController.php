<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Jemaat;
use App\Models\User;
use App\Notifications\UnifiedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Notification;

class JemaatController extends Controller
{
    protected function getBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    protected function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && ($user->hasRole('administrator') || ($user->branch && $user->branch->is_headquarter));
    }

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $user = Auth::user();
        $branchId = $user?->branch_id ?? $user?->roles->pluck('branch_id')->filter()->first();

        $query = Jemaat::query();

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', (int) $request->jenis_kelamin);
        }

        if ($request->filled('status_jemaat')) {
            $query->where('status_jemaat', (bool) $request->status_jemaat);
        }

        $jemaats = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('features.jemaat._table', compact('jemaats', 'isSuperAdmin'))->render();
        }

        $branches = $isSuperAdmin ? Branch::all() : collect();

        $defaultBranchId = Auth::user()?->roles->first()?->branch_id;

        return view('features.jemaat.index', compact('jemaats', 'branches', 'isSuperAdmin', 'defaultBranchId'));
    }

    public function apiIndex(Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        $query = Jemaat::select('id', 'nama_lengkap', 'no_hp', 'email', 'branch_id');

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id') && $isSuperAdmin) {
            $query->where('branch_id', $request->branch_id);
        }

        $jemaats = $query->orderBy('nama_lengkap')->get();

        return response()->json($jemaats);
    }

    public function apiShow(Jemaat $jemaat, Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $jemaat->branch_id !== $branchId) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $jemaat->load(['branch']);

        return response()->json($jemaat);
    }

    public function create()
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $branches = $isSuperAdmin ? Branch::all() : collect();
        $defaultBranchId = $this->getBranchId();
        $jemaat = null;

        return view('features.jemaat.form', compact('branches', 'isSuperAdmin', 'defaultBranchId', 'jemaat'));
    }

    public function store(Request $request)
    {
        try {
            $isSuperAdmin = $this->isSuperAdmin();

            $validationRules = [
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:0,1',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date|before:today',
                'alamat' => 'required|string|max:500',
                'no_hp' => 'required|string|regex:/^[0-9\+\-\s\(\)]{10,20}$/',
                'email' => 'nullable|email|max:255|unique:jemaats,email',
                'pekerjaan' => 'nullable|string|max:100',
                'pendidikan' => 'nullable|string|max:50',
                'status_jemaat' => 'nullable|boolean',
                'foto' => 'nullable|image|mimes:jpg,png,jpeg',
            ];

            if ($isSuperAdmin) {
                $validationRules['branch_id'] = 'required|exists:branches,id';
            }

            $validated = $request->validate($validationRules, [
                'no_hp.regex' => 'Format nomor HP tidak valid',
                'tanggal_lahir.before' => 'Tanggal lahir harus di masa lalu',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $fotoPath = $request->file('foto')->store('jemaat/foto', 'public');
            }

            $branchId = $isSuperAdmin ? $validated['branch_id'] : $this->getBranchId();

            $jemaat = Jemaat::create([
                'branch_id' => $branchId,
                'nama_lengkap' => $validated['nama_lengkap'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'alamat' => $validated['alamat'],
                'no_hp' => $validated['no_hp'],
                'email' => $validated['email'] ?? null,
                'pekerjaan' => $validated['pekerjaan'] ?? null,
                'pendidikan' => $validated['pendidikan'] ?? null,
                'status_jemaat' => $validated['status_jemaat'] ?? true,
                'foto' => $fotoPath,
            ]);

            $this->sendNotificationToAuthorizedUsers($branchId, 'Data Jemaat Baru', "Jemaat atas nama {$jemaat->nama_lengkap} telah ditambahkan.", route('admin.jemaat.index'), 'ri-user-add-line', 'text-success', 'JEMAAT');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        'success' => true,
                        'message' => '✅ Data jemaat berhasil disimpan!',
                    ],
                    201,
                );
            }

            return redirect()->route('admin.jemaat.index')->with('success', 'Jemaat berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $e->errors(),
                    ],
                    422,
                );
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Jemaat Store Error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                    ],
                    500,
                );
            }
            return back()->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
    }

    public function show(Jemaat $jemaat)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $jemaat->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        return view('features.jemaat.show', compact('jemaat'));
    }

    public function edit(Jemaat $jemaat)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $jemaat->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        $branches = $isSuperAdmin ? Branch::all() : collect();

        return view('features.jemaat.form', compact('jemaat', 'branches', 'isSuperAdmin'));
    }

    public function update(Request $request, Jemaat $jemaat)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        if (!$isSuperAdmin && $branchId && $jemaat->branch_id !== $branchId) {
            abort(403, 'Akses ditolak');
        }

        try {
            $validationRules = [
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:0,1',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date|before:today',
                'alamat' => 'required|string|max:500',
                'no_hp' => 'required|string|regex:/^[0-9\+\-\s\(\)]{10,20}$/',
                'email' => 'nullable|email|max:255|unique:jemaats,email,' . $jemaat->id,
                'pekerjaan' => 'nullable|string|max:100',
                'pendidikan' => 'nullable|string|max:50',
                'status_jemaat' => 'nullable|boolean',
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            ];

            if ($isSuperAdmin) {
                $validationRules['branch_id'] = 'required|exists:branches,id';
            }

            $validated = $request->validate($validationRules, [
                'no_hp.regex' => 'Format nomor HP tidak valid',
            ]);

            $fotoPath = $jemaat->foto;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }
                $fotoPath = $request->file('foto')->store('jemaat/foto', 'public');
            }

            $updateData = [
                'nama_lengkap' => $validated['nama_lengkap'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'alamat' => $validated['alamat'],
                'no_hp' => $validated['no_hp'],
                'email' => $validated['email'] ?? null,
                'pekerjaan' => $validated['pekerjaan'] ?? null,
                'pendidikan' => $validated['pendidikan'] ?? null,
                'status_jemaat' => $validated['status_jemaat'] ?? true,
                'foto' => $fotoPath,
            ];

            if ($isSuperAdmin && isset($validated['branch_id'])) {
                $updateData['branch_id'] = $validated['branch_id'];
            }

            $jemaat->update($updateData);

            $targetBranchId = $isSuperAdmin && isset($validated['branch_id']) ? $validated['branch_id'] : $jemaat->branch_id;

            $this->sendNotificationToAuthorizedUsers($targetBranchId, 'Data Jemaat Diperbarui', "Data jemaat atas nama {$jemaat->nama_lengkap} telah diperbarui.", route('admin.jemaat.index'), 'ri-edit-line', 'text-warning', 'JEMAAT');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '✅ Data jemaat berhasil diperbarui!',
                    'data' => $jemaat,
                ]);
            }

            return redirect()->route('admin.jemaat.index')->with('success', 'Data jemaat berhasil diupdate!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $e->errors(),
                    ],
                    422,
                );
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Jemaat Update Error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Terjadi kesalahan sistem.',
                    ],
                    500,
                );
            }
            return back()->with('error', 'Gagal mengupdate data.');
        }
    }

    public function export()
    {
        return back()->with('info', 'Fitur export akan segera hadir!');
    }

    private function sendNotificationToAuthorizedUsers($branchId, $title, $message, $url, $icon, $color, $source)
    {
        $query = User::where('is_active', true)->with('roles.permissions');

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereHas('roles', function ($r) {
                    $r->where('name', 'administrator')->orWhere('name', 'Super Admin');
                });
            });
        }

        $users = $query->get();

        $authorizedUsers = $users->filter(function ($user) {
            return $user->hasPermission('jemaat.view');
        });

        if ($authorizedUsers->isNotEmpty()) {
            Notification::send($authorizedUsers, new UnifiedNotification($title, $message, $url, $icon, $color, $source));
        }
    }
}
