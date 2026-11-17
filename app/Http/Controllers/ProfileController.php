<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Tampilkan form profil user
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update data profil (nama, email) - SATU FUNGSI UTAMA
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate(
            [
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            ],
            [
                'name.required' => 'Nama lengkap wajib diisi',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan oleh akun lain',
            ],
        );

        // Catat perubahan untuk logging (opsional, sesuai preferensi user)
        $changes = [];
        if ($user->name !== $validated['name']) {
            $changes['name'] = ['old' => $user->name, 'new' => $validated['name']];
        }
        if ($user->email !== $validated['email']) {
            $changes['email'] = ['old' => $user->email, 'new' => $validated['email']];
        }

        // Update data
        $user->update($validated);

        // Response JSON untuk AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui!',
                'changes' => $changes, // Untuk referensi logging/debug
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'updated_at' => $user->updated_at?->diffForHumans(),
                ],
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update password user - terpisah agar validasi lebih jelas
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate(
            [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'password_confirmation' => ['required'],
            ],
            [
                'current_password.required' => 'Password saat ini wajib diisi',
                'current_password.current_password' => 'Password saat ini tidak sesuai',
                'password.required' => 'Password baru wajib diisi',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ],
        );

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['password' => Hash::make($validated['password'])]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah!',
            ]);
        }

        return back()->with('success', 'Password berhasil diubah!');
    }
}
