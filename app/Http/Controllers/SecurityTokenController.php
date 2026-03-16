<?php
// app/Http/Controllers/SecurityTokenController.php

namespace App\Http\Controllers;

use App\Models\SecurityToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ✅ Ganti activity() dengan Log
use Illuminate\Validation\ValidationException;

class SecurityTokenController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $currentUser */ // ✅ Fix warning isSuperAdmin
        $currentUser = Auth::user();
        
        $tokens = SecurityToken::when(!$currentUser->isSuperAdmin(), function ($q) use ($currentUser) {
                $q->where('branch_id', $currentUser->branch_id);
            })
            ->with('branch')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.security.tokens.index', compact('tokens'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'abilities' => 'array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Hanya super admin yang bisa buat token untuk branch lain
        if (!$currentUser->isSuperAdmin() && $validated['branch_id']) {
            if ($validated['branch_id'] !== $currentUser->branch_id) {
                throw ValidationException::withMessages([
                    'branch_id' => 'Anda hanya bisa membuat token untuk branch Anda sendiri.'
                ]);
            }
        }

        // Generate token
        $tokenData = SecurityToken::generateToken('sk');
        
        $token = SecurityToken::create([
            'branch_id' => $validated['branch_id'] ?? $currentUser->branch_id,
            'name' => $validated['name'],
            'token' => $tokenData['hashed'],
            'prefix' => $tokenData['prefix'],
            'abilities' => $validated['abilities'] ?? ['*'],
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => $currentUser->id,
            'ip_address' => $request->ip(),
        ]);

        // ✅ Ganti activity() dengan Log standar Laravel
        Log::info('Security token created', [
            'token_id' => $token->id,
            'token_name' => $token->name,
            'created_by' => $currentUser->id,
            'branch_id' => $token->branch_id,
        ]);

        // ⚠️ TAMPILKAN PLAIN TOKEN HANYA SEKALI!
        return redirect()->route('admin.security.tokens.index')
            ->with('success', 'Token berhasil dibuat!')
            ->with('new_token_plain', $tokenData['plain']);
    }

    public function revoke(SecurityToken $token)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        // Isolasi: tidak bisa revoke token branch lain
        if (!$currentUser->isSuperAdmin() && $token->branch_id !== $currentUser->branch_id) {
            abort(403);
        }

        $token->revoke();
        
        // ✅ Ganti activity() dengan Log
        Log::warning('Security token revoked', [
            'token_id' => $token->id, 
            'token_name' => $token->name,
            'revoked_by' => $currentUser->id,
        ]);

        return back()->with('success', 'Token telah dinonaktifkan.');
    }

    public function rotate(SecurityToken $token)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        // Isolasi
        if (!$currentUser->isSuperAdmin() && $token->branch_id !== $currentUser->branch_id) {
            abort(403);
        }

        // Revoke token lama
        $oldPrefix = $token->prefix;
        $token->revoke();

        // Buat token baru dengan data sama
        $tokenData = SecurityToken::generateToken('sk');
        
        $newToken = SecurityToken::create([
            'branch_id' => $token->branch_id,
            'name' => $token->name . ' (Rotated)',
            'token' => $tokenData['hashed'],
            'prefix' => $tokenData['prefix'],
            'abilities' => $token->abilities,
            'expires_at' => $token->expires_at,
            'created_by' => $currentUser->id,
            'ip_address' => request()->ip(),
        ]);

        // ✅ Ganti activity() dengan Log
        Log::info('Security token rotated', [
            'old_token_prefix' => $oldPrefix,
            'new_token_prefix' => $newToken->prefix,
            'token_name' => $token->name,
            'rotated_by' => $currentUser->id,
        ]);

        return redirect()->route('admin.security.tokens.index')
            ->with('success', 'Token berhasil di-rotate!')
            ->with('new_token_plain', $tokenData['plain']);
    }
}