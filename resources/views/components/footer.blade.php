<footer {{ $attributes->merge(['class' => 'py-16 bg-gray-50']) }}>
    <div class="max-w-2xl mx-auto text-center">
        {{-- Logo --}}
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
            <a href="{{ localizedRoute('localized.about') }}" class="text-gray-500 hover:text-gray-700">{{ __('about.heading') }}</a>
            <span class="text-gray-300">&bull;</span>
            <a href="{{ localizedRoute('localized.terms') }}" class="text-gray-500 hover:text-gray-700">{{ __('terms.title') }}</a>
            <span class="text-gray-300">&bull;</span>
            <a href="{{ localizedRoute('localized.privacy') }}" class="text-gray-500 hover:text-gray-700">{{ __('privacy.title') }}</a>
        </div>

        {{-- Copyright --}}
        <p class="mt-6 text-sm text-gray-400">
            &copy; {{ date('Y') }} {{ __('app_name') }}. {{ __('All rights reserved.') }}
        </p>

        {{-- Dev info (non-production only) --}}
        @if(config('app.env') !== 'production')
            <p class="mt-2 text-xs text-gray-300">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </p>
        @endif
    </div>
</footer>
