<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-4 px-6">
                    <h1 class="text-lg font-semibold mb-2">{{ __('Classic Piggy Bank') }}</h1>
                    <p class="text-gray-600 mb-8">{{ __('classic_form_subtitle') }}</p>

                    @guest
                        <div class="mb-6 w-full rounded-md bg-blue-50 p-4 text-sm text-blue-800 shadow-md border border-blue-200">
                            {{ __('classic_form_guest_banner') }}
                        </div>
                    @endguest

                    <form id="classicForm" method="POST"
                          action="{{ auth()->check() ? localizedRoute('localized.create-piggy-bank.classic.store') : '#' }}">
                        @csrf

                        {{-- Name (required) --}}
                        <div class="mb-8 mt-6">
                            <x-input-label for="name" class="font-semibold text-gray-900">
                                {{ __('1. Name your piggy bank (required field)') }}
                            </x-input-label>
                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-2 block w-full"
                                required
                                maxlength="255"
                                autocomplete="on"
                                :value="old('name')"
                                placeholder="{{ __('classic_form_name_placeholder') }}"
                            />
                            <p id="name-count" class="text-gray-400 text-xs mt-1">0 / 255</p>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Currency (required) --}}
                        <div class="mb-8">
                            <x-input-label for="currency" class="font-semibold text-gray-900">
                                {{ __('2. Currency (required field)') }}
                            </x-input-label>
                            <select
                                id="currency"
                                name="currency"
                                class="mt-2 block w-full sm:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-xs cursor-pointer"
                            >
                                @foreach(config('app.currencies') as $code => $currencyData)
                                    <option
                                        value="{{ $code }}"
                                        {{ (auth()->check() ? auth()->user()->currency : session('currency')) === $code ? 'selected' : '' }}
                                    >
                                        {{ $code }} - {{ __($currencyData['name']) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                        </div>

                        {{-- Link (optional) --}}
                        <div class="mb-8">
                            <x-input-label for="link" class="font-semibold text-gray-900">
                                {{ __('3. Link (optional field)') }}
                            </x-input-label>
                            <div class="flex flex-col md:flex-row gap-4 mt-2">
                                <div class="grow">
                                    <x-text-input
                                        id="link"
                                        name="link"
                                        type="url"
                                        class="mt-1 block w-full"
                                        maxlength="255"
                                        :value="old('link')"
                                        placeholder="{{ __('step1_link_placeholder') }}"
                                    />
                                    <p id="link-count" class="text-gray-400 text-xs mt-1">0 / 255</p>
                                    <x-input-error :messages="$errors->get('link')" class="mt-2" />
                                </div>

                                <div class="w-full md:w-48 mt-1">
                                    <div class="aspect-square h-32 md:aspect-auto md:h-48 relative overflow-hidden rounded-lg shadow-xs bg-gray-50">
                                        <div
                                            id="preview-loading"
                                            class="absolute inset-0 bg-white/80 flex items-center justify-center opacity-0 invisible transition-all duration-300 z-20"
                                        >
                                            <div class="animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"></div>
                                        </div>

                                        <div
                                            id="preview-error"
                                            class="absolute inset-0 bg-white/80 flex items-center justify-center opacity-0 invisible transition-all duration-300 z-20"
                                        >
                                            <span class="text-red-500 text-sm px-4 text-center">
                                                {{ __('Could not load image preview') }}
                                            </span>
                                        </div>

                                        <div class="relative w-full h-full">
                                            <img
                                                id="preview-image-current"
                                                src="{{ asset('images/default_piggy_bank.png') }}"
                                                alt="Current preview"
                                                class="absolute inset-0 w-full h-full object-contain transition-opacity duration-500"
                                            />
                                            <img
                                                id="preview-image-next"
                                                src="{{ asset('images/default_piggy_bank.png') }}"
                                                alt="Next preview"
                                                class="absolute inset-0 w-full h-full object-contain opacity-0 transition-opacity duration-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Details (optional) --}}
                        <div class="mb-8">
                            <x-input-label for="details" class="font-semibold text-gray-900">
                                {{ __('4. Details (optional field)') }}
                            </x-input-label>
                            <textarea
                                id="details"
                                name="details"
                                rows="4"
                                maxlength="5000"
                                class="mt-2 block w-full rounded-md shadow-xs border-gray-300 focus:ring-3 focus:ring-opacity-50"
                                placeholder="{{ __('classic_form_details_placeholder') }}"
                            >{{ old('details') }}</textarea>
                            <p id="details-count" class="text-gray-400 text-xs mt-1">0 / 5000</p>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>
                    </form>

                    {{-- Max active PBs check --}}
                    @auth
                        @if($activePiggyBanksCount >= $maxActivePiggyBanks)
                            <div class="mb-6 w-full rounded-md bg-yellow-50 p-4 text-sm text-yellow-800 shadow-md border border-yellow-200">
                                {{ __("You've reached the maximum limit of :limit active or paused piggy banks. To create this piggy bank, you can either save it as a draft using the 'Save as Draft' button above, or ", ['limit' => $maxActivePiggyBanks]) }}
                                <a href="{{ localizedRoute('localized.piggy-banks.index') }}" class="underline font-medium">{{ __('cancel some of your active or paused piggy banks') }}</a>.
                            </div>
                        @endif
                    @endauth

                    @auth
                        <p class="text-sm text-gray-500 mt-6">
                            {{ __('classic_switch_to_scheduled_hint') }}
                            <a href="{{ localizedRoute('localized.create-piggy-bank.clear-preference') }}"
                               class="text-indigo-600 hover:text-indigo-800 underline">
                                {{ __('classic_switch_to_scheduled_link') }}
                            </a>
                        </p>
                    @endauth

                    {{-- Action Buttons --}}
                    <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-between sm:space-y-0 mt-6">
                        <div x-data="{ showConfirmCancel: false }">
                            <x-danger-button @click="showConfirmCancel = true" class="w-[200px] sm:w-auto justify-center sm:justify-start cursor-pointer">
                                {{ __('Cancel') }}
                            </x-danger-button>

                            <x-confirmation-dialog>
                                <x-slot:title>
                                    {{ __('Are you sure you want to cancel?') }}
                                </x-slot>

                                <x-slot:actions>
                                    <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                        <form action="{{ localizedRoute('localized.create-piggy-bank.cancel') }}" method="POST" class="block">
                                            @csrf
                                            <x-danger-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start cursor-pointer">
                                                {{ __('Yes, cancel') }}
                                            </x-danger-button>
                                        </form>

                                        <x-secondary-button
                                            @click="showConfirmCancel = false"
                                            class="w-[200px] sm:w-auto justify-center sm:justify-start cursor-pointer"
                                        >
                                            {{ __('No, continue') }}
                                        </x-secondary-button>
                                    </div>
                                </x-slot:actions>
                            </x-confirmation-dialog>
                        </div>

                        @auth
                            <x-primary-button
                                form="classicForm"
                                type="submit"
                                class="w-[200px] sm:w-auto justify-center sm:justify-start cursor-pointer"
                                :disabled="$activePiggyBanksCount >= $maxActivePiggyBanks"
                            >
                                {{ __('Create Piggy Bank') }}
                            </x-primary-button>
                        @endauth

                        @guest
                            <div class="flex flex-col sm:flex-row gap-3">
                                <form method="POST" action="{{ localizedRoute('localized.create-piggy-bank.classic.stash') }}">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="login">
                                    <input type="hidden" name="name" id="stash_name_login">
                                    <input type="hidden" name="currency" id="stash_currency_login">
                                    <input type="hidden" name="link" id="stash_link_login">
                                    <input type="hidden" name="details" id="stash_details_login">
                                    <x-secondary-button type="submit" onclick="syncStashFields('login')" class="w-[200px] sm:w-auto justify-center sm:justify-start cursor-pointer">
                                        {{ __('Log in to create') }}
                                    </x-secondary-button>
                                </form>

                                <form method="POST" action="{{ localizedRoute('localized.create-piggy-bank.classic.stash') }}">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="register">
                                    <input type="hidden" name="name" id="stash_name_register">
                                    <input type="hidden" name="currency" id="stash_currency_register">
                                    <input type="hidden" name="link" id="stash_link_register">
                                    <input type="hidden" name="details" id="stash_details_register">
                                    <x-primary-button type="submit" onclick="syncStashFields('register')" class="w-[200px] sm:w-auto justify-center sm:justify-start cursor-pointer">
                                        {{ __('Sign up to create') }}
                                    </x-primary-button>
                                </form>
                            </div>
                        @endguest
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function syncStashFields(target) {
            document.getElementById('stash_name_' + target).value = document.getElementById('name').value;
            document.getElementById('stash_currency_' + target).value = document.getElementById('currency').value;
            document.getElementById('stash_link_' + target).value = document.getElementById('link').value;
            document.getElementById('stash_details_' + target).value = document.getElementById('details').value;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Character counters
            [
                { id: 'name', max: 255 },
                { id: 'link', max: 255 },
                { id: 'details', max: 5000 },
            ].forEach(function(field) {
                const input = document.getElementById(field.id);
                const counter = document.getElementById(field.id + '-count');
                if (input && counter) {
                    counter.textContent = input.value.length + ' / ' + field.max;
                    input.addEventListener('input', function() {
                        counter.textContent = this.value.length + ' / ' + field.max;
                    });
                }
            });

            // Link preview
            const linkInput = document.getElementById('link');
            const currentImage = document.getElementById('preview-image-current');
            const nextImage = document.getElementById('preview-image-next');
            const loadingElement = document.getElementById('preview-loading');
            const errorElement = document.getElementById('preview-error');
            const defaultImage = '{{ asset("images/default_piggy_bank.png") }}';
            let debounceTimer;

            function showLoading() {
                if (loadingElement) loadingElement.classList.remove('opacity-0', 'invisible');
                if (currentImage) currentImage.classList.add('opacity-50');
                if (nextImage) nextImage.classList.add('opacity-0');
                if (errorElement) errorElement.classList.add('opacity-0', 'invisible');
            }

            function hideLoading() {
                if (loadingElement) loadingElement.classList.add('opacity-0', 'invisible');
                if (currentImage) currentImage.classList.remove('opacity-50');
            }

            function showError() {
                if (errorElement) errorElement.classList.remove('opacity-0', 'invisible');
            }

            function hideError() {
                if (errorElement) errorElement.classList.add('opacity-0', 'invisible');
            }

            function updatePreviewImage(newImageUrl) {
                const tempImage = new Image();
                tempImage.onload = function() {
                    if (nextImage) {
                        nextImage.src = newImageUrl;
                        nextImage.classList.remove('opacity-0');
                        if (currentImage) currentImage.classList.add('opacity-0');
                    }
                    setTimeout(function() {
                        if (currentImage) {
                            currentImage.src = newImageUrl;
                            currentImage.classList.remove('opacity-0');
                        }
                        if (nextImage) nextImage.classList.add('opacity-0');
                        hideLoading();
                        hideError();
                    }, 500);
                };
                tempImage.onerror = function() {
                    showError();
                    hideLoading();
                };
                tempImage.src = newImageUrl;
            }

            if (linkInput) {
                linkInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const url = this.value.trim();

                    if (!url) {
                        hideLoading();
                        hideError();
                        if (currentImage) currentImage.src = defaultImage;
                        if (nextImage) nextImage.src = defaultImage;
                        return;
                    }

                    showLoading();

                    debounceTimer = setTimeout(function() {
                        fetch('/api/create-piggy-bank/link-preview', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ url: url })
                        })
                        .then(function(response) {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(function(data) {
                            if (data.preview && data.preview.image) {
                                updatePreviewImage(data.preview.image);
                            } else {
                                throw new Error('No preview image available');
                            }
                        })
                        .catch(function() {
                            showError();
                            updatePreviewImage(defaultImage);
                        });
                    }, 500);
                });
            }
        });
    </script>
</x-app-layout>
