<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiAccessToken;
use App\Services\Security\DataAccessSecurityService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class VerifyDataToken
{
    protected $securityService;

    public function __construct(DataAccessSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->jsonError('Missing or invalid authorization header', 401);
        }

        $providedToken = substr($authHeader, 7);
        $prefix = substr($providedToken, 0, 10);

        $apiToken = ApiAccessToken::where('token_prefix', $prefix)
            ->whereNull('revoked_at')
            ->with(['grant.request.grants'])
            ->first();

        if (!$apiToken) {
            return $this->jsonError('Invalid token', 401);
        }

        if (!$this->securityService->validateToken($providedToken, $apiToken->token_hash)) {
            return $this->jsonError('Invalid token', 401);
        }

        if ($apiToken->grant->isExpired()) {
            return $this->jsonError('Token has expired', 401);
        }

        if ($apiToken->grant->request->status !== 'approved') {
            return $this->jsonError('Access request not approved', 403);
        }

        if ($apiToken->allowed_ips && !$this->securityService->isIpAllowed($apiToken->allowed_ips, $request->ip())) {
            Log::warning('Data API: IP not allowed', ['ip' => $request->ip(), 'token_prefix' => $prefix]);
            return $this->jsonError('IP address not authorized', 403);
        }

        $rateLimitKey = 'data-api:' . $apiToken->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, $apiToken->grant->rate_limit_per_minute)) {
            return response()
                ->json(
                    [
                        'error' => 'Rate limit exceeded',
                        'retry_after' => RateLimiter::availableIn($rateLimitKey),
                    ],
                    429,
                )
                ->header('Retry-After', RateLimiter::availableIn($rateLimitKey));
        }
        RateLimiter::hit($rateLimitKey, 60);

        // Update last_used
        $apiToken->update(['last_used_at' => now()]);

        $request->attributes->set('validated_token', $apiToken);

        return $next($request);
    }

    protected function jsonError(string $message, int $status): Response
    {
        return response()
            ->json(['error' => $message], $status)
            ->header('Content-Type', 'application/json');
    }
}
