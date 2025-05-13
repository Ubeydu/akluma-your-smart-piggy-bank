<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-10 text-gray-800">
        <h1 class="text-2xl font-bold mb-6">{{ __('terms.title') }}</h1>

        <p class="mb-4">{{ __('terms.updated') }}</p>

        <p class="mb-4">{{ __('terms.intro') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('terms.can_do_title') }}</h2>
        <p class="mb-4">{{ __('terms.can_do_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('terms.cant_do_title') }}</h2>
        <p class="mb-4">{{ __('terms.cant_do_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('terms.data_title') }}</h2>
        <p class="mb-4">{{ __('terms.data_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('terms.guarantee_title') }}</h2>
        <p class="mb-4">{{ __('terms.guarantee_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('terms.changes_title') }}</h2>
        <p class="mb-4">{{ __('terms.changes_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('terms.contact_title') }}</h2>
        <p class="mb-4">
            {{ __('terms.contact_intro') }}

            <span class="inline-flex items-center space-x-1">
        <span class="help-email">contact@akluma.com</span>
        <button class="help-copy-btn" title="{{ __('copy_email_title') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
        </button>
    </span>

            {{ __('terms.contact_linkedin') }}
            <a
                href="https://www.linkedin.com/in/ubeydullah-kele%C5%9F-2221a915/"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-500 hover:underline"
            >
                Ubeydullah Keleş
            </a>.
        </p>
    </div>
</x-app-layout>
