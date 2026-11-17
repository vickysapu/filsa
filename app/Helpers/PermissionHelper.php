<?php

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        return auth()->check() && auth()->user()->hasPermission($permission);
    }
}

if (!function_exists('role')) {
    function role(string $roleName): bool
    {
        return auth()->check() && auth()->user()->hasRole($roleName);
    }
}

if (!function_exists('hasAnyRole')) {
    function hasAnyRole(array $roles): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole($roles);
    }
}