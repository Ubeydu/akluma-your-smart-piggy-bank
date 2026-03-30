@section('title', __('about.page_title') . ' - ' . __('app_name'))
@section('meta_description', __('about.meta_description'))
@section('og_title', __('about.page_title') . ' - ' . __('app_name'))
@section('og_description', __('about.meta_description'))

<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-10 text-gray-800">
        <h1 class="text-2xl font-bold mb-6">{{ __('about.heading') }}</h1>

        <h2 class="font-semibold mt-6 mb-2">{{ __('about.what_title') }}</h2>
        <p class="mb-4">{{ __('about.what_text_1') }}</p>
        <p class="mb-4">{{ __('about.what_text_2') }}</p>
        <p class="mb-4">{{ __('about.what_text_3') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('about.why_title') }}</h2>
        <p class="mb-4">{{ __('about.why_text_1') }}</p>
        <p class="mb-4">{{ __('about.why_text_2') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('about.how_title') }}</h2>
        <ol class="list-decimal list-inside mb-4 space-y-1">
            <li>{{ __('about.how_step_1') }}</li>
            <li>{{ __('about.how_step_2') }}</li>
            <li>{{ __('about.how_step_3') }}</li>
            <li>{{ __('about.how_step_4') }}</li>
        </ol>
        <p class="mb-4">
            {{ __('about.how_cta_text') }}
            <a href="{{ localizedRoute('localized.create-piggy-bank.choose-type') }}"
               class="text-indigo-600 hover:text-indigo-700 font-medium">
                {{ __('about.how_cta_link') }}
            </a>
        </p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('about.who_title') }}</h2>
        <p class="mb-4">{{ __('about.who_text_1') }}</p>
        <p class="mb-4">{{ __('about.who_text_2') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('about.contact_title') }}</h2>
        <p class="mb-4">{{ __('about.contact_text') }}</p>
        <p class="mb-4">
            {{ __('about.contact_email_label') }}
            <span class="inline-flex items-center space-x-1">
                <span class="help-email">contact@akluma.com</span>
                <button class="help-copy-btn" title="{{ __('copy_email_title') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
            </span>
        </p>
        <p class="mb-4">
            LinkedIn:
            <a href="https://www.linkedin.com/in/ubeydullah-kele%C5%9F-2221a915/"
               target="_blank"
               rel="noopener noreferrer"
               class="text-blue-500 hover:underline">
                Ubeydullah Keleş
            </a>
        </p>
    </div>
</x-app-layout>
