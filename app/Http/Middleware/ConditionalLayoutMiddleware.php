<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ConditionalLayoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For guests, set the flag to use welcome layout
        // BUT ONLY when on the exact locale route
        if (!Auth::check()) {
            $path = $request->path();
            $segments = explode('/', $path);

            // Only set welcome layout for exact locale path
            if (count($segments) === 1) {
                View::share('useWelcomeLayout', true);
            } else {
                View::share('useWelcomeLayout', false);
            }
        }

        return $next($request);
    }
}
