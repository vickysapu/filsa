<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiAccessToken;
use App\Services\Security\DataAccessSecurityService;

class VerifyPortalToken
{
    public function __construct(
        protected DataAccessSecurityService $securityService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $portalToken = session('portal_token');
        
        if (!$portalToken) {
            return redirect()->route('data-portal.verify')
                ->with('error', 'Silakan verifikasi token terlebih dahulu.');
        }
        
        $apiToken = ApiAccessToken::with(['grant.request'])
            ->where('token_prefix', substr($portalToken, 0, 10))
            ->first();
        
        if (!$apiToken || !$this->securityService->validateToken($portalToken, $apiToken->token_hash)) {
            session()->forget('portal_token');
            return redirect()->route('data-portal.verify')
                ->with('error', 'Token tidak valid atau sudah kedaluwarsa.');
        }
        
        if ($apiToken->isRevoked() || $apiToken->grant->isExpired()) {
            session()->forget('portal_token');
            return redirect()->route('data-portal.verify')
                ->with('error', 'Token sudah kedaluwarsa atau dicabut.');
        }
        
        $request->attributes->set('portal_api_token', $apiToken);
        $request->attributes->set('portal_branch_id', $apiToken->grant->request->branch_id);
        
        return $next($request);
    }
}