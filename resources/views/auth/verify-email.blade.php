<x-guest-layout>
    {{-- Content block --}}
    <div class="text-center text-sm text-gray-600 mb-6">
        <div class="flex justify-center mb-4">
            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
        </div>

        <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Check your email') }}</h2>

        <p>
            {{ __('We sent a verification link to') }}
            <span class="font-semibold">{{ auth()->user()->email }}</span>
            — {{ __("don't see it? Check your spam or junk folder.") }}
        </p>
    </div>

    {{-- Success message --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 text-center">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    {{-- Action block --}}
    <div class="text-center text-sm text-gray-600">
        <form method="POST" action="{{ route('localized.verification.send.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}" class="mb-4">
            @csrf
            <x-primary-button class="w-full justify-center">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <div class="flex items-center justify-center gap-3 mb-4">
            <a href="{{ localizedRoute('localized.dashboard') }}" class="underline hover:text-gray-900">
                {{ __('Return to home page') }}
            </a>
            <span class="text-gray-300">·</span>
            <form method="POST" action="{{ route('localized.logout.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}" class="inline">
                @csrf
                <button type="submit" class="underline hover:text-gray-900 cursor-pointer">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>

        <p>{{ __('Still having trouble? Contact us at :email', ['email' => 'contact@akluma.com']) }}</p>
    </div>
</x-guest-layout>
