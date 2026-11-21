<?php

use Illuminate\Support\Facades\Route;

$publicDomain = config('domains.public');
$adminDomain = config('domains.admin');

if (($publicDomain === null) !== ($adminDomain === null)) {
    throw new LogicException('Set both PUBLIC_DOMAIN and ADMIN_DOMAIN, or leave both empty.');
}

if ($publicDomain !== null && strcasecmp($publicDomain, $adminDomain) === 0) {
    throw new LogicException('PUBLIC_DOMAIN and ADMIN_DOMAIN must be different hosts.');
}

$registerRouteFile = static function (?string $domain, string $routeFile): void {
    if ($domain !== null) {
        Route::domain($domain)->group($routeFile);
        return;
    }

    Route::group([], $routeFile);
};

$registerRouteFile($publicDomain, __DIR__ . '/public.php');
$registerRouteFile($adminDomain, __DIR__ . '/admin.php');