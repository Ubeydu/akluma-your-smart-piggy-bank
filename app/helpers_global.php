<?php

/**
 * Global helper functions for localized routing
 */

if (!function_exists('localizedRoute')) {
    function localizedRoute(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        return \App\Helpers\RouteHelper::localizedRoute($routeName, $parameters, $locale);
    }
}

if (!function_exists('localizedUrl')) {
    function localizedUrl(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        return \App\Helpers\RouteHelper::localizedUrl($routeName, $parameters, $locale);
    }
}
