<?php

namespace App\Services;

use App\Helpers\RouteSlugHelper;
use Illuminate\Support\Facades\Route;

class LocalizedRouteService
{
    /**
     * Register a route for all available locales with translated slugs
     *
     * @param  string  $method  HTTP method (GET, POST, etc.)
     * @param  string  $routeKey  Route key from config/route-slugs.php (e.g., 'piggy-banks')
     * @param  string|callable  $action  Controller action or closure
     * @param  string  $routeName  Route name (e.g., 'localized.piggy-banks.index')
     * @param  array  $options  Additional route options (middleware, constraints, etc.)
     */
    public static function register(
        string $method,
        string $routeKey,
        $action,
        string $routeName,
        array $options = []
    ): void {
        $availableLocales = RouteSlugHelper::getAvailableLocales();

        foreach ($availableLocales as $locale) {
            // Handle compound route keys (e.g., 'create-piggy-bank/step-1')
            $translatedSlug = self::translateCompoundRouteKey($routeKey, $locale);

            // Build the full URI pattern with locale prefix
            // Handle empty slugs properly
            $uri = $translatedSlug === ''
                ? '{locale}/home-'.$locale  // fallback to locale-specific URI
                : '{locale}/'.$translatedSlug;

            // Create UNIQUE route name per locale
            $uniqueRouteName = $routeName.'.'.$locale;

            // Create the route for this locale
            $route = Route::$method($uri, $action)
                ->name($uniqueRouteName)
                ->where('locale', $locale)
                ->middleware('locale');

            // Comment out the debug logging to mute it while preserving for future use
            /*
            \Log::debug('REGISTERED ROUTE', [
                'locale' => $locale,
                'name' => $uniqueRouteName,
                'uri' => $uri,
            ]);
            */

            if (! empty($options['middleware'])) {
                // Replace 'auth' with 'localized.auth' in middleware
                if (is_array($options['middleware'])) {
                    foreach ($options['middleware'] as $key => $middleware) {
                        if ($middleware === 'auth') {
                            $options['middleware'][$key] = 'localized.auth';
                        }
                    }
                }
                $route->middleware($options['middleware']);
            }

            if (! empty($options['where'])) {
                $route->where($options['where']);
            }
        }
    }

    /**
     * Get all available locales from configuration
     */
    public static function getAvailableLocales(): array
    {
        return array_keys(config('app.available_languages', []));
    }

    /**
     * Translate a compound route key by first checking for full key, then falling back to segment translation
     *
     * @param  string  $routeKey  Route key that may contain slashes (e.g., 'create-piggy-bank/step-1')
     * @param  string  $locale  Target locale
     * @return string Translated route with all segments translated
     */
    private static function translateCompoundRouteKey(string $routeKey, string $locale): string
    {
        // First, try to get the full route key as-is from the config
        $fullKeyTranslation = RouteSlugHelper::getSlug($routeKey, $locale);

        // If we got a translation different from the input, use it
        if ($fullKeyTranslation !== $routeKey) {
            return $fullKeyTranslation;
        }

        // Otherwise, fall back to segment-by-segment translation
        $segments = explode('/', $routeKey);

        // Translate each segment
        $translatedSegments = array_map(function ($segment) use ($locale) {
            return RouteSlugHelper::getSlug($segment, $locale);
        }, $segments);

        // Join back with slashes
        return implode('/', $translatedSegments);
    }
}
