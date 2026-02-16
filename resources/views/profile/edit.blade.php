<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (auth()->user()->google_id)
                <div class="p-4 sm:p-8 bg-white shadow-sm rounded-lg">
                    <div class="max-w-xl flex items-center gap-3">
                        <x-google-icon class="w-5 h-5 shrink-0" />
                        <p class="text-sm text-gray-600">
                            {{ __('Your account is connected to Google.') }}
                            @if (! auth()->user()->hasPassword())
                                {{ __('You can set a password anytime using the "Forgot your password?" link on the login page.') }}
                            @endif
                        </p>
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow-sm rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-notification-preferences-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            @if (auth()->user()->hasPassword())
                <div class="p-4 sm:p-8 bg-white shadow-sm rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow-sm rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
