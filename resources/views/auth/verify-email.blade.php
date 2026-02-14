<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('localized.verification.send.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}">
                @csrf

                <div>
                    <x-primary-button>
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('localized.logout.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>

        <a href="{{ localizedRoute('localized.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
            {{ __('Return to home page') }}
        </a>
    </div>
</x-guest-layout>
