<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
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

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
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
</x-guest-layout>
