<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('localized.password.email.' . app()->getLocale(), ['locale' => app()->getLocale()]) }}" id="password-reset-form">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button id="submit-btn">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('password-reset-form');
            const submitBtn = document.getElementById('submit-btn');
            const emailInput = document.getElementById('email');
            
            // Check if user is currently throttled
            function checkThrottleStatus() {
                const email = emailInput.value;
                if (!email) return;
                
                const throttleKey = 'password_reset_throttle_' + email;
                const throttleData = localStorage.getItem(throttleKey);
                
                if (throttleData) {
                    const data = JSON.parse(throttleData);
                    const now = new Date().getTime();
                    const timeLeft = data.expiry - now;
                    
                    if (timeLeft > 0) {
                        startCountdown(timeLeft);
                        return true;
                    } else {
                        localStorage.removeItem(throttleKey);
                    }
                }
                return false;
            }
            
            // Start countdown and disable button
            function startCountdown(timeLeft) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-25');
                
                const interval = setInterval(() => {
                    const minutes = Math.floor(timeLeft / 60000);
                    const seconds = Math.floor((timeLeft % 60000) / 1000);
                    
                    submitBtn.textContent = `{{ __('Wait') }} ${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    timeLeft -= 1000;
                    
                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-25');
                        submitBtn.textContent = '{{ __('Email Password Reset Link') }}';
                        
                        // Remove throttle data from localStorage
                        const email = emailInput.value;
                        if (email) {
                            localStorage.removeItem('password_reset_throttle_' + email);
                        }
                    }
                }, 1000);
            }
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                const email = emailInput.value;
                if (!email) return;
                
                // Store throttle data in localStorage
                const throttleKey = 'password_reset_throttle_' + email;
                const expiry = new Date().getTime() + (5 * 60 * 1000); // 5 minutes
                localStorage.setItem(throttleKey, JSON.stringify({expiry: expiry}));
            });
            
            // Check throttle status when email changes
            emailInput.addEventListener('blur', checkThrottleStatus);
            
            // Check throttle status on page load
            checkThrottleStatus();
        });
    </script>
</x-guest-layout>
