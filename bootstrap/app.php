<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityTokenMiddleware;
use App\Http\Middleware\RequestFingerprintMiddleware;
use App\Http\Middleware\AuditLogMiddleware;
use App\Http\Middleware\EnsureAdministrator;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ValidatePageToken;
use App\Http\Middleware\VerifyPortalToken;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        channels: __DIR__ . '/../routes/channels.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission'           => PermissionMiddleware::class,
            'role'                 => RoleMiddleware::class,
            'security.token'       => SecurityTokenMiddleware::class,
            'security.fingerprint' => RequestFingerprintMiddleware::class,
            'security.audit'       => AuditLogMiddleware::class,
            'page.token'           => ValidatePageToken::class,
            'ensure.administrator' => EnsureAdministrator::class,
            'verify.portal.token'  => VerifyPortalToken::class,
        ]);

        $middleware->api(prepend: [HandleCors::class]);
        $middleware->statefulApi();
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
    })
    ->create();