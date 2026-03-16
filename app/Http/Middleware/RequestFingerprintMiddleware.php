<?php
// app/Http/Middleware/RequestFingerprintMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ✅ Import Log
use App\Models\User;

class RequestFingerprintMiddleware
{
    protected $maxAttempts = 60;
    protected $decaySeconds = 60;

    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User|null $user */ // ✅ Fix warning
        $user = Auth::user();
        
        $fingerprint = $this->generateFingerprint($request);
        $key = "fingerprint:{$fingerprint}";

        // Rate limiting per fingerprint
        if (Cache::get($key, 0) >= $this->maxAttempts) {
            return response()->json(['error' => 'Too many requests from this fingerprint'], 429);
        }

        Cache::increment($key, 1, $this->decaySeconds);

        // Optional: Check for suspicious user agent changes
        if ($user && $request->session()->has('last_user_agent')) {
            $lastAgent = $request->session()->get('last_user_agent');
            $currentAgent = $request->userAgent();
            
            if ($lastAgent && $this->isSuspiciousAgentChange($lastAgent, $currentAgent)) {
                // ✅ Ganti activity() dengan Log + LoginActivities (jika ingin simpan ke DB)
                Log::warning('Suspicious user agent change detected', [
                    'user_id' => $user->id,
                    'old_agent' => $lastAgent,
                    'new_agent' => $currentAgent,
                    'ip' => $request->ip(),
                ]);
                
                // ✅ Opsional: Simpan ke tabel LoginActivities yang sudah ada
                // \App\Models\LoginActivities::create([
                //     'user_id' => $user->id,
                //     'ip_address' => $request->ip(),
                //     'user_agent' => $currentAgent,
                //     'success' => false,
                //     'failure_reason' => 'suspicious_agent_change',
                // ]);
            }
        }

        $request->session()->put('last_user_agent', $request->userAgent());

        return $next($request);
    }

    protected function generateFingerprint(Request $request): string
    {
        $parts = [
            $request->ip(),
            $request->userAgent() ?? '',
            $request->header('Accept-Language') ?? '',
            Auth::id() ?? 'guest',
        ];
        return hash('sha256', implode('|', $parts));
    }

    protected function isSuspiciousAgentChange(string $old, string $new): bool
    {
        $oldEngine = $this->extractEngine($old);
        $newEngine = $this->extractEngine($new);
        return $oldEngine && $newEngine && $oldEngine !== $newEngine;
    }

    protected function extractEngine(string $userAgent): ?string
    {
        if (str_contains($userAgent, 'Chrome')) return 'chrome';
        if (str_contains($userAgent, 'Firefox')) return 'firefox';
        if (str_contains($userAgent, 'Safari')) return 'safari';
        if (str_contains($userAgent, 'Edge')) return 'edge';
        return null;
    }
}