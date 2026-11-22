<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Jemaat;
use App\Models\User;
use App\Notifications\UnifiedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JemaatController extends Controller
{
    protected function isAdministrator()
    {
        $user = Auth::user();

        return $user && $user->hasRole('administrator');
    }

    protected function isSuperAdmin()
    {
        return $this->isAdministrator();
    }

    protected function getBranchId()
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return $user->roles->pluck('branch_id')->filter()->first() ?? $user->branch_id;
    }
    
    protected function authorizeJemaat(Jemaat $jemaat)
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        $branchId = $this->getBranchId();

        if (!$branchId || (int) $jemaat->branch_id !== (int) $branchId) {
            abort(403, 'Akses ditolak');
        }
    }

    protected function wantsJsonResponse(Request $request)
    {
        return $request->ajax() || $request->wantsJson();
    }

    public function index(Request $request)
    {
        $isSuperAdmin = $this->isSuperAdmin();
        $branchId = $this->getBranchId();

        $query = Jemaat::query();

        if ($isSuperAdmin) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            if (!$branchId) {
                abort(403, 'Akses ditolak');
            }
            $query->where('branch_id', $branchId);
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

        if ($request->filled('status_pernikahan')) {
            $query->where('status_pernikahan', $request->status_pernikahan);
        }

        if ($request->filled('status_jemaat')) {
            $query->where('status_jemaat', (bool) $request->status_jemaat);
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $jemaats = $query->latest()->paginate($perPage)->withQueryString();

        $branches = $isSuperAdmin ? Branch::all() : collect();
        $defaultBranchId = $branchId;

        return view('features.jemaat.index', compact('jemaats', 'branches', 'isSuperAdmin', 'defaultBranchId'));
    }

    public function apiIndex(Request $request)
    {
        $branchId = $this->getBranchId();
        $isSuperAdmin = $this->isSuperAdmin();

        $query = Jemaat::select('id', 'nama_lengkap', 'no_hp', 'email', 'branch_id');

        if ($isSuperAdmin) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            if (!$branchId) {
                return response()->json([], 403);
            }
            $query->where('branch_id', $branchId);
        }

        return response()->json($query->orderBy('nama_lengkap')->get());
    }

    public function apiShow(Jemaat $jemaat)
    {
        $this->authorizeJemaat($jemaat);

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

            $rules = [
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:0,1',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date|before:today',
                'alamat' => 'required|string|max:500',
                'no_hp' => 'required|string|regex:/^[0-9\+\-\s\(\)]{10,20}$/',
                'email' => 'nullable|email|max:255|unique:jemaats,email',
                'status_pernikahan' => ['required', Rule::in(['belum menikah', 'menikah', 'janda', 'duda'])],
                'pekerjaan' => 'nullable|string|max:100',
                'pendidikan' => 'nullable|string|max:50',
                'status_jemaat' => 'nullable|boolean',
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            ];

            if ($isSuperAdmin) {
                $rules['branch_id'] = 'required|exists:branches,id';
            }

            $validated = $request->validate($rules, [
                'no_hp.regex' => 'Format nomor HP tidak valid',
                'email.unique' => 'Email sudah terdaftar dalam sistem',
                'tanggal_lahir.before' => 'Tanggal lahir harus di masa lalu',
            ]);

            $branchId = $isSuperAdmin ? $validated['branch_id'] : $this->getBranchId();

            if (!$branchId) {
                abort(403, 'Akun Anda belum terhubung ke cabang');
            }

            $fotoPath = null;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $fotoPath = $request->file('foto')->store('jemaat/foto', 'public');
            }

            $jemaat = Jemaat::create([
                'branch_id' => $branchId,
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
                'status_jemaat' => $validated['status_jemaat'] ?? true,
                'foto' => $fotoPath,
            ]);

            $this->sendNotificationToAuthorizedUsers(
                $branchId,
                'Data Jemaat Baru',
                "Jemaat atas nama {$jemaat->nama_lengkap} telah ditambahkan.",
                route('admin.jemaat.index'),
                'ri-user-add-line',
                'text-success',
                'JEMAAT'
            );

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data jemaat berhasil disimpan!',
                ], 201);
            }

            return redirect()->route('admin.jemaat.index')->with('success', 'Jemaat berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Jemaat Store Error: ' . $e->getMessage());
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                ], 500);
            }
            return back()->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
    }

    public function show(Jemaat $jemaat)
    {
        $this->authorizeJemaat($jemaat);

        return view('features.jemaat.show', compact('jemaat'));
    }

    public function edit(Jemaat $jemaat)
    {
        $this->authorizeJemaat($jemaat);

        $isSuperAdmin = $this->isSuperAdmin();
        $branches = $isSuperAdmin ? Branch::all() : collect();
        $defaultBranchId = $this->getBranchId();

        return view('features.jemaat.form', compact('jemaat', 'branches', 'isSuperAdmin', 'defaultBranchId'));
    }

    public function update(Request $request, Jemaat $jemaat)
    {
        $this->authorizeJemaat($jemaat);

        $isSuperAdmin = $this->isSuperAdmin();

        try {
            $rules = [
                'nama_lengkap' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:0,1',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date|before:today',
                'alamat' => 'required|string|max:500',
                'no_hp' => 'required|string|regex:/^[0-9\+\-\s\(\)]{10,20}$/',
                'email' => 'nullable|email|max:255|unique:jemaats,email,' . $jemaat->id,
                'status_pernikahan' => ['required', Rule::in(['belum menikah', 'menikah', 'janda', 'duda'])],
                'pekerjaan' => 'nullable|string|max:100',
                'pendidikan' => 'nullable|string|max:50',
                'status_jemaat' => 'nullable|boolean',
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            ];

            if ($isSuperAdmin) {
                $rules['branch_id'] = 'required|exists:branches,id';
            }

            $validated = $request->validate($rules, [
                'no_hp.regex' => 'Format nomor HP tidak valid',
                'email.unique' => 'Email sudah terdaftar dalam sistem',
                'tanggal_lahir.before' => 'Tanggal lahir harus di masa lalu',
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
                'status_pernikahan' => $validated['status_pernikahan'],
                'pekerjaan' => $validated['pekerjaan'] ?? null,
                'pendidikan' => $validated['pendidikan'] ?? null,
                'status_jemaat' => $validated['status_jemaat'] ?? true,
                'foto' => $fotoPath,
            ];

            if ($isSuperAdmin && isset($validated['branch_id'])) {
                $updateData['branch_id'] = $validated['branch_id'];
            }

            $jemaat->update($updateData);

            $this->sendNotificationToAuthorizedUsers(
                $jemaat->branch_id,
                'Data Jemaat Diperbarui',
                "Data jemaat atas nama {$jemaat->nama_lengkap} telah diperbarui.",
                route('admin.jemaat.index'),
                'ri-edit-line',
                'text-warning',
                'JEMAAT'
            );

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data jemaat berhasil diperbarui!',
                    'data' => $jemaat,
                ]);
            }

            return redirect()->route('admin.jemaat.index')->with('success', 'Data jemaat berhasil diupdate!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Jemaat Update Error: ' . $e->getMessage());
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem.',
                ], 500);
            }
            return back()->with('error', 'Gagal mengupdate data.');
        }
    }

    public function destroy(Request $request, Jemaat $jemaat)
    {
        $this->authorizeJemaat($jemaat);

        try {
            $nama = $jemaat->nama_lengkap;
            $branchId = $jemaat->branch_id;

            if ($jemaat->foto && Storage::disk('public')->exists($jemaat->foto)) {
                Storage::disk('public')->delete($jemaat->foto);
            }

            $jemaat->delete();

            $this->sendNotificationToAuthorizedUsers(
                $branchId,
                'Data Jemaat Dihapus',
                "Data jemaat atas nama {$nama} telah dihapus.",
                route('admin.jemaat.index'),
                'ri-delete-bin-line',
                'text-danger',
                'JEMAAT'
            );

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data jemaat berhasil dihapus!',
                ]);
            }

            return redirect()->route('admin.jemaat.index')->with('success', 'Data jemaat berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Jemaat Delete Error: ' . $e->getMessage());
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data jemaat.',
                ], 500);
            }
            return back()->with('error', 'Gagal menghapus data.');
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
                $q->where('branch_id', $branchId)
                    ->orWhereHas('roles', function ($r) {
                        $r->where('name', 'administrator')
                            ->orWhere('name', 'Super Admin');
                    });
            });
        }

        $authorizedUsers = $query->get()->filter(function ($user) {
            return $user->hasPermission('jemaat.view');
        });

        if ($authorizedUsers->isNotEmpty()) {
            Notification::send(
                $authorizedUsers,
                new UnifiedNotification($title, $message, $url, $icon, $color, $source)
            );
        }
    }
}