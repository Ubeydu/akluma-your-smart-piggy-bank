<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="py-4 px-6">
                    <h1 class="text-lg font-semibold mb-4">{{ __('Step 1 of 3') }}</h1>
                    <p class="text-gray-600 mb-6">{{ __('Provide information about your goal') }}</p>

                    <form  id="mainForm" method="POST" action="{{ route('create-piggy-bank.step-2') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name">
                                {!! __('1. I am saving for a (required field)') !!}
                            </x-input-label>
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required maxlength="255" autocomplete="on" :value="old('name', session('pick_date_step1.name'))" />
                            <p id="name-count" class="text-gray-500 text-sm mt-1">0 / 255</p>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <x-input-label for="price_whole"> {!! __('2. Price of the item (required field)') !!} </x-input-label>
                            <div class="flex gap-2 items-start mt-1">
                                <!-- Whole number part -->
                                <div class="flex-1 min-w-0">
                                    <x-text-input
                                        id="price_whole"
                                        name="price_whole"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[1-9][0-9]{2,9}"
                                        min="100"
                                        :value="old('price_whole', session('pick_date_step1.price') ? explode('.', session('pick_date_step1.price')->getAmount())[0] : '')"
                                        onkeypress="return (function(evt) {
                                            const value = this.value;

                                            // Check for non-numeric input
                                            if (!/[0-9]/.test(evt.key)) {
                                                const errorDiv = document.getElementById('price_whole_error_numbers');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            // Check for initial zero
                                            if (value === '' && evt.key === '0') {
                                                const errorDiv = document.getElementById('price_whole_error_numbers');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            // Check length
                                            if (value.length >= 10) {
                                                const errorDiv = document.getElementById('price_whole_error_length');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            return true;
                                        }).call(this, window.event || arguments[0])"
                                        class="block w-full"
                                        required
                                        oninput="updateFormattedPrice(this.value, 'formatted_price');"
                                        onpaste="return (function(evt) {
                                            const pastedData = (evt.clipboardData || window.clipboardData).getData('text');

                                            // Check for non-numeric input
                                            if (!/^[0-9]+$/.test(pastedData)) {
                                                const errorDiv = document.getElementById('price_whole_error_numbers');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            // Check length
                                            if (pastedData.length > 10) {
                                                const errorDiv = document.getElementById('price_whole_error_length');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            return true;
                                        }).call(this, window.event || arguments[0])"
                                    />

                                    <div id="price_whole_error_numbers" class="text-red-500 text-sm mt-1 hidden">
                                        {{ __('price_whole_numbers_only') }}
                                    </div>
                                    <div id="price_whole_error_length" class="text-red-500 text-sm mt-1 hidden">
                                        {{ __('price_whole_length_exceeded') }}
                                    </div>

                                </div>


                                @if( App\Helpers\CurrencyHelper::hasDecimalPlaces(session('currency', config('app.default_currency'))))
                                <div class="flex items-center mt-2">
                                    <span class="text-lg">.</span>
                                </div>

                                <!-- Decimal/cents part -->
                                <div class="w-12">
                                    <x-text-input
                                        id="price_cents"
                                        name="price_cents"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="\d{1,2}"
                                        onfocus="this.dataset.cleared = 'false';"
                                        oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2);"
                                        onblur="if (this.value === '') this.value = '00';"
                                        :value="session('pick_date_step1.price') ? (explode('.', session('pick_date_step1.price')->getAmount())[1] ?? '00') : '00'"
                                        class="block w-full"
                                        required
                                    />
                                </div>
                                @endif

                                <!-- Currency -->
                                <select
                                    id="currency"
                                    name="currency"
                                    class="block w-24 text-center border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    onchange="clearFormAndSwitchCurrency(this.value);"
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


                            </div>

                            <div class="flex items-center gap-4 mt-1">
                                <p class="text-gray-500 text-sm">{{ __('minimum amount 100') }},</p>
{{--                                <p class="text-gray-500 text-sm">{{ __('maximum amount 9,999,999,999') }}</p>--}}
                                <p id="formatted_price" class="text-gray-500 text-sm italic"></p>
                            </div>

                            <!-- Error messages -->
                            <div class="mt-2">
                                <x-input-error :messages="$errors->get('price_whole')" />
                                <x-input-error :messages="$errors->get('price_cents')" />
                            </div>
                        </div>

                        <!-- Link (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="link" :value="__('3. Product link')" />
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-grow">
                                    <x-text-input
                                        id="link"
                                        name="link"
                                        type="url"
                                        class="mt-1 block w-full"
                                        maxlength="1000"
                                        :value="old('link', session('pick_date_step1.link'))"
                                    />
                                    <p id="link-count" class="text-gray-500 text-sm mt-1">0 / 1000</p>
                                    <x-input-error :messages="$errors->get('link')" class="mt-2" />
                                </div>

                                <div class="w-full md:w-48 mt-1">
                                    @php
                                        $preview = session('pick_date_step1.preview');
                                        $imageUrl = $preview['image'] ?? asset('images/default_piggy_bank.png');
                                    @endphp
                                    <div class="aspect-square h-32 md:aspect-auto md:h-48 relative overflow-hidden rounded-lg shadow-sm bg-gray-50">
                                        <!-- Loading spinner overlay -->
                                        <div
                                            id="preview-loading"
                                            class="absolute inset-0 bg-white/80 flex items-center justify-center opacity-0 invisible transition-all duration-300 z-20"
                                        >
                                            <div class="animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"></div>
                                        </div>

                                        <!-- Error message overlay -->
                                        <div
                                            id="preview-error"
                                            class="absolute inset-0 bg-white/80 flex items-center justify-center opacity-0 invisible transition-all duration-300 z-20"
                                        >
                                            <span class="text-red-500 text-sm px-4 text-center">
                                                {{ __('Could not load image preview') }}
                                            </span>
                                        </div>

                                        <!-- Image container -->
                                        <div class="relative w-full h-full">
                                            <img
                                                id="preview-image-current"
                                                src="{{ $imageUrl }}"
                                                alt="Current preview"
                                                class="absolute inset-0 w-full h-full object-contain transition-opacity duration-500"
                                            />
                                            <img
                                                id="preview-image-next"
                                                src="{{ $imageUrl }}"
                                                alt="Next preview"
                                                class="absolute inset-0 w-full h-full object-contain opacity-0 transition-opacity duration-500"
                                            />
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <!-- Details (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="details" :value="__('4. Details')" />
                            <textarea id="details" name="details" rows="4" maxlength="5000" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring focus:ring-opacity-50">{{ old('details', session('pick_date_step1.details')) }}</textarea>
                            <p id="details-count" class="text-gray-500 text-sm mt-1">0 / 5000</p>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>


                        <!-- Starting Amount (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="starting_amount_whole" :value="__('5. I already saved some money')" />
                            <div class="flex gap-2 items-start mt-1">
                                <!-- Whole number part -->
                                <div class="flex-1 min-w-0">
                                    <x-text-input
                                        id="starting_amount_whole"
                                        name="starting_amount_whole"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]{1,9}"
                                        :value="old('starting_amount_whole', session('pick_date_step1.starting_amount') ? explode('.', session('pick_date_step1.starting_amount')->getAmount())[0] : '')"
                                        onkeypress="return (function(evt) {
                                            const value = this.value;

                                            // Check for non-numeric input
                                            if (!/[0-9]/.test(evt.key)) {
                                                const errorDiv = document.getElementById('starting_amount_error_numbers');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            // Check for initial zero
                                            if (value === '' && evt.key === '0') {
                                                const errorDiv = document.getElementById('starting_amount_error_numbers');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            // Check length
                                            if (value.length >= 10) {
                                                const errorDiv = document.getElementById('starting_amount_error_length');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            return true;
                                        }).call(this, window.event || arguments[0])"
                                        onpaste="return (function(evt) {
                                            const pastedData = (evt.clipboardData || window.clipboardData).getData('text');

                                            // Check for non-numeric input
                                            if (!/^[0-9]+$/.test(pastedData)) {
                                                const errorDiv = document.getElementById('starting_amount_error_numbers');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            // Check length
                                            if (pastedData.length > 10) {
                                                const errorDiv = document.getElementById('starting_amount_error_length');
                                                errorDiv.classList.remove('hidden');
                                                setTimeout(() => {
                                                    errorDiv.classList.add('hidden');
                                                }, 3000);
                                                return false;
                                            }

                                            return true;
                                        }).call(this, window.event || arguments[0])"
                                        oninput="
                                            const centsInput = document.getElementById('starting_amount_cents');
                                            if (centsInput) centsInput.value = (this.value === '' || this.value === '0') ? '' : '00';
                                            updateFormattedPrice(this.value, 'formatted_starting_amount_whole');
                                        "
                                        class="block w-full"
                                    />

                                    <div id="starting_amount_error_numbers" class="text-red-500 text-sm mt-1 hidden">
                                        {{ __('starting_amount_numbers_only') }}
                                    </div>
                                    <div id="starting_amount_error_length" class="text-red-500 text-sm mt-1 hidden">
                                        {{ __('starting_amount_length_exceeded') }}
                                    </div>


                                </div>

                                @if( App\Helpers\CurrencyHelper::hasDecimalPlaces(session('currency', config('app.default_currency'))))

                                <div class="flex items-center mt-2">
                                    <span class="text-lg">.</span>
                                </div>

                                <!-- Decimal/cents part -->
                                <div class="w-12">
                                    <x-text-input
                                        id="starting_amount_cents"
                                        name="starting_amount_cents"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="\d{1,2}"
                                        :value="session('pick_date_step1.starting_amount') ? (explode('.', session('pick_date_step1.starting_amount')->getAmount())[1] ?? '00') : ''"
                                        oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2);"
                                        class="block w-full"
                                    />
                                </div>
                                @endif

                                <!-- Currency -->
                                <div class="w-24">
                                    <x-text-input
                                        id="starting_amount_currency"
                                        type="text"
                                        class="block w-full text-center"
                                        readonly
                                        value="{{ auth()->check() ? auth()->user()->currency : session('currency') }}"
                                    />
                                </div>


                            </div>

                            <div class="flex items-center gap-4 mt-1">
{{--                                <p class="text-gray-500 text-sm">{{ __('maximum amount 9,999,999,999') }}</p>--}}
                                <p id="formatted_starting_amount_whole" class="text-gray-500 text-sm italic"></p>
                            </div>



                            <!-- Error messages -->
                            <div class="mt-2">
                                <x-input-error :messages="$errors->get('starting_amount_whole')" />
                                <x-input-error :messages="$errors->get('starting_amount_cents')" />
                                <p id="amount-warning" class="text-red-500 text-sm mt-2 hidden">
                                    {{ __('Starting amount cannot be greater than or equal to the price. Please put a smaller amount.') }}
                                </p>
                                <p id="difference-amount-warning" class="text-red-500 text-sm mt-2 hidden">
                                    {{ __('The difference between starting amount and price cannot be less than 100. Please check.') }}
                                </p>
                            </div>
                        </div>
                    </form>


                    <!-- Action Buttons -->
                    <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-between sm:space-y-0 mt-6">
                        <div x-data="{ showConfirmCancel: false }">
                            <!-- Cancel button -->
                            <x-danger-button @click="showConfirmCancel = true" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                {{ __('Cancel') }}
                            </x-danger-button>

                            <!-- Confirmation dialog component -->
                            <x-confirmation-dialog>
                                <x-slot:title>
                                    {{ __('Are you sure you want to cancel?') }}
                                </x-slot>

                                <x-slot:actions>
                                    <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                        <form action="{{ route('create-piggy-bank.cancel') }}" method="POST" class="block">
                                            @csrf
                                            <x-danger-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                {{ __('Yes, cancel') }}
                                            </x-danger-button>
                                        </form>

                                        <x-secondary-button
                                            @click="showConfirmCancel = false"
                                            class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                        >
                                            {{ __('No, continue') }}
                                        </x-secondary-button>
                                    </div>
                                </x-slot:actions>
                            </x-confirmation-dialog>
                        </div>

                        <form action="{{ route('create-piggy-bank.clear') }}" method="POST">
                            @csrf
                            <x-secondary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                {{ __('Clear') }}
                            </x-secondary-button>
                        </form>

                        <x-primary-button form="mainForm" type="submit" id="nextButton" disabled class="w-[200px] sm:w-auto justify-center sm:justify-start disabled:bg-gray-400 disabled:cursor-not-allowed  disabled:hover:bg-gray-300">
                            {{ __('Next') }}
                        </x-primary-button>
                    </div>

                    </div>
                </div>
            </div>

    </div>

    <script>
        const translations = {
            formattedPrice: @json(__('formatted: :value'))
        };

        const linkPreviewUrl = '{{ route('create-piggy-bank.api.link-preview') }}';
    </script>


    @vite(['resources/js/create-piggy.js'])

</x-app-layout>
