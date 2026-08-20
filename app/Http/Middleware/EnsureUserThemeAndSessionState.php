<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserThemeAndSessionState
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Read theme from cookie or default to system
        $cookieTheme = $request->cookie('theme', 'system');
        if (!in_array($cookieTheme, ['dark', 'light', 'system'])) {
            $cookieTheme = 'system';
        }

        $isDark = ($cookieTheme === 'dark');

        // Share globally to all Blade templates
        View::share('userTheme', $cookieTheme);
        View::share('isDark', $isDark);

        $response = $next($request);

        // If theme cookie is not set or needs refresh, attach it to response
        if (!$request->hasCookie('theme') || $request->cookie('theme') !== $cookieTheme) {
            $response->headers->setCookie(
                cookie('theme', $cookieTheme, 60 * 24 * 365, '/', null, false, false, false, 'Lax')
            );
        }

        // Prevent stale browser layout caching on authenticated routes
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-cache, private, must-revalidate');
        }

        return $response;
    }
}
