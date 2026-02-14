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

        <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
        <meta name="apple-mobile-web-app-title" content="Akluma" />
        <link rel="manifest" href="{{ asset('site.webmanifest') }}" />

        <!-- This makes your Laravel routes available to JavaScript -->
        @routes

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-200">
            @if(Auth::check() || !isset($useWelcomeLayout))
                @include('layouts.navigation')
            @else

                    <div class="w-full">
                        <x-unauthenticated-header />
                    </div>

            @endif

            {{-- Email verification banner for unverified users --}}
            @auth
                @if (!auth()->user()->hasVerifiedEmail())
                    <div class="w-full bg-amber-50 border-b border-amber-200">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between text-sm text-amber-800">
                            <span>{{ __('Verify your email to make sure your savings reminders reach you.') }}</span>
                            <a href="{{ localizedRoute('localized.verification.notice') }}"
                               class="shrink-0 ml-4 font-medium underline hover:text-amber-900">
                                {{ __('Verify now') }}
                            </a>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <x-flash-message />
        </div>
    </body>

    <!-- Help Popup -->
    <div id="helpPopup" class="help-popup">
        <div class="help-popup-content">
            <span class="help-close-btn">&times;</span>
            <p>
                {{ __('help_popup_message_part1') }}
                <span class="inline-flex items-center space-x-1">
                <span class="help-email">contact@akluma.com</span>
                <button class="help-copy-btn" title="{{ __('copy_email_title') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
            </span>
                {{ __('help_popup_message_part2') }}
            </p>

            <p class="mt-4 text-sm">
                &mdash;
                <a
                    href="https://www.linkedin.com/in/ubeydullah-kele%C5%9F-2221a915/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-medium text-blue-500 hover:underline"
                >
                    Ubeydullah Keleş, {{ __('founder') }}
                </a>
            </p>
        </div>
    </div>

    @vite(['resources/js/help-popup.js'])
</html>
