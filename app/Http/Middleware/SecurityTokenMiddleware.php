<?php
// app/Http/Middleware/SecurityTokenMiddleware.php

namespace App\Http\Middleware;

use App\Models\SecurityToken;
use App\Models\User; // ✅ Import User
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityTokenMiddleware
{
    public function handle(Request $request, Closure $next, string $ability = null)
    {
        $token = $request->header('X-Security-Token') 
                    ?? $request->input('_token_secure') 
                    ?? $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Security token required'], 401);
        }

        $securityToken = SecurityToken::validateToken($token);

        if (!$securityToken) {
            return response()->json(['error' => 'Invalid or expired security token'], 401);
        }

        // Branch isolation untuk token
        if ($securityToken->branch_id && Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            
            if (!$user->isSuperAdmin() && $user->branch_id !== $securityToken->branch_id) {
                return response()->json(['error' => 'Token not authorized for this branch'], 403);
            }
        }

        // Cek ability/permission token
        if ($ability && $securityToken->abilities) {
            if (!in_array($ability, $securityToken->abilities) && !in_array('*', $securityToken->abilities)) {
                return response()->json(['error' => 'Token lacks required permission'], 403);
            }
        }

        // Update last_used
        $securityToken->markAsUsed();

        $request->attributes->set('security_token', $securityToken);

        return $next($request);
    }
}