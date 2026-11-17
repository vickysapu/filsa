<?php
// app/Helpers/UrlHelper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class UrlHelper
{
    public static function routeWithToken(string $route, array $params = [], array $query = []): string
    {
        $routeUrl = route($route, $params, false);
        
        $tokenKey = 'page_token_' . md5(parse_url($routeUrl, PHP_URL_PATH));
        
        $token = Session::get($tokenKey);
        if (!$token) {
            $token = Str::random(32);
            Session::put($tokenKey, $token);
        }
        
        $query['_token_nav'] = $token;
        
        return route($route, $params) . '?' . http_build_query($query);
    }
    
    public static function urlWithToken(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $tokenKey = 'page_token_' . md5($path);
        
        $token = Session::get($tokenKey);
        if (!$token) {
            $token = Str::random(32);
            Session::put($tokenKey, $token);
        }
        
        $separator = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        return $url . $separator . '_token_nav=' . $token;
    }
}