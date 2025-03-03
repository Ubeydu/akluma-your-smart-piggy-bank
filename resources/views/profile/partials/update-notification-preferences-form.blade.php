<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Notification Preferences') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Manage how you receive notifications about your piggy banks.") }}
        </p>
    </header>

    <form id="preferences-form" method="post" action="{{ route('preferences.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <x-input-label for="email_notifications" :value="__('Email Reminders')" />
                    <p class="text-sm text-gray-600">{{ __('Receive notifications via email') }}</p>
                </div>
                <input id="email_notifications" name="email_notifications" type="checkbox" value="true" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded auto-save-pref"
                    {{ isset(Auth::user()->notification_preferences['email']['enabled']) && Auth::user()->notification_preferences['email']['enabled'] ? 'checked' : '' }}>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <x-input-label for="sms_notifications" :value="__('SMS Reminders')" />
                    <p class="text-sm text-gray-600">{{ __('Receive notifications via SMS') }}</p>
                </div>
                <input id="sms_notifications" name="sms_notifications" type="checkbox" value="true" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded auto-save-pref"
                    {{ isset(Auth::user()->notification_preferences['sms']['enabled']) && Auth::user()->notification_preferences['sms']['enabled'] ? 'checked' : '' }}>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <x-input-label for="push_notifications" :value="__('Push Notifications')" />
                    <p class="text-sm text-gray-600">{{ __('Receive notifications via push notifications') }}</p>
                </div>
                <input id="push_notifications" name="push_notifications" type="checkbox" value="true" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded auto-save-pref"
                    {{ isset(Auth::user()->notification_preferences['push']['enabled']) && Auth::user()->notification_preferences['push']['enabled'] ? 'checked' : '' }}>
            </div>
        </div>

        <div id="save-status" class="text-sm text-gray-600 hidden">
            <span class="saving-indicator">{{ __('Saving...') }}</span>
            <span class="success-indicator hidden">{{ __('Preferences saved') }}</span>
            <span class="error-indicator hidden">{{ __('Error saving preferences') }}</span>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.auto-save-pref');
            const form = document.getElementById('preferences-form');
            const saveStatus = document.getElementById('save-status');
            const savingIndicator = saveStatus.querySelector('.saving-indicator');
            const successIndicator = saveStatus.querySelector('.success-indicator');
            const errorIndicator = saveStatus.querySelector('.error-indicator');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Show saving indicator
                    saveStatus.classList.remove('hidden');
                    savingIndicator.classList.remove('hidden');
                    successIndicator.classList.add('hidden');
                    errorIndicator.classList.add('hidden');

                    // Submit the form using fetch API
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            savingIndicator.classList.add('hidden');

                            if (data.success) {
                                successIndicator.classList.remove('hidden');
                                setTimeout(() => {
                                    saveStatus.classList.add('hidden');
                                }, 2000);
                            } else {
                                errorIndicator.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            savingIndicator.classList.add('hidden');
                            errorIndicator.classList.remove('hidden');
                            console.error('Error:', error);
                        });
                });
            });
        });
    </script>
</section>
