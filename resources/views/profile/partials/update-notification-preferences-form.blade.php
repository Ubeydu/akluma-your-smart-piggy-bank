<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Notification Preferences') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Manage how you receive notifications about your piggy banks.") }}
        </p>
    </header>

    <form id="preferences-form" method="post" action="{{ route('localized.preferences.update', ['locale' => app()->getLocale()]) }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <label for="email_notifications" class="text-sm text-gray-900 font-medium">
                        {{ __('Receive notifications via email') }}
                    </label>
                </div>
                <input id="email_notifications" name="email_notifications" type="checkbox" value="true" class="w-4 h-4 text-blue-600 focus:ring-blue-500 checked:bg-blue-600 border-gray-300 rounded-sm auto-save-pref cursor-pointer"
                    {{ isset(Auth::user()->notification_preferences['email']['enabled']) && Auth::user()->notification_preferences['email']['enabled'] ? 'checked' : '' }}>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <label for="sms_notifications" class="text-sm text-gray-600 font-medium">
                        {{ __('Receive notifications via SMS') }}
                    </label>
                    <span class="inline-block bg-linear-to-r from-yellow-400 to-orange-500 text-xs text-gray-600 font-medium px-3 py-1 min-w-[120px] text-center rounded-full shadow-md" style="background: linear-gradient(to right, #FBBF24, #F97316);">
                        {{ __('Coming Soon ✨') }}
                    </span>
                </div>
                <input id="sms_notifications" name="sms_notifications" type="checkbox" value="true" disabled class="w-4 h-4 bg-gray-100 text-gray-400 focus:ring-blue-500 checked:bg-blue-600 border-gray-300 rounded-sm cursor-not-allowed">
            </div>


            <div class="flex items-center justify-between">
                <div>
                    <label for="push_notifications" class="text-sm text-gray-600 font-medium">
                        {{ __('Receive notifications via push notifications') }}
                    </label>
                    <span class="inline-block bg-linear-to-r from-yellow-400 to-orange-500 text-gray-600 text-xs font-medium px-2 py-1 rounded-full shadow-md" style="background: linear-gradient(to right, #FBBF24, #F97316);">
                        {{ __('Coming Soon ✨') }}
                    </span>
                </div>
                <input id="push_notifications" name="push_notifications" type="checkbox" value="true" disabled class="w-4 h-4 bg-gray-100 text-gray-400 focus:ring-blue-500 checked:bg-blue-600 border-gray-300 rounded-sm cursor-not-allowed">
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
            const checkboxes = document.querySelectorAll('.auto-save-pref:not(:disabled)');
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
