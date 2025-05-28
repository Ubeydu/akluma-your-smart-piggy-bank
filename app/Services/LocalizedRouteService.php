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
            // Get translated slug for this locale
            $translatedSlug = RouteSlugHelper::getSlug($routeKey, $locale);

            // Build the full URI pattern with locale prefix
            $uri = '{locale}/' . $translatedSlug;

            // Create UNIQUE route name per locale
            $uniqueRouteName = $routeName . '.' . $locale;

            // Create the route for this locale
            $route = Route::$method($uri, $action)
                ->name($uniqueRouteName)
                ->where('locale', '[a-z]{2}')
                ->middleware('locale');

            // Apply any additional options
            if (!empty($options['middleware'])) {
                $route->middleware($options['middleware']);
            }

            if (!empty($options['where'])) {
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
}
