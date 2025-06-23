<?php

namespace App\Helpers;

class RouteSlugHelper
{
    /**
     * Get translated slug for a route name and locale
     *
     * @param string $routeKey The route key from config (e.g., 'piggy-banks')
     * @param string|null $locale The locale (defaults to current locale)
     * @return string The translated slug
     */
    public static function getSlug(string $routeKey, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $routes = config('route-slugs.routes', []);

        if (!isset($routes[$routeKey])) {
            // Fallback to the route key itself if not found
            return $routeKey;
        }

        if (!isset($routes[$routeKey][$locale])) {
            // Fallback to English if locale not found
            return $routes[$routeKey]['en'] ?? $routeKey;
        }

        return $routes[$routeKey][$locale];
    }

    /**
     * Get translated parameter name for a parameter and locale
     *
     * @param string $paramKey The parameter key from config (e.g., 'piggy_id')
     * @param string|null $locale The locale (defaults to current locale)
     * @return string The translated parameter name
     */
    public static function getParameter(string $paramKey, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $parameters = config('route-slugs.parameters', []);

        if (!isset($parameters[$paramKey])) {
            return $paramKey;
        }

        if (!isset($parameters[$paramKey][$locale])) {
            return $parameters[$paramKey]['en'] ?? $paramKey;
        }

        return $parameters[$paramKey][$locale];
    }

    /**
     * Get all available locales from the configuration
     *
     * @return array
     */
    public static function getAvailableLocales(): array
    {
        return array_keys(config('app.available_languages', []));
    }
}
