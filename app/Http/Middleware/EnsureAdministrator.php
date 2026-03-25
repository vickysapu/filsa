<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class EnsureAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('administrator') || !$user->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Administrator access required.'], 403);
            }
            abort(403, 'Akses ditolak. Hanya administrator yang diizinkan.');
        }

        $this->logAdminActivity($user, $request);

        return $next($request);
    }

    protected function logAdminActivity(User $user, Request $request): void
    {
        Log::channel('audit')->info('Administrator data access', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}