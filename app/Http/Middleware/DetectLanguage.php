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
            Session::put('locale', auth()->user()->language ?? 'en');
            App::setLocale(auth()->user()->language ?? 'en');
        } else {
            // Check session first
            if (Session::has('locale')) {
                App::setLocale(Session::get('locale'));
            } elseif (!$request->session()->has('locale')) { // Only detect if session is empty
                // Detect browser language
                $browserLanguage = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);

//                // Add this after the line that gets the browser language
//                \Log::info('Browser language detected: ' . $browserLanguage);
//                \Log::info('Full HTTP_ACCEPT_LANGUAGE: ' . $request->server('HTTP_ACCEPT_LANGUAGE'));

                $availableLanguages = ['tr', 'en', 'fr'];

                // Use detected language if supported; otherwise, default to English
                $language = in_array($browserLanguage, $availableLanguages) ? $browserLanguage : 'en';

                // Store in session
                Session::put('locale', $language);
                App::setLocale($language);
            }
        }

        return $next($request);
    }
}
