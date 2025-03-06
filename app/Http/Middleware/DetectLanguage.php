<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DetectLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Authenticated users use their saved language
            App::setLocale(auth()->user()->language);
        } else {
            // Check session first
            if (Session::has('app_locale')) {
                App::setLocale(Session::get('app_locale'));
            } else {
                // Detect browser language
                $browserLanguage = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
                $availableLanguages = ['tr', 'en', 'fr'];

                // Use detected language if supported; otherwise, default to English
                $language = in_array($browserLanguage, $availableLanguages) ? $browserLanguage : 'en';

                // Store in session
                Session::put('app_locale', $language);
                App::setLocale($language);
            }
        }

        return $next($request);
    }
}
