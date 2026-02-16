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
        <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
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
