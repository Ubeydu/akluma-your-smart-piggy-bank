<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageSwitcher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLanguages = config('app.available_languages', []);
        $locale = \Session::get('locale');

        \Log::info('Debugging Locale Check:', [
            'locale' => $locale,
            'availableLanguages' => $availableLanguages
        ]);

        if ($locale && in_array($locale, $availableLanguages)) {
            \App::setLocale($locale);
        }

        return $next($request);
    }
}
