<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTrailingSlash
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only force trailing slash for GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $path = $request->path(); // e.g., 'about', 'about/'

        // Skip homepage
        if ($path === '') {
            return $next($request);
        }

        // Skip files (like .css, .js, .png, etc.)
        if (str_contains($path, '.') && preg_match('/\.\w+$/', $path)) {
            return $next($request);
        }

        // Skip if already ends with a slash
        if (str_ends_with($path, '/')) {
            return $next($request);
        }

        // Force redirect with trailing slash
        return redirect($request->fullUrl() . '/', 301);
    }
}
