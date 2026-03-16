<?php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityTokenMiddleware;
use App\Http\Middleware\RequestFingerprintMiddleware;
use App\Http\Middleware\AuditLogMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ValidatePageToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'security.token' => \App\Http\Middleware\SecurityTokenMiddleware::class,
            'security.fingerprint' => \App\Http\Middleware\RequestFingerprintMiddleware::class,
            'security.audit' => \App\Http\Middleware\AuditLogMiddleware::class,
            
            'page.token' => ValidatePageToken::class,
        ]);
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => PermissionMiddleware::class, 
            'role' => RoleMiddleware::class,             
            
            // 🔐 Custom Security Middleware
            'security.token' => SecurityTokenMiddleware::class,
            'security.fingerprint' => RequestFingerprintMiddleware::class,
            'security.audit' => AuditLogMiddleware::class,
        ]);

        // Global rate limiting
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();