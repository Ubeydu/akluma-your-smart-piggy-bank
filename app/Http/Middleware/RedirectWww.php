<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectWww
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getHost() === 'www.akluma.com') {
            return redirect()->to('https://akluma.com' . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
