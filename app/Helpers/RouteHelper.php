<?php

namespace App\Helpers;

class RouteHelper
{
    /**
     * Generate a localized route URL without manual locale passing
     *
     * @param  string  $routeName  The route name (e.g., 'localized.piggy-banks.index')
     * @param  array  $parameters  Route parameters
     * @param  string|null  $locale  Override locale (defaults to current)
     */
    public static function localizedRoute(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        // Append locale to route name to get the language-specific route
        $localizedRouteName = $routeName . '.' . $locale;

        // Always ensure locale is in parameters for localized routes
        $parameters['locale'] = $locale;

        return route($localizedRouteName, $parameters);
    }


    /**
     * Generate an absolute localized URL
     *
     * @param  string  $routeName  The route name
     * @param  array  $parameters  Route parameters
     * @param  string|null  $locale  Override locale
     */
    public static function localizedUrl(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        return self::localizedRoute($routeName, $parameters, $locale);
    }
}
