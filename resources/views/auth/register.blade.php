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


    <form method="POST" action="{{ route('localized.register', ['locale' => app()->getLocale()]) }}">
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
            {!! __('auth.accept_terms', ['url' => route('localized.terms', ['locale' => app()->getLocale()])]) !!}
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
            {!! __('auth.accept_privacy', ['url' => route('localized.privacy', ['locale' => app()->getLocale()])]) !!}
        </span>
            </label>
            <x-input-error :messages="$errors->get('privacy')" class="mt-2" />
        </div>



        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('localized.login', ['locale' => app()->getLocale()]) }}">
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
