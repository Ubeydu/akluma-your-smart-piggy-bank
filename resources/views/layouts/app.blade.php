<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
            <p>{{ __('help_popup_message') }}</p>
            <p class="help-email-container">
                <span class="help-email">u.keles@gmail.com</span>
                <button class="help-copy-btn" title="{{ __('copy_email_title') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
            </p>
            <p class="mt-4 text-sm">
                &mdash;
                <span class="font-medium">Ubeydullah Keleş, {{ __('founder') }}</span>
            </p>
        </div>
    </div>


</html>
