<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{!! __('welcome_page_title') !!} - {!! __('app_name') !!}</title>

        <meta name="description" content="{!! __('welcome_meta_description') !!}">

        <!-- Open Graph Meta Tags -->
        <meta property="og:title" content="{!! __('welcome_page_title') !!} - {!! __('app_name') !!}">
        <meta property="og:description" content="{!! __('welcome_meta_description') !!}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ __('app_name') }}">

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:image" content="{{ asset('apple-touch-icon.png') }}">
        <meta name="twitter:title" content="{!! __('welcome_page_title') !!} - {!! __('app_name') !!}">
        <meta name="twitter:description" content="{!! __('welcome_meta_description') !!}">

        <link rel="canonical" href="{{ str_replace('https://www.akluma.com', 'https://akluma.com', url()->current()) }}" />

        @php
            $availableLocales = array_keys(config('app.available_languages', []));
        @endphp

        @foreach($availableLocales as $locale)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ str_replace('https://www.akluma.com', 'https://akluma.com', url('/') . $locale) }}" />
        @endforeach

        {{-- x-default for welcome page points to English version --}}
        <link rel="alternate" hreflang="x-default" href="{{ str_replace('https://www.akluma.com', 'https://akluma.com', url('/en')) }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Favicons and Icons -->
        <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
        <meta name="apple-mobile-web-app-title" content="Akluma" />
        <link rel="manifest" href="{{ asset('site.webmanifest') }}" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /* ! tailwindcss v3.4.1 | MIT License | https://tailwindcss.com */*,::after,::before{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}::after,::before{--tw-content:''}:host,html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:Figtree, ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;font-feature-settings:normal;font-variation-settings:normal;-webkit-tap-highlight-color:transparent}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,pre,samp{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-feature-settings:normal;font-variation-settings:normal;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-size:100%;font-weight:inherit;line-height:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}[type=button],[type=reset],[type=submit],button{-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dd,dl,figure,h1,h2,h3,h4,h5,h6,hr,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}menu,ol,ul{list-style:none;margin:0;padding:0}dialog{padding:0}textarea{resize:vertical}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}[role=button],button{cursor:pointer}:disabled{cursor:default}audio,canvas,embed,iframe,img,object,svg,video{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]{display:none}*, ::before, ::after{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }::backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }.absolute{position:absolute}.relative{position:relative}.-left-20{left:-5rem}.top-0{top:0px}.-bottom-16{bottom:-4rem}.-left-16{left:-4rem}.-mx-3{margin-left:-0.75rem;margin-right:-0.75rem}.mt-4{margin-top:1rem}.mt-6{margin-top:1.5rem}.flex{display:flex}.grid{display:grid}.hidden{display:none}.aspect-video{aspect-ratio:16 / 9}.size-12{width:3rem;height:3rem}.size-5{width:1.25rem;height:1.25rem}.size-6{width:1.5rem;height:1.5rem}.h-12{height:3rem}.h-40{height:10rem}.h-full{height:100%}.min-h-screen{min-height:100vh}.w-full{width:100%}.w-\[calc\(100\%\+8rem\)\]{width:calc(100% + 8rem)}.w-auto{width:auto}.max-w-\[877px\]{max-width:877px}.max-w-2xl{max-width:42rem}.flex-1{flex:1 1 0%}.shrink-0{flex-shrink:0}.grid-cols-2{grid-template-columns:repeat(2, minmax(0, 1fr))}.flex-col{flex-direction:column}.items-start{align-items:flex-start}.items-center{align-items:center}.items-stretch{align-items:stretch}.justify-end{justify-content:flex-end}.justify-center{justify-content:center}.gap-2{gap:0.5rem}.gap-4{gap:1rem}.gap-6{gap:1.5rem}.self-center{align-self:center}.overflow-hidden{overflow:hidden}.rounded-\[10px\]{border-radius:10px}.rounded-full{border-radius:9999px}.rounded-lg{border-radius:0.5rem}.rounded-md{border-radius:0.375rem}.rounded-sm{border-radius:0.125rem}.bg-\[\#FF2D20\]\/10{background-color:rgb(255 45 32 / 0.1)}.bg-white{--tw-bg-opacity:1;background-color:rgb(255 255 255 / var(--tw-bg-opacity))}.bg-linear-to-b{background-image:linear-gradient(to bottom, var(--tw-gradient-stops))}.from-transparent{--tw-gradient-from:transparent var(--tw-gradient-from-position);--tw-gradient-to:rgb(0 0 0 / 0) var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.via-white{--tw-gradient-to:rgb(255 255 255 / 0)  var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), #fff var(--tw-gradient-via-position), var(--tw-gradient-to)}.to-white{--tw-gradient-to:#fff var(--tw-gradient-to-position)}.stroke-\[\#FF2D20\]{stroke:#FF2D20}.object-cover{object-fit:cover}.object-top{object-position:top}.p-6{padding:1.5rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.py-10{padding-top:2.5rem;padding-bottom:2.5rem}.px-3{padding-left:0.75rem;padding-right:0.75rem}.py-16{padding-top:4rem;padding-bottom:4rem}.py-2{padding-top:0.5rem;padding-bottom:0.5rem}.pt-3{padding-top:0.75rem}.text-center{text-align:center}.font-sans{font-family:Figtree, ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji}.text-sm{font-size:0.875rem;line-height:1.25rem}.text-sm\/relaxed{font-size:0.875rem;line-height:1.625}.text-xl{font-size:1.25rem;line-height:1.75rem}.font-semibold{font-weight:600}.text-black{--tw-text-opacity:1;color:rgb(0 0 0 / var(--tw-text-opacity))}.text-white{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.underline{-webkit-text-decoration-line:underline;text-decoration-line:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.shadow-\[0px_14px_34px_0px_rgba\(0\2c 0\2c 0\2c 0\.08\)\]{--tw-shadow:0px 14px 34px 0px rgba(0,0,0,0.08);--tw-shadow-colored:0px 14px 34px 0px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.ring-1{--tw-ring-offset-shadow:var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow:var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)}.ring-transparent{--tw-ring-color:transparent}.ring-white\/\[0\.05\]{--tw-ring-color:rgb(255 255 255 / 0.05)}.drop-shadow-\[0px_4px_34px_rgba\(0\2c 0\2c 0\2c 0\.06\)\]{--tw-drop-shadow:drop-shadow(0px 4px 34px rgba(0,0,0,0.06));filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.drop-shadow-\[0px_4px_34px_rgba\(0\2c 0\2c 0\2c 0\.25\)\]{--tw-drop-shadow:drop-shadow(0px 4px 34px rgba(0,0,0,0.25));filter:var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)}.transition{transition-property:color, background-color, border-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-text-decoration-color, -webkit-backdrop-filter;transition-property:color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;transition-property:color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-text-decoration-color, -webkit-backdrop-filter;transition-timing-function:cubic-bezier(0.4, 0, 0.2, 1);transition-duration:150ms}.duration-300{transition-duration:300ms}.selection\:bg-\[\#FF2D20\] *::selection{--tw-bg-opacity:1;background-color:rgb(255 45 32 / var(--tw-bg-opacity))}.selection\:text-white *::selection{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.selection\:bg-\[\#FF2D20\]::selection{--tw-bg-opacity:1;background-color:rgb(255 45 32 / var(--tw-bg-opacity))}.selection\:text-white::selection{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.hover\:text-black:hover{--tw-text-opacity:1;color:rgb(0 0 0 / var(--tw-text-opacity))}.hover\:text-black\/70:hover{color:rgb(0 0 0 / 0.7)}.hover\:ring-black\/20:hover{--tw-ring-color:rgb(0 0 0 / 0.2)}.focus\:outline-none:focus{outline:2px solid transparent;outline-offset:2px}.focus-visible\:ring-1:focus-visible{--tw-ring-offset-shadow:var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow:var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)}.focus-visible\:ring-\[\#FF2D20\]:focus-visible{--tw-ring-opacity:1;--tw-ring-color:rgb(255 45 32 / var(--tw-ring-opacity))}@media (min-width: 640px){.sm\:size-16{width:4rem;height:4rem}.sm\:size-6{width:1.5rem;height:1.5rem}.sm\:pt-5{padding-top:1.25rem}}@media (min-width: 768px){.md\:row-span-3{grid-row:span 3 / span 3}}@media (min-width: 1024px){.lg\:col-start-2{grid-column-start:2}.lg\:h-16{height:4rem}.lg\:max-w-7xl{max-width:80rem}.lg\:grid-cols-3{grid-template-columns:repeat(3, minmax(0, 1fr))}.lg\:grid-cols-2{grid-template-columns:repeat(2, minmax(0, 1fr))}.lg\:flex-col{flex-direction:column}.lg\:items-end{align-items:flex-end}.lg\:justify-center{justify-content:center}.lg\:gap-8{gap:2rem}.lg\:p-10{padding:2.5rem}.lg\:pb-10{padding-bottom:2.5rem}.lg\:pt-0{padding-top:0px}.lg\:text-\[\#FF2D20\]{--tw-text-opacity:1;color:rgb(255 45 32 / var(--tw-text-opacity))}}@media (prefers-color-scheme: dark){.dark\:block{display:block}.dark\:hidden{display:none}.dark\:bg-black{--tw-bg-opacity:1;background-color:rgb(0 0 0 / var(--tw-bg-opacity))}.dark\:bg-zinc-900{--tw-bg-opacity:1;background-color:rgb(24 24 27 / var(--tw-bg-opacity))}.dark\:via-zinc-900{--tw-gradient-to:rgb(24 24 27 / 0)  var(--tw-gradient-to-position);--tw-gradient-stops:var(--tw-gradient-from), #18181b var(--tw-gradient-via-position), var(--tw-gradient-to)}.dark\:to-zinc-900{--tw-gradient-to:#18181b var(--tw-gradient-to-position)}.dark\:text-white\/50{color:rgb(255 255 255 / 0.5)}.dark\:text-white{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.dark\:text-white\/70{color:rgb(255 255 255 / 0.7)}.dark\:ring-zinc-800{--tw-ring-opacity:1;--tw-ring-color:rgb(39 39 42 / var(--tw-ring-opacity))}.dark\:hover\:text-white:hover{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.dark\:hover\:text-white\/70:hover{color:rgb(255 255 255 / 0.7)}.dark\:hover\:text-white\/80:hover{color:rgb(255 255 255 / 0.8)}.dark\:hover\:ring-zinc-700:hover{--tw-ring-opacity:1;--tw-ring-color:rgb(63 63 70 / var(--tw-ring-opacity))}.dark\:focus-visible\:ring-\[\#FF2D20\]:focus-visible{--tw-ring-opacity:1;--tw-ring-color:rgb(255 45 32 / var(--tw-ring-opacity))}.dark\:focus-visible\:ring-white:focus-visible{--tw-ring-opacity:1;--tw-ring-color:rgb(255 255 255 / var(--tw-ring-opacity))}}
            </style>
        @endif

    </head>
    <body class="font-sans antialiased">

    <!-- Structured Data -->
    @php
        $schemaData = [
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => __('app_name'),
            "url" => url('/'),
            "logo" => asset('apple-touch-icon.png'),
            "sameAs" => []
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode($schemaData) !!}
    </script>


    @php error_reporting(E_ALL); ini_set('display_errors', 1); @endphp

    @include('components.flash-message')

    <div class="bg-gray-50 text-black/50">
        <div class="relative min-h-screen flex flex-col items-center justify-start selection:bg-[#FF2D20] selection:text-white">
            <div class="w-full bg-white">
                <div class="relative max-w-2xl px-6 lg:max-w-7xl mx-auto">
                    <header x-data="{ open: false }">

                        <div class="flex items-center justify-between py-10">
                            <div class="flex justify-start">
                                <x-application-logo class="w-full h-auto max-w-[40px] sm:max-w-[60px] fill-current" />
                            </div>

                            <!-- Desktop navigation -->
                            @if (Route::has('localized.login.' . app()->getLocale()))
                                <nav class="hidden flex-1 -mx-3 justify-end sm:flex">

                                    <a
                                        href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
                                    class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                    >
                                    {{ __('Create New Piggy Bank') }}
                                    </a>


                                    @auth
                                        <a
                                            href="{{ localizedRoute('localized.dashboard') }}"
                                            class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                        >
                                            {{ __('Dashboard') }}
                                        </a>
                                        <a
                                            href="{{ localizedRoute('localized.piggy-banks.index') }}"
                                            class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                        >
                                            {{ __('My Piggy Banks') }}
                                        </a>
                                        <a
                                            href="{{ localizedRoute('localized.vaults.index') }}"
                                            class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                        >
                                            {{ __('My Vaults') }}
                                        </a>
                                    @else

                                        <a
                                            href="{{ localizedRoute('localized.login') }}"
                                        class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                        >
                                        {{ __('Log in') }}
                                        </a>

                                        @if (Route::has('localized.register.' . app()->getLocale()))

                                            <a
                                                href="{{ localizedRoute('localized.register') }}"
                                            class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                            >
                                            {{ __('Register') }}
                                            </a>
                                        @endif
                                    @endauth


                                    <a
                                        href="#"
                                        id="getHelpBtn"
                                        class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]"
                                    >
                                        {{ __('Get Help') }}
                                    </a>

                                    <div class="custom-language-dropdown">
                                        <x-language-dropdown />
                                    </div>

                                </nav>
                            @endif

                            <!-- Mobile menu button -->
                            <div class="flex items-center sm:hidden">
                                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 focus:text-gray-600 transition duration-150 ease-in-out">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5M3.75 18.75h16.5"/>
                                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Mobile navigation menu -->
                        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                            <div class="pt-2 pb-3 space-y-1">

                                <x-responsive-nav-link href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
                                                       :active="request()->routeIs('create-piggy-bank.*')">
                                    {{ __('Create New Piggy Bank') }}
                                </x-responsive-nav-link>
                                @auth
                                    <x-responsive-nav-link :href="localizedRoute('localized.dashboard')"
                                                           :active="request()->routeIs('localized.dashboard.*')">
                                        {{ __('Dashboard') }}
                                    </x-responsive-nav-link>
                                    <x-responsive-nav-link :href="localizedRoute('localized.piggy-banks.index')"
                                                           :active="request()->routeIs('localized.piggy-banks.index.*')">
                                        {{ __('My Piggy Banks') }}
                                    </x-responsive-nav-link>
                                    <x-responsive-nav-link :href="localizedRoute('localized.vaults.index')"
                                                           :active="request()->routeIs('localized.vaults.*')">
                                        {{ __('My Vaults') }}
                                    </x-responsive-nav-link>
                                @else
                                    <x-responsive-nav-link :href="localizedRoute('localized.login')"
                                                           :active="request()->routeIs('localized.login')">
                                        {{ __('Log in') }}
                                    </x-responsive-nav-link>
                                    @if (Route::has('localized.register.' . app()->getLocale()))
                                        <x-responsive-nav-link :href="localizedRoute('localized.register')"
                                                               :active="request()->routeIs('localized.register')">
                                            {{ __('Register') }}
                                        </x-responsive-nav-link>
                                    @endif
                                @endauth

                                <x-responsive-nav-link href="#" id="getHelpBtnMobile">
                                    {{ __('Get Help') }}
                                </x-responsive-nav-link>

                                <div class="pl-3 pr-4 py-2">
                                    <x-language-dropdown />
                                </div>
                            </div>
                        </div>
                    </header>
                </div>
            </div>

            @auth
                @if (!auth()->user()->hasVerifiedEmail())
                    <div class="w-full">
                        <div class="max-w-2xl px-6 lg:max-w-7xl mx-auto pt-4">
                            <div class="w-full rounded-md bg-amber-50 p-4 text-sm text-amber-800 shadow-md border border-amber-200 flex items-center justify-between">
                                <span>{{ __('Verify your email to make sure your savings reminders reach you.') }}</span>
                                <a
                                    href="{{ localizedRoute('localized.verification.notice') }}"
                                    class="shrink-0 ml-4 underline font-medium hover:text-amber-900"
                                >
                                    {{ __('Verify now') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            {{-- Hero Section --}}
            <div class="w-full bg-gradient-to-br from-gray-50 to-white">
                <div class="relative max-w-2xl px-6 lg:max-w-7xl mx-auto py-16 lg:py-24">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        {{-- Left: Text Content --}}
                        <div class="order-2 lg:order-1">
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                                {{ __('hero_headline') }}
                            </h1>
                            <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-lg">
                                {{ __('hero_subheadline') }}
                            </p>
                            <div class="flex flex-col items-start gap-3">
                                <a href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
                                   class="inline-flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-lg rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                                    {{ __('hero_cta') }}
                                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                                <span class="text-sm text-gray-500 font-bold">
                                    {{ __('hero_cta_subtext') }}
                                </span>
                            </div>
                        </div>

                        {{-- Right: Illustration --}}
                        <div class="order-1 lg:order-2 flex justify-center lg:justify-end">
                            <div class="relative w-64 h-64 md:w-80 md:h-80 lg:w-96 lg:h-96">
                                {{-- Decorative background circles --}}
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full opacity-60"></div>
                                <div class="absolute inset-4 bg-gradient-to-br from-indigo-50 to-white rounded-full"></div>

                                {{-- Piggy Bank SVG Placeholder --}}
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-40 h-40 md:w-48 md:h-48 lg:w-56 lg:h-56 text-indigo-500" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M508.488,169.344c-6.422-14.469-20.484-23.844-35.766-23.844l-1.813,0.047l-10.344,0.547
                                            c-23.219-32.281-55.765-57.156-94.968-72.438c-25.625-10.031-54.203-16-84.938-17.719c-6.391-0.344-12.891-0.531-19.391-0.531
                                            c-4.672,0-9.438,0.094-14.281,0.266c-14.297,0.547-28.422,2.391-42,5.484c-2.172,0.5-4.375,1.031-6.563,1.594
                                            c-27.203-15.969-53.781-19.563-72.203-19.563c-1.531,0-3.016,0.031-4.438,0.063c-3.766,0.125-6.516,0.266-8.594,0.5
                                            c-4.188,0.438-7.844,1.563-10.953,3.188c-6.406,2.719-11.922,7.344-15.781,13.375c-5.672,9-7,19.781-3.766,29.688l12.109,37.109
                                            c-2.406,2.922-4.672,5.891-6.813,8.859c-9.641,13.563-16.359,26.063-22.266,37.094c-7.984,14.906-13.172,24.156-18.906,26.438
                                            l-0.5,0.188l-1.453,0.563l-3.219,1.219c-14.266,5.344-47.656,17.875-40.703,52.594l1.516,7.688
                                            c5.922,30.094,9.859,49.063,12.484,57l1.531,4.672c5.313,16.172,8.625,24.391,25.688,35.391c7.5,4.813,16.625,6.531,26.281,8.313
                                            c2.875,0.531,7.063,1.313,8.953,1.844c1.828,0.906,3.766,1.625,5.781,2.078c6.781,1.578,12.281,2.375,16.719,3.016
                                            c2.875,0.406,6.438,0.938,7.406,1.344c0.484,0.344,4.141,3.328,13.734,20.656l0.5,0.844c5.875,9.688,12.203,25.406,14.297,30.578
                                            c1.109,2.734,2.094,5.25,2.953,7.391c1.922,4.875,2.797,7.125,3.766,9.063c8.313,16.563,23.109,24.875,44.031,24.875h29.359
                                            c18.859,0,37.891-14,37.891-45.25v-5.875v-4.469h51.891v4.469v5.875c0,31.25,18.672,45.25,37.172,45.25h27.547
                                            c20.641,0,35.109-8.531,42.984-25.328c0.969-2.078,1.859-4.469,3.781-9.672c0.625-1.672,1.328-3.563,2.078-5.594
                                            c12.203-24.125,23.968-42.875,36.984-58.906c26.953-31.688,41.203-70.594,41.203-112.484c0-11.234-1.156-22.734-3.453-34.266
                                            c1.781-2.203,3.844-4.656,6.234-7.297C512.098,202.125,515.254,184.563,508.488,169.344z M480.848,197.719
                                            c-7.781,8.563-12.75,15.625-15.031,19.063c3.703,13.75,5.5,27.313,5.5,40.047c0,38.453-13.75,70.672-35.172,95.766
                                            c-17.14,21.094-30.187,43.906-40.843,65.094l0,0c-2.734,7.25-4.766,12.938-5.563,14.688c-2.75,5.875-6.891,10.281-19.297,10.281
                                            c-12.391,0-19.281,0-27.547,0c-8.25,0-11-8.813-11-19.094c0-1.594,0-3.594,0-5.875v-30.641H227.661v30.641c0,2.281,0,4.281,0,5.875
                                            c0,10.281-2.938,19.094-11.719,19.094c-8.813,0-16.172,0-29.359,0c-13.234,0-17.625-4.406-20.563-10.281
                                            c-0.859-1.75-3-7.438-5.922-14.688c-4.234-10.5-10.125-24.344-16.172-34.344c-22.188-40-26.547-31.25-54.875-37.781l-0.016-0.031
                                            c-6.313-3.625-26.594-4.781-32.703-8.719c-11.109-7.156-10.656-8.656-16.547-26.281c-3.109-9.375-10.609-48.688-13.188-61.625
                                            c-2.813-14.016,13.156-18.688,27.844-24.313l2.016-0.781c23.391-9.281,28.844-38.938,52.844-72.656
                                            c4.297-6.016,9.375-12,15.094-17.75l-16.828-51.5c-0.813-2.5-0.484-5.25,0.938-7.5c1.438-2.25,3.781-3.75,6.438-4.063
                                            c0,0-3.656-0.594,7.656-0.938c1.172-0.031,2.375-0.047,3.625-0.047c17.422,0,42.906,3.891,68.016,21.828
                                            c5.313-1.688,10.828-3.219,16.547-4.531c11.688-2.656,24.063-4.344,37.188-4.844c4.5-0.172,8.938-0.25,13.297-0.25
                                            c6.109,0,12.078,0.156,17.906,0.469c29.313,1.688,54.859,7.375,76.906,16c42.781,16.656,72.421,44.344,90.921,74.938
                                            c13.734-0.688,15.797-0.797,25.188-1.281h0.531C482.77,171.688,491.801,185.578,480.848,197.719z"/>
                                        <circle cx="125.458" cy="243.829" r="19.516"/>
                                        <path d="M248.942,106.547l-1.344,24.297c2.125,0.063,4.547,0.188,7.531,0.344c22.125,1.188,56.031,3.125,98.141,14.75
                                            l1.375-25.094c-43.672-11.094-75.781-12.688-98.188-13.938C253.598,106.75,251.098,106.656,248.942,106.547z"/>
                                    </svg>
                                </div>

                                {{-- Floating coin decorations --}}
                                <div class="absolute top-4 right-8 w-8 h-8 bg-yellow-400 rounded-full shadow-lg animate-bounce" style="animation-delay: 0.2s;"></div>
                                <div class="absolute bottom-12 left-4 w-6 h-6 bg-yellow-300 rounded-full shadow-md animate-bounce" style="animation-delay: 0.5s;"></div>
                                <div class="absolute top-1/3 left-0 w-5 h-5 bg-yellow-500 rounded-full shadow animate-bounce" style="animation-delay: 0.8s;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Use Cases Section --}}
            <section class="w-full bg-white py-16 lg:py-24">
                <div class="max-w-2xl px-6 lg:max-w-7xl mx-auto">
                    {{-- Section Heading --}}
                    <div class="text-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            {{ __('use_cases_heading') }}
                        </h2>
                    </div>

                    {{-- Cards Container: Horizontal scroll on mobile, 2x3 grid on desktop --}}
                    <div class="flex overflow-x-auto pb-4 gap-4 snap-x snap-mandatory lg:grid lg:grid-cols-3 lg:gap-6 lg:overflow-visible lg:pb-0 scrollbar-hide">

                        {{-- Card: Travel/Vacation --}}
                        <article class="flex-shrink-0 w-72 snap-start lg:w-auto bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('use_case_vacation_title') }}</h3>
                            <p class="text-gray-600 text-base font-bold">{{ __('use_case_vacation_desc') }}</p>
                        </article>

                        {{-- Card: Tech/Gadgets --}}
                        <article class="flex-shrink-0 w-72 snap-start lg:w-auto bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('use_case_gadgets_title') }}</h3>
                            <p class="text-gray-600 text-base font-bold">{{ __('use_case_gadgets_desc') }}</p>
                        </article>

                        {{-- Card: Life Events/Milestones --}}
                        <article class="flex-shrink-0 w-72 snap-start lg:w-auto bg-gradient-to-br from-rose-50 to-orange-50 rounded-xl p-6 border border-rose-100">
                            <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('use_case_milestones_title') }}</h3>
                            <p class="text-gray-600 text-base font-bold">{{ __('use_case_milestones_desc') }}</p>
                        </article>

                        {{-- Card: Security/Emergency Fund --}}
                        <article class="flex-shrink-0 w-72 snap-start lg:w-auto bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('use_case_emergency_title') }}</h3>
                            <p class="text-gray-600 text-base font-bold">{{ __('use_case_emergency_desc') }}</p>
                        </article>

                        {{-- Card: Education/Learning --}}
                        <article class="flex-shrink-0 w-72 snap-start lg:w-auto bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl p-6 border border-amber-100">
                            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('use_case_learning_title') }}</h3>
                            <p class="text-gray-600 text-base font-bold">{{ __('use_case_learning_desc') }}</p>
                        </article>

                        {{-- Card: Home & Living --}}
                        <article class="flex-shrink-0 w-72 snap-start lg:w-auto bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl p-6 border border-teal-100">
                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('use_case_home_title') }}</h3>
                            <p class="text-gray-600 text-base font-bold">{{ __('use_case_home_desc') }}</p>
                        </article>
                    </div>

                    {{-- CTA Button --}}
                    <div class="text-center mt-12">
                        <a href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
                           class="inline-flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-lg rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            {{ __('hero_cta') }}
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <p class="text-sm text-gray-500 mt-3 font-bold">
                            {{ __('hero_cta_subtext') }}
                        </p>
                    </div>
                </div>
            </section>

            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl mx-auto">



                    <main class="min-h-[50vh] text-6xl font-bold">

                        {{-- How It Works Section --}}
                        <div class="py-16 lg:py-24 relative overflow-hidden bg-white -mx-6 px-6 lg:-mx-[calc((100vw-80rem)/2+1.5rem)] lg:px-[calc((100vw-80rem)/2+1.5rem)]">
                            {{-- Section Heading --}}
                            <div class="text-center mb-12">
                                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                                    {{ __('how_it_works_heading') }}
                                </h2>
                            </div>

                            <!-- Steps container -->
                            <div class="max-w-6xl mx-auto px-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 relative">
                                    <!-- Connection lines (visible on desktop only) -->
                                    <div class="hidden lg:block absolute top-1/2 left-0 w-full h-1 bg-gray-200 -translate-y-1/2 z-0"></div>

                                    <!-- Step 1 -->
                                    <div class="relative z-10 group">
                                        <div class="bg-white rounded-lg shadow-lg p-6 transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-xl">
                                            <!-- Icon container with animated background -->
                                            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-yellow-100 flex items-center justify-center relative overflow-hidden group-hover:scale-110 transition-transform duration-300">
                                                <!-- Animated background -->
                                                <div class="absolute inset-0 bg-linear-to-r from-yellow-200 to-yellow-400 opacity-50 group-hover:opacity-100 transition-opacity duration-300"></div>

                                                <!-- Piggy Bank Icon -->
                                                <svg class="w-12 h-12 text-yellow-600 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>

                                                <!-- Animated ring -->
                                                <div class="absolute inset-0 border-4 border-yellow-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500"></div>
                                            </div>

                                            <!-- Step number badge -->
                                            <div class="absolute -top-4 -right-4 w-14 h-14 bg-yellow-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg transform rotate-0 group-hover:rotate-12 transition-transform duration-300">1</div>

                                            <!-- Content -->
                                            <h3 class="text-xl font-bold text-center mb-3 text-gray-800">{{ __('Set Your Goal') }}</h3>
                                            <p class="text-gray-600 text-center text-base">{{ __('Enter details of your saving goal') }}</p>

                                            <!-- Hidden extra content that shows on hover -->
                                            <div class="max-h-0 overflow-hidden transition-all duration-500 group-hover:max-h-32 mt-2">
                                                <p class="text-yellow-600 text-sm italic text-center pt-2">{{ __('If you add product link, we may be able to fetch its photo') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2 -->
                                    <div class="relative z-10 group">
                                        <div class="bg-white rounded-lg shadow-lg p-6 transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-xl">
                                            <!-- Icon container with animated background -->
                                            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-indigo-100 flex items-center justify-center relative overflow-hidden group-hover:scale-110 transition-transform duration-300">
                                                <!-- Animated background -->
                                                <div class="absolute inset-0 bg-linear-to-r from-indigo-200 to-indigo-400 opacity-50 group-hover:opacity-100 transition-opacity duration-300"></div>

                                                <!-- Calendar/Strategy Icon -->
                                                <svg class="w-12 h-12 text-indigo-600 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>

                                                <!-- Animated ring -->
                                                <div class="absolute inset-0 border-4 border-indigo-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500"></div>
                                            </div>

                                            <!-- Step number badge -->
                                            <div class="absolute -top-4 -right-4 w-14 h-14 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg transform rotate-0 group-hover:rotate-12 transition-transform duration-300">2</div>

                                            <!-- Content -->
                                            <h3 class="text-xl font-bold text-center mb-3 text-gray-800">{{ __('Choose Strategy') }}</h3>
                                            <p class="text-gray-600 text-center text-base">{{ __('Pick a date or define how much you can save') }}</p>

                                            <!-- Hidden extra content that shows on hover -->
                                            <div class="max-h-0 overflow-hidden transition-all duration-500 group-hover:max-h-32 mt-2">
                                                <p class="text-indigo-600 text-sm italic text-center pt-2">{{ __('We\'ll create a personalized saving plan for you') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3 -->
                                    <div class="relative z-10 group">
                                        <div class="bg-white rounded-lg shadow-lg p-6 transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-xl">
                                            <!-- Icon container with animated background -->
                                            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center relative overflow-hidden group-hover:scale-110 transition-transform duration-300">
                                                <!-- Animated background -->
                                                <div class="absolute inset-0 bg-linear-to-r from-green-200 to-green-400 opacity-50 group-hover:opacity-100 transition-opacity duration-300"></div>

                                                <!-- Money/Savings Icon -->
                                                <svg class="w-12 h-12 text-green-600 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>

                                                <!-- Animated coins -->
                                                <div class="absolute top-1/2 left-1/2 w-4 h-4 bg-yellow-400 rounded-full opacity-0 group-hover:opacity-100 group-hover:animate-coin1"></div>
                                                <div class="absolute top-1/2 left-1/2 w-3 h-3 bg-yellow-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:animate-coin2"></div>

                                                <!-- Animated ring -->
                                                <div class="absolute inset-0 border-4 border-green-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500"></div>
                                            </div>

                                            <!-- Step number badge -->
                                            <div class="absolute -top-4 -right-4 w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg transform rotate-0 group-hover:rotate-12 transition-transform duration-300">3</div>

                                            <!-- Content -->
                                            <h3 class="text-xl font-bold text-center mb-3 text-gray-800">{{ __('Save Regularly') }}</h3>
                                            <p class="text-gray-600 text-center text-base">{{ __('Track your progress with reminders and feedback') }}</p>

                                            <!-- Hidden extra content that shows on hover -->
                                            <div class="max-h-0 overflow-hidden transition-all duration-500 group-hover:max-h-32 mt-2">
                                                <p class="text-green-600 text-sm italic text-center pt-2">{{ __('We\'ll help you stay consistent with friendly reminders') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 4 -->
                                    <div class="relative z-10 group">
                                        <div class="bg-white rounded-lg shadow-lg p-6 transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-xl">
                                            <!-- Icon container with animated background -->
                                            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-purple-100 flex items-center justify-center relative overflow-hidden group-hover:scale-110 transition-transform duration-300">
                                                <!-- Animated background -->
                                                <div class="absolute inset-0 bg-linear-to-r from-purple-200 to-purple-400 opacity-50 group-hover:opacity-100 transition-opacity duration-300"></div>

                                                <!-- Trophy/Achievement Icon -->
                                                <svg class="w-12 h-12 text-purple-600 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                                </svg>

                                                <!-- Animated stars -->
                                                <div class="absolute top-0 left-1/4 w-2 h-2 bg-yellow-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:animate-star1"></div>
                                                <div class="absolute bottom-1/4 right-0 w-2 h-2 bg-yellow-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:animate-star2"></div>
                                                <div class="absolute bottom-0 left-1/3 w-2 h-2 bg-yellow-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:animate-star3"></div>

                                                <!-- Animated ring -->
                                                <div class="absolute inset-0 border-4 border-purple-300 rounded-full opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500"></div>
                                            </div>

                                            <!-- Step number badge -->
                                            <div class="absolute -top-4 -right-4 w-14 h-14 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg transform rotate-0 group-hover:rotate-12 transition-transform duration-300">4</div>

                                            <!-- Content -->
                                            <h3 class="text-xl font-bold text-center mb-3 text-gray-800">{{ __('Celebrate Success') }}</h3>
                                            <p class="text-gray-600 text-center text-base">{{ __('Buy your dream item and enjoy your achievement') }}</p>

                                            <!-- Hidden extra content that shows on hover -->
                                            <div class="max-h-0 overflow-hidden transition-all duration-500 group-hover:max-h-32 mt-2">
                                                <p class="text-purple-600 text-sm italic text-center pt-2">{{ __('Your hard work paid off. Time to enjoy your reward') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CTA Button --}}
                            <div class="text-center mt-12">
                                <a href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
                                   class="inline-flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-lg rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                                    {{ __('hero_cta') }}
                                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                                <p class="text-sm text-gray-500 mt-3">
                                    {{ __('hero_cta_subtext') }}
                                </p>
                            </div>

                        </div>

                        {{-- Why Akluma Section --}}
                        <section class="py-16 lg:py-24 bg-gradient-to-br from-gray-50 to-white -mx-6 px-6 lg:-mx-[calc((100vw-80rem)/2+1.5rem)] lg:px-[calc((100vw-80rem)/2+1.5rem)]">
                            <div class="max-w-3xl mx-auto">
                                {{-- Section Heading --}}
                                <div class="text-center mb-12">
                                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                                        {{ __('why_akluma_heading') }}
                                    </h2>
                                </div>

                                {{-- Features List --}}
                                <div class="space-y-8">
                                    {{-- Feature: Email Reminders --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ __('feature_reminders_title') }}</h3>
                                            <p class="text-base text-gray-600 font-bold">{{ __('feature_reminders_desc') }}</p>
                                        </div>
                                    </div>

                                    {{-- Feature: Progress Tracking --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ __('feature_progress_title') }}</h3>
                                            <p class="text-base text-gray-600 font-bold">{{ __('feature_progress_desc') }}</p>
                                        </div>
                                    </div>

                                    {{-- Feature: Flexible Strategies --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ __('feature_flexible_title') }}</h3>
                                            <p class="text-base text-gray-600 font-bold">{{ __('feature_flexible_desc') }}</p>
                                        </div>
                                    </div>

                                    {{-- Feature: Multiple Currencies --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ __('feature_currency_title') }}</h3>
                                            <p class="text-base text-gray-600 font-bold">{{ __('feature_currency_desc') }}</p>
                                        </div>
                                    </div>

                                    {{-- Feature: Free to Use --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ __('feature_free_title') }}</h3>
                                            <p class="text-base text-gray-600 font-bold">{{ __('feature_free_desc') }}</p>
                                        </div>
                                    </div>

                                    {{-- Feature: Privacy --}}
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ __('feature_privacy_title') }}</h3>
                                            <p class="text-base text-gray-600 font-bold">{{ __('feature_privacy_desc') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </main>
                    <footer class="py-16 bg-gray-50 -mx-6 px-6 lg:-mx-[calc((100vw-80rem)/2+1.5rem)] lg:px-[calc((100vw-80rem)/2+1.5rem)]">
                        <div class="max-w-2xl mx-auto text-center">
                            {{-- Logo (piggy bank) --}}
                            <div class="flex justify-center mb-4">
                                <x-application-logo class="w-12 h-12 text-gray-400 fill-current" />
                            </div>

                            {{-- Brand Name --}}
                            <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('app_name') }}</h3>

                            {{-- Contact Email --}}
                            <a href="mailto:contact@akluma.com" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                contact@akluma.com
                            </a>

                            {{-- Links --}}
                            <div class="mt-6 flex justify-center gap-4 text-sm">
                                <a href="{{ localizedRoute('localized.terms') }}" class="text-gray-500 hover:text-gray-700">{{ __('terms.title') }}</a>
                                <span class="text-gray-300">•</span>
                                <a href="{{ localizedRoute('localized.privacy') }}" class="text-gray-500 hover:text-gray-700">{{ __('privacy.title') }}</a>
                            </div>

                            {{-- Copyright --}}
                            <p class="mt-6 text-sm text-gray-400">
                                © {{ date('Y') }} {{ __('app_name') }}. {{ __('All rights reserved.') }}
                            </p>

                            {{-- Dev info (non-production only) --}}
                            @if(config('app.env') !== 'production')
                                <p class="mt-2 text-xs text-gray-300">
                                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                                </p>
                            @endif
                        </div>
                    </footer>
                </div>
            </div>
        </div>

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




    </body>

    @vite(['resources/js/help-popup.js'])

</html>

