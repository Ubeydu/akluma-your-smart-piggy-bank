<x-guest-layout>
    <div x-data="{
        editingEmail: {{ $errors->has('email') ? 'true' : 'false' }},
        newEmail: '{{ old('email', auth()->user()->email) }}',
        cooldown: {{ (int) session('cooldown', 0) }},
        init() {
            if (this.cooldown > 0) {
                this.startTimer();
            }
        },
        startTimer() {
            const interval = setInterval(() => {
                this.cooldown--;
                if (this.cooldown <= 0) {
                    this.cooldown = 0;
                    clearInterval(interval);
                }
            }, 1000);
        },
        get cooldownDisplay() {
            const m = Math.floor(this.cooldown / 60);
            const s = this.cooldown % 60;
            return m + ':' + String(s).padStart(2, '0');
        },
        get emailValid() {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.newEmail);
        }
    }">
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

            {{-- Wrong email? toggle --}}
            <p class="mt-2" x-show="!editingEmail">
                <button type="button" @click="editingEmail = true" class="underline hover:text-gray-900 cursor-pointer">
                    {{ __('Wrong email?') }}
                </button>
            </p>

            {{-- Inline email update form --}}
            <div x-show="editingEmail" x-cloak class="mt-4">
                <form method="POST" action="{{ route('localized.verification.update-email.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}">
                    @csrf
                    @method('PATCH')
                    <div class="flex gap-2">
                        <x-text-input
                            id="email"
                            name="email"
                            type="email"
                            x-model="newEmail"
                            required
                            autofocus
                            class="flex-1 text-sm"
                            placeholder="{{ __('New email address') }}"
                        />
                        <x-primary-button class="shrink-0 text-sm" x-bind:disabled="!emailValid" x-bind:class="{ 'opacity-50 cursor-not-allowed': !emailValid }">
                            {{ __('Update & Resend') }}
                        </x-primary-button>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-left" />
                    <button type="button" @click="editingEmail = false" class="mt-2 text-xs underline hover:text-gray-900 cursor-pointer">
                        {{ __('Cancel') }}
                    </button>
                </form>
            </div>
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
                <x-primary-button
                    class="w-full justify-center"
                    x-bind:disabled="cooldown > 0"
                    x-bind:class="{ 'opacity-50 cursor-not-allowed': cooldown > 0 }"
                >
                    <span x-show="cooldown <= 0">{{ __('Resend Verification Email') }}</span>
                    <span x-show="cooldown > 0" x-cloak>{{ __('Resend available in') }} <span x-text="cooldownDisplay"></span></span>
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
    </div>
</x-guest-layout>
