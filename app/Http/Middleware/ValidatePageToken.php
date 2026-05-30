<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ValidatePageToken
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*') || !$request->is('admin/*')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        $urlToken = $request->query('_token_nav');

        $tokenKey = 'page_token_' . md5($request->path());

        $sessionToken = Session::get($tokenKey);

        if (!$sessionToken) {
            $sessionToken = Str::random(32);
            Session::put($tokenKey, $sessionToken);
        }

        if (!$urlToken) {
            return redirect()->to($request->fullUrlWithQuery(['_token_nav' => $sessionToken]));
        }

        if ($urlToken !== $sessionToken) {
            return redirect()->to($request->url() . '?' . http_build_query(array_merge($request->query(), ['_token_nav' => $sessionToken])));
        }

        $request->attributes->set('page_token', $sessionToken);

        return $next($request);
    }
}
