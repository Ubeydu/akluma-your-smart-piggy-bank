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

        // The route names already include locale suffix (e.g., 'localized.dashboard.en')
        // So we just need to check if the route exists with locale suffix
        $localizedRouteName = \Route::has($routeName . '.' . $locale)
            ? $routeName . '.' . $locale
            : $routeName;

        // Always ensure locale is in parameters for localized routes
        $parameters['locale'] = $locale;

//        \Log::debug('🔍 localizedRoute() debug', [
//            'input_route' => $routeName,
//            'locale' => $locale,
//            'resolved_name' => $localizedRouteName,
//            'parameters' => $parameters,
//            'route_exists' => \Route::has($localizedRouteName),
//        ]);

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
