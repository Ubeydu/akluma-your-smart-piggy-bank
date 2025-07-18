<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @hasSection('title')
                @yield('title')
            @else
                {{ __('app_name') }}
            @endif
        </title>

        @if(Str::startsWith(Route::currentRouteName(), ['localized.password.request', 'localized.password.reset', 'localized.verification.notice', 'localized.password.confirm']))
            <meta name="robots" content="noindex, nofollow">
        @endif

        <meta name="description" content="@yield('meta_description', __('default_meta_description'))">

        <!-- Open Graph Meta Tags -->
        <meta property="og:title" content="@yield('og_title', __('app_name'))">
        <meta property="og:description" content="@yield('og_description', __('default_meta_description'))">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ __('app_name') }}">

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ $__env->yieldContent('twitter_title', __('app_name')) }}">
        <meta name="twitter:description" content="{{ $__env->yieldContent('twitter_description', __('default_meta_description')) }}">

        <link rel="canonical" href="{{ str_replace('https://www.akluma.com', 'https://akluma.com', url()->current()) }}" />

        @php
            $currentLocale = app()->getLocale();
            $availableLocales = array_keys(config('app.available_languages', []));
            $currentRouteName = Route::currentRouteName();
        @endphp

        @if($currentRouteName && str_starts_with($currentRouteName, 'localized.'))
            @foreach($availableLocales as $locale)
                @php
                    $routeNameParts = explode('.', $currentRouteName);
                    $baseRouteName = implode('.', array_slice($routeNameParts, 0, -1));
                    $localeRouteName = $baseRouteName . '.' . $locale;

                    try {
                        $localizedUrl = str_replace('https://www.akluma.com', 'https://akluma.com',
                            route($localeRouteName, array_merge(request()->route()->parameters(), ['locale' => $locale]))
                        );
                    } catch (Exception $e) {
                        $localizedUrl = null;
                    }
                @endphp

                @if($localizedUrl)
                    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $localizedUrl }}" />
                @endif
            @endforeach

            {{-- x-default hreflang points to your default (English) version --}}
            @php
                $defaultLocale = 'en';
                $routeNameParts = explode('.', $currentRouteName);
                $baseRouteName = implode('.', array_slice($routeNameParts, 0, -1));
                $defaultRouteName = $baseRouteName . '.' . $defaultLocale;
             try {
                    $xDefaultUrl = str_replace('https://www.akluma.com', 'https://akluma.com',
                        route($defaultRouteName, array_merge(request()->route()->parameters(), ['locale' => $defaultLocale]))
                    );
                } catch (Exception $e) {
                    $xDefaultUrl = null;
                }
            @endphp
            @if($xDefaultUrl)
                <link rel="alternate" hreflang="x-default" href="{{ $xDefaultUrl }}" />
            @endif
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-600" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
