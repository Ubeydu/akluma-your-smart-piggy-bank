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

        \Log::info('🛠️ localizedRoute() helper debug', [
            'input_route' => $routeName,
            'input_locale' => $locale,
            'resolved_route_name' => $localeSpecificRouteName,
            'parameters' => $parameters,
            'route_exists' => \Route::has($localeSpecificRouteName),
            'app_locale' => app()->getLocale(),
        ]);

        $generatedUrl = route($localeSpecificRouteName, $parameters);
        
        \Log::info('✅ localizedRoute() result', [
            'generated_url' => $generatedUrl,
            'route_name' => $localeSpecificRouteName,
        ]);

        return $generatedUrl;
    }
}

if (!function_exists('localizedUrl')) {
    function localizedUrl(string $routeName, array $parameters = [], ?string $locale = null): string
    {
        return \App\Helpers\RouteHelper::localizedUrl($routeName, $parameters, $locale);
    }
}
