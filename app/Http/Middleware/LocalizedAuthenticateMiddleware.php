<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class LocalizedAuthenticateMiddleware extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * Extends Laravel's Authenticate middleware to redirect to localized login pages
     * based on the current URL's locale segment.
     *
     * @param  Request  $request
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
//        \Log::info('Auth middleware check', [
//            'path' => $request->path(),
//            'is_ajax' => $request->ajax(),
//            'is_xhr' => $request->header('X-Requested-With'),
//            'auth_check' => auth()->check(),
//            'session_id' => session()->getId(),
//        ]);

        // Only store the intended URL if it's not already set and user is not authenticated
        if (! session()->has('url.intended') && ! auth()->check()) {
            //            \Log::info('🔍 LocalizedAuthenticateMiddleware - Storing intended URL', [
            //                'intended_url' => URL::current(),
            //                'full_url' => $request->fullUrl(),
            //                'session_intended' => session('url.intended'),
            //            ]);

            session(['url.intended' => $request->fullUrl()]);
        }

        try {
            // Attempt standard authentication check
            return parent::handle($request, $next, ...$guards);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Log authentication exception details
            \Log::warning('Authentication exception caught', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'intended_url' => session('url.intended'),
                'session_id' => session()->getId(),
                'path' => $request->path(),
                'is_ajax' => $request->ajax(),
                'is_xhr' => $request->header('X-Requested-With'),
                'cookies' => $request->cookies->all(),
                'headers' => $request->headers->all(),
            ]);

            // Special handling for AJAX requests
            if ($request->ajax() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                \Log::info('AJAX auth failure - returning 401 instead of redirect');
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // Authentication failed, determine appropriate locale for redirection
            $locale = $request->segment(1);
            $availableLocales = array_keys(config('app.available_languages', []));

            //            \Log::info('🔐 Authentication failed - locale detection', [
            //                'url' => $request->url(),
            //                'first_segment' => $locale,
            //                'available_locales' => $availableLocales,
            //                'session_locale' => session('locale'),
            //                'is_valid_locale' => in_array($locale, $availableLocales),
            //            ]);

            // Fallback to session locale, then default locale if invalid
            if (! in_array($locale, $availableLocales)) {
                $locale = session('locale', config('app.locale', 'en'));
                //                \Log::warning('🔄 Using fallback locale', [
                //                    'fallback_locale' => $locale,
                //                    'reason' => 'URL segment not in available locales',
                //                ]);
            }

            //            \Log::info('🔐 Redirecting to localized login', [
            //                'target_route' => "localized.login.{$locale}",
            //                'locale' => $locale,
            //                'intended_url' => session('url.intended'),
            //            ]);

            // Redirect to the localized login page
            return redirect()->route("localized.login.{$locale}", ['locale' => $locale]);
        }
    }
}
