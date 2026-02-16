@section('title', __('Login') . ' - ' . __('app_name'))

<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('error'))
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Google Sign-In -->
    <a href="{{ route('auth.google.redirect', ['timezone' => '', 'language' => session('locale', app()->getLocale())]) }}"
       id="google-login-btn"
       class="flex items-center justify-center w-full gap-3 px-4 py-2.5 border border-gray-300 rounded-md bg-white hover:bg-gray-50 transition-colors cursor-pointer">
        <x-google-icon />
        <span class="text-sm font-medium text-gray-700">{{ __('Sign in with Google') }}</span>
    </a>

    <script>
        document.getElementById('google-login-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const lang = '{{ session('locale', app()->getLocale()) }}';
            window.location.href = '{{ route('auth.google.redirect') }}' + '?timezone=' + encodeURIComponent(tz) + '&language=' + encodeURIComponent(lang);
        });
    </script>

    <!-- Separator -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-white px-4 text-gray-500">{{ __('Or sign in with email') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ localizedRoute('localized.login.store') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded-sm border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500 cursor-pointer" name="remember">
                <span class="ms-2 mr-4 text-sm text-gray-600 cursor-pointer">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-end mt-4 gap-2">
            <x-primary-button class="mb-3">
                {{ __('Log in') }}
            </x-primary-button>

            <div class="w-full flex flex-wrap justify-end gap-2">
                @if (Route::has('localized.register.' . app()->getLocale()))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                       href="{{ route('localized.register.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}">
                        {{ __('Never registered?') }}
                    </a>
                    <span class="text-gray-600">{{ __('or') }}</span>
                @endif

                    @if (Route::has('localized.password.request.' . app()->getLocale()))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                       href="{{ route('localized.password.request.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
        </div>

    </form>
</x-guest-layout>
