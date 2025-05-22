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


///**
// * Middleware: SetLocaleFromUrl
// *
// * This middleware checks the first segment of the URL to determine the requested locale.
// *
// * - If the locale is valid (i.e., listed in config('app.available_languages')):
// *     → Sets the app locale
// *     → Stores it in session
// *     → Updates the user's preferred language in the database if authenticated
// *
// * - If the locale is invalid or missing:
// *     → Redirects to the same path prefixed with the appropriate locale
// *        (default or user-preferred)
// *     → Ensures no redirect loop or repeated invalid locale segment
// */
//class SetLocaleFromUrl
//{
//    /**
//     * Handle an incoming request.
//     *
//     * @param  Closure(Request): (Response)  $next
//     */
//    public function handle(Request $request, Closure $next): Response
//    {
//        // Get available languages from config
//        $availableLocales = array_keys(config('app.available_languages', []));
//
//        // Get the locale from the URL
//        $locale = $request->segment(1);
//
//        // Check if the URL has a valid locale
//        if (in_array($locale, $availableLocales)) {
//            // Set the application locale
//            App::setLocale($locale);
//
//            // Store in session
//            Session::put('locale', $locale);
//
//            // Persist in DB for authenticated users
//            if (Auth::check() && Auth::user()->language !== $locale) {
//                Auth::user()->update(['language' => $locale]);
//            }
//
//            $request->route()->forgetParameter('locale');
//
//            return $next($request);
//        }
//
//        // If no valid locale in URL, redirect to default locale (English)
//        // or user's preferred locale if authenticated
//        $defaultLocale = Auth::check() ? Auth::user()->language : 'en';
//
//        // Make sure the default locale is valid, fallback to 'en' if not
//        if (!in_array($defaultLocale, $availableLocales)) {
//            $defaultLocale = 'en';
//        }
//
//        // Avoid redirect loop: only redirect if current URL doesn't already start with the desired locale
//        if ($request->segment(1) !== $defaultLocale) {
//            // Build the redirect URL with the locale prefix
//            $redirectUrl = '/' . $defaultLocale;
//
//            // Remove the first segment (invalid locale) and append the rest
//            $segments = $request->segments();
//            array_shift($segments); // remove invalid locale
//            if (!empty($segments)) {
//                $redirectUrl .= '/' . implode('/', $segments);
//            }
//
//            // Append query string if it exists
//            if ($request->getQueryString()) {
//                $redirectUrl .= '?' . $request->getQueryString();
//            }
//
//            return redirect($redirectUrl);
//        }
//
//        // Already prefixed correctly, let the request proceed
//        return $next($request);
//
//    }
//}
