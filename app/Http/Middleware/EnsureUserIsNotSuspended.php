<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuspended()) {
            $language = Auth::user()->language ?? 'en';

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            app()->setLocale($language);

            return redirect("/{$language}/login")->withErrors([
                'email' => __('account_suspended_message'),
            ]);
        }

        return $next($request);
    }
}
