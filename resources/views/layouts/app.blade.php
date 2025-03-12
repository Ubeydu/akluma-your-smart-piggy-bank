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

        <link rel="icon" type="image/png" href="/public/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/public/favicon.svg" />
        <link rel="shortcut icon" href="/public/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/public/apple-touch-icon.png" />
        <meta name="apple-mobile-web-app-title" content="Akluma" />
        <link rel="manifest" href="/public/site.webmanifest" />

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
                <header class="bg-white shadow">
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
</html>
