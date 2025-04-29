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
        $path = $request->path(); // Use ->path() instead of ->getPathInfo()

        // Early return for homepage (empty path) or already has slash
        if ($path === '' || str_ends_with($path, '/')) {
            return $next($request);
        }

        // Early return for files (e.g., .css, .js, .png)
        if (str_contains($path, '.') && preg_match('/\.\w+$/', $path)) {
            return $next($request);
        }

        // Force redirect with trailing slash
        $url = $request->fullUrl();
        if (!str_ends_with($url, '/')) {
            $url .= '/';
        }

        return redirect($url, 301);
    }
}
