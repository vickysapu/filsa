<?php
// app/Http/Middleware/AuditLogMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogMiddleware
{
    protected $sensitiveRoutes = [
        'admin.users.destroy',
        'admin.roles.destroy',
        'admin.permissions.destroy',
        'admin.pemasukan.verify',
        'admin.pengeluaran.verify',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log sensitive operations
        if (in_array($request->route()->getName(), $this->sensitiveRoutes)) {
            $this->logAudit($request, $response);
        }

        return $response;
    }

    protected function logAudit(Request $request, $response): void
    {
        $user = Auth::user();
        $logData = [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'branch_id' => $user?->branch_id,
            'route' => $request->route()->getName(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $this->sanitizePayload($request->all()),
            'response_status' => $response->status(),
            'timestamp' => now()->toDateTimeString(),
        ];

        // Log to file + database (implementasi sesuai kebutuhan)
        Log::channel('audit')->info('Sensitive action performed', $logData);
        
        // Optional: Save to database table 'audit_logs'
        // AuditLog::create($logData);
    }

    protected function sanitizePayload(array $payload): array
    {
        $sensitive = ['password', 'password_confirmation', 'token', 'api_key'];
        foreach ($sensitive as $key) {
            if (isset($payload[$key])) {
                $payload[$key] = '***REDACTED***';
            }
        }
        return $payload;
    }
}