@section('title', __('Register') . ' - ' . __('app_name'))

<x-guest-layout>
    <div class="mb-6 p-4 bg-indigo-50 border-l-4 border-indigo-400 text-indigo-700 rounded-sm">
        <p class="text-sm">
            {!! __('Currently, our website is completely free.<br><br>We\'re launching an MVP (Minimum Viable Product) first to gauge how useful this tool is for our users. If we see strong engagement, we may introduce premium features later.<br><br>But for now—enjoy, save money, and help us grow!') !!}
        </p>

        <p class="mt-4 text-sm text-indigo-600">
            &mdash;
            <a href="https://www.linkedin.com/in/ubeydullah-kele%C5%9F-2221a915/" target="_blank" rel="noopener noreferrer" class="text-indigo-700 hover:underline font-medium">
                Ubeydullah Keleş, {{ __('founder') }}
            </a>
        </p>
    </div>

    <!-- Google Sign-In -->
    <a href="{{ route('auth.google.redirect') }}"
       id="google-register-btn"
       class="flex items-center justify-center w-full gap-3 px-4 py-2.5 border border-gray-300 rounded-md bg-white hover:bg-gray-50 transition-colors cursor-pointer">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        <span class="text-sm font-medium text-gray-700">{{ __('Sign in with Google') }}</span>
    </a>

    <p class="mt-2 text-xs text-center text-gray-500">
        {!! __('By continuing with Google, you agree to our :terms and :privacy.', [
            'terms' => '<a href="' . route('localized.terms.' . app()->getLocale(), ['locale' => app()->getLocale()]) . '" class="underline hover:text-gray-700">' . __('terms.title') . '</a>',
            'privacy' => '<a href="' . route('localized.privacy.' . app()->getLocale(), ['locale' => app()->getLocale()]) . '" class="underline hover:text-gray-700">' . __('privacy.title') . '</a>',
        ]) !!}
    </p>

    <script>
        document.getElementById('google-register-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const lang = document.getElementById('language') ? document.getElementById('language').value : '{{ session('locale', app()->getLocale()) }}';
            window.location.href = '{{ route('auth.google.redirect') }}' + '?timezone=' + encodeURIComponent(tz) + '&language=' + encodeURIComponent(lang);
        });
    </script>

    <!-- Separator -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-white px-4 text-gray-500">{{ __('Or register with email') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ localizedRoute('localized.register.store') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="flex justify-between items-center">
                <x-input-label for="password" :value="__('Password')" />
                <span class="text-sm text-gray-600">{{ __('Minimum 8 characters.') }}</span>
            </div>

            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Policy Checkboxes -->
        <div class="mt-4">
            <label class="flex items-start space-x-2">
                <input type="checkbox" name="terms" id="terms"
                       class="mt-1 border-gray-300 focus:ring-indigo-500 rounded"
                       onchange="toggleRegisterButton()" />
                <span class="text-sm text-gray-700">
            {!! __('auth.accept_terms', ['url' => route('localized.terms.' . app()->getLocale(), ['locale' => app()->getLocale()])]) !!}
        </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="mt-2">
            <label class="flex items-start space-x-2">
                <input type="checkbox" name="privacy" id="privacy"
                       class="mt-1 border-gray-300 focus:ring-indigo-500 rounded"
                       onchange="toggleRegisterButton()" />
                <span class="text-sm text-gray-700">
            {!! __('auth.accept_privacy', ['url' => route('localized.privacy.' . app()->getLocale(), ['locale' => app()->getLocale()])]) !!}
        </span>
            </label>
            <x-input-error :messages="$errors->get('privacy')" class="mt-2" />
        </div>



        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ localizedRoute('localized.login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4" id="register-btn" disabled>
                {{ __('Register') }}
            </x-primary-button>
        </div>

        <input type="hidden" id="timezone" name="timezone" value="">
        <script>
            document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
        </script>


        <input type="hidden" name="language" id="language" value="{{ session('locale') ?? app()->getLocale() }}">
        <script>
            // Only override with browser language if locale wasn't manually selected
            // Check if the locale was set via the language switcher
            const hasManuallySelectedLanguage = {{ session()->has('locale') ? 'true' : 'false' }};

            if (!hasManuallySelectedLanguage) {
                // Get browser language (like "en-US" or "fr-FR")
                let browserLang = navigator.language;
                // Extract just the 2-character language code
                let langCode = browserLang.split('-')[0];
                // Check if it's a supported language
                const supportedLanguages = ['en', 'fr', 'tr'];
                if (supportedLanguages.includes(langCode)) {
                    document.getElementById('language').value = langCode;
                }
            }
        </script>


    </form>

    @vite(['resources/js/register-policy-check.js'])

</x-guest-layout>
