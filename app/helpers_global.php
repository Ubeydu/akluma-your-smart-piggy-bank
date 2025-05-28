<?php

/**
 * Global helper functions for localized routing
 */

if (!function_exists('localizedRoute')) {
    function localizedRoute(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        // Create locale-specific route name: localized.dashboard.en
        $localeSpecificRouteName = $routeName . '.' . $locale;

        // Always ensure locale is in parameters
        $parameters['locale'] = $locale;

        return route($localeSpecificRouteName, $parameters);
    }
}

if (!function_exists('localizedUrl')) {
    function localizedUrl(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        return \App\Helpers\RouteHelper::localizedUrl($routeName, $parameters, $locale);
    }
}
