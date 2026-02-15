<x-guest-layout>
    <div x-data="{
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
        }
    }">
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('localized.password.email.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus x-bind:disabled="cooldown > 0" x-bind:class="{ 'bg-gray-100 text-gray-500': cooldown > 0 }" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button
                    x-bind:disabled="cooldown > 0"
                    x-bind:class="{ 'opacity-50 cursor-not-allowed': cooldown > 0 }"
                >
                    <span x-show="cooldown <= 0">{{ __('Email Password Reset Link') }}</span>
                    <span x-show="cooldown > 0" x-cloak>{{ __('Resend available in') }} <span x-text="cooldownDisplay"></span></span>
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
