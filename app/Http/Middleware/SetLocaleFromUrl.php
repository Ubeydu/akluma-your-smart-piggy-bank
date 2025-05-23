<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Log;
use Symfony\Component\HttpFoundation\Response;


/**
 * Middleware: SetLocaleFromUrl
 *
 * Handles locale setting for both {locale}-prefixed routes (e.g. /en/foo) and non-localized routes (e.g. /reset-password/{token}).
 */
class SetLocaleFromUrl
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // List of supported locales
        $availableLocales = array_keys(config('app.available_languages', []));

        // Get the first URL segment
        $locale = $request->segment(1);

        Log::debug('SetLocaleFromUrl: Incoming URL', ['full_url' => $request->fullUrl(), 'lang' => $request->query('lang') ?? null]);

        // If ?lang=xx is present in query, use it (only if valid)
        if ($request->has('lang')) {
            $lang = $request->query('lang');
            if (in_array($lang, $availableLocales)) {
                App::setLocale($lang);
                Session::put('locale', $lang);
                return $next($request);
            }
        }


        /**
         * 1. CASE: URL starts with a valid locale segment (e.g., /en/register)
         * -------------------------------------------------
         * - Sets locale from URL
         * - Stores it in session
         * - If user is logged in, updates user's language preference in DB
         * - Forgets the 'locale' parameter for routing to avoid issues
         */
        if (in_array($locale, $availableLocales)) {
            App::setLocale($locale);
            Session::put('locale', $locale);

            if (Auth::check() && Auth::user()->language !== $locale) {
                Auth::user()->update(['language' => $locale]);
            }

            // Remove 'locale' from route parameters to avoid issues elsewhere
            $request->route()->forgetParameter('locale');

            return $next($request);
        }

        /**
         * 2. CASE: URL does NOT start with a locale segment (e.g., /reset-password/{token})
         * -------------------------------------------------
         * - Sets the locale from user preference (if authenticated)
         * - Else, sets from session (last chosen by guest)
         * - Else, falls back to 'en'
         * - DOES NOT redirect or change the URL
         *
         * This ensures password reset, login, and other non-localized routes still show in the user's language,
         * but don't break with redirects or require duplicate routes.
         */
        if (Auth::check() && Auth::user()->language && in_array(Auth::user()->language, $availableLocales)) {
            App::setLocale(Auth::user()->language);
            Session::put('locale', Auth::user()->language);
        } elseif (Session::has('locale') && in_array(Session::get('locale'), $availableLocales)) {
            App::setLocale(Session::get('locale'));
        } else {
            App::setLocale('en');
        }

        // Continue to next middleware / controller
        return $next($request);
    }
}
