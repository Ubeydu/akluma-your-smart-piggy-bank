<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-10 text-gray-800">
        <h1 class="text-2xl font-bold mb-6">{{ __('privacy.title') }}</h1>

        <p class="mb-4">{{ __('privacy.updated') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.collect_title') }}</h2>
        <p class="mb-4">{{ __('privacy.collect_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.why_title') }}</h2>
        <p class="mb-4">{{ __('privacy.why_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.emails_title') }}</h2>
        <p class="mb-4">{{ __('privacy.emails_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.cookies_title') }}</h2>
        <p class="mb-4">{{ __('privacy.cookies_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.thirdparties_title') }}</h2>
        <p class="mb-4">{{ __('privacy.thirdparties_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.children_title') }}</h2>
        <p class="mb-4">{{ __('privacy.children_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.storage_title') }}</h2>
        <p class="mb-4">{{ __('privacy.storage_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.security_title') }}</h2>
        <p class="mb-4">{{ __('privacy.security_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.rights_title') }}</h2>
        <p class="mb-4">{{ __('privacy.rights_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.changes_title') }}</h2>
        <p class="mb-4">{{ __('privacy.changes_text') }}</p>

        <h2 class="font-semibold mt-6 mb-2">{{ __('privacy.contact_title') }}</h2>
        <p class="mb-4">
            {{ __('privacy.contact_intro') }}

            <span class="inline-flex items-center space-x-1">
        <span class="help-email">contact@akluma.com</span>
        <button class="help-copy-btn" title="{{ __('copy_email_title') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
        </button>
    </span>
            {{ __('privacy.contact_linkedin') }}
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
