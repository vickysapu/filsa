<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivities;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $maxAttempts = 5;
    protected $decayMinutes = 15;

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Rate Limiting
        $this->checkRateLimit($request);

        $credentials = $request->validate([
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:8',
            'remember' => 'boolean',
        ]);

        $user = User::with('branch', 'roles')->where('email', $credentials['email'])->first();

        $this->logLoginAttempt($request, $user, false);

        if (!$user) {
            $this->incrementFailedAttempts($request);
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        if (!$user->is_active) {
            $this->logLoginAttempt($request, $user, false, 'account_disabled');
            $this->incrementFailedAttempts($request);
            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ]);
        }

        if ($user->branch && 
            $user->branch->getConnection()->getSchemaBuilder()->hasColumn('branches', 'is_active') && 
            !$user->branch->is_active) {
            $this->logLoginAttempt($request, $user, false, 'branch_disabled');
            throw ValidationException::withMessages([
                'email' => 'Cabang Anda sedang dinonaktifkan. Hubungi administrator pusat.',
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            $this->logLoginAttempt($request, $user, false, 'invalid_password');
            $this->incrementFailedAttempts($request);
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        if ($user->roles->isEmpty()) {
            $this->logLoginAttempt($request, $user, false, 'no_role_assigned');
            throw ValidationException::withMessages([
                'email' => 'Akun Anda belum memiliki role. Hubungi administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        Auth::login($user, $request->boolean('remember'));

        $user->update(['last_login_at' => now()]);

        $this->logLoginAttempt($request, $user, true);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah logout.');
    }

    protected function checkRateLimit(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            
            $this->logLoginAttempt($request, null, false, 'rate_limited');
            
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }
    }

    protected function incrementFailedAttempts(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);
    }

    protected function throttleKey(Request $request): string
    {
        return 'login|' . $request->ip() . '|' . $request->input('email');
    }

    protected function logLoginAttempt(Request $request, ?User $user, bool $success, ?string $failureReason = null): void
    {
        LoginActivities::create([
            'branch_id' => $user?->branch_id,
            'user_id' => $user?->id,
            'email_attempt' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => $success,
            'failure_reason' => $failureReason,
        ]);
    }
}