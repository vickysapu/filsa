<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDataPermission
{
    public function handle(Request $request, Closure $next, string $permissionType = 'read'): Response
    {
        /** @var \App\Models\ApiAccessToken|null $apiToken */
        $apiToken = $request->attributes->get('validated_token');

        if (!$apiToken) {
            return response()->json(['error' => 'Token not validated'], 401);
        }

        $grant = $apiToken->grant;
        $permissionMap = [
            'read' => true, 
            'write' => $grant->allow_create || $grant->allow_update || $grant->allow_delete,
            'create' => $grant->allow_create,
            'update' => $grant->allow_update,
            'delete' => $grant->allow_delete,
        ];

        if (!($permissionMap[$permissionType] ?? false)) {
            return response()->json(['error' => "Permission '{$permissionType}' not granted"], 403);
        }

        return $next($request);
    }
}