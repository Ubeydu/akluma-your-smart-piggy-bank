<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * @param  Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Store the intended URL for post-login redirect
        session(['url.intended' => URL::current()]);

        try {
            // Attempt standard authentication check
            return parent::handle($request, $next, ...$guards);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Authentication failed, determine appropriate locale for redirection
            $locale = $request->segment(1);
            $availableLocales = array_keys(config('app.available_languages', []));

            \Log::info('🔐 Authentication failed - locale detection', [
                'url' => $request->url(),
                'first_segment' => $locale,
                'available_locales' => $availableLocales,
                'session_locale' => session('locale'),
                'is_valid_locale' => in_array($locale, $availableLocales),
            ]);

            // Fallback to session locale, then default locale if invalid
            if (! in_array($locale, $availableLocales)) {
                $locale = session('locale', config('app.locale', 'en'));
                \Log::warning('🔄 Using fallback locale', [
                    'fallback_locale' => $locale,
                    'reason' => 'URL segment not in available locales'
                ]);
            }

            \Log::info('🔐 Redirecting to localized login', [
                'target_route' => "localized.login.{$locale}",
                'locale' => $locale,
                'intended_url' => session('url.intended'),
            ]);

            // Redirect to the localized login page
            return redirect()->route("localized.login.{$locale}", ['locale' => $locale]);
        }
    }
}
