<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-4 px-6">
                    <h1 class="text-lg font-semibold mb-4">{{ __('Step 3 of 3') }}</h1>

                    <div class="mb-6">
                        <x-input-label for="saving_amount_whole" class="font-semibold text-gray-900"> {{ __('Enter Saving Amount') }} </x-input-label>
                        <p class="text-gray-500 text-sm mt-1">
                            {{ __('Please, enter the amount you want to save regularly. This can be daily, weekly, monthly, or yearly.') }}
                        </p>
                        <div class="flex gap-2 items-start mt-2">
                            <!-- Whole number part -->
                            <div class="flex-1 min-w-0">
                                <x-text-input
                                    id="saving_amount_whole"
                                    name="saving_amount_whole"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[1-9][0-9]{0,9}"
                                    min="10"
                                    :value="old('saving_amount_whole', session('enter_saving_amount_step3.amount') ? explode('.', session('enter_saving_amount_step3.amount')->getAmount())[0] : '')"
                                    oninput="updateFormattedPrice(this.value, 'formatted_saving_amount');"
                                    onkeypress="return (function(evt) {
                    const value = this.value;

                    // Check for non-numeric input
                    if (!/[0-9]/.test(evt.key)) {
                        const errorDiv = document.getElementById('saving_amount_whole_error_numbers');
                        errorDiv.classList.remove('hidden');
                        setTimeout(() => {
                            errorDiv.classList.add('hidden');
                        }, 3000);
                        return false;
                    }

                    // Check for initial zero
                    if (value === '' && evt.key === '0') {
                        const errorDiv = document.getElementById('saving_amount_whole_error_numbers');
                        errorDiv.classList.remove('hidden');
                        setTimeout(() => {
                            errorDiv.classList.add('hidden');
                        }, 3000);
                        return false;
                    }

                    // Check length
                    if (value.length >= 10) {
                        const errorDiv = document.getElementById('saving_amount_whole_error_length');
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
                                    onpaste="return (function(evt) {
                    const pastedData = (evt.clipboardData || window.clipboardData).getData('text');

                    // Check for non-numeric input
                    if (!/^[0-9]+$/.test(pastedData)) {
                        const errorDiv = document.getElementById('saving_amount_whole_error_numbers');
                        errorDiv.classList.remove('hidden');
                        setTimeout(() => {
                            errorDiv.classList.add('hidden');
                        }, 3000);
                        return false;
                    }

                    // Check length
                    if (pastedData.length > 10) {
                        const errorDiv = document.getElementById('saving_amount_whole_error_length');
                        errorDiv.classList.remove('hidden');
                        setTimeout(() => {
                            errorDiv.classList.add('hidden');
                        }, 3000);
                        return false;
                    }

                    return true;
                }).call(this, window.event || arguments[0])"
                                />

                                <div id="saving_amount_whole_error_numbers" class="text-red-500 text-sm mt-1 hidden">
                                    {{ __('saving_amount_whole_numbers_only') }}
                                </div>
                                <div id="saving_amount_whole_error_length" class="text-red-500 text-sm mt-1 hidden">
                                    {{ __('saving_amount_whole_length_exceeded') }}
                                </div>
                                <div id="saving_amount_max_error" class="text-red-500 text-sm mt-1 hidden">
                                    {{ __('Saving amount must be less than the target amount.') }}
                                </div>

                            </div>

                            @if( App\Helpers\CurrencyHelper::hasDecimalPlaces(session('currency', config('app.default_currency'))))
                                <div class="flex items-center mt-2">
                                    <span class="text-lg">.</span>
                                </div>

                                <!-- Decimal/cents part -->
                                <div class="w-12">
                                    <x-text-input
                                        id="saving_amount_cents"
                                        name="saving_amount_cents"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="\d{1,2}"
                                        onfocus="this.dataset.cleared = 'false';"
                                        oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2);"
                                        onblur="if (this.value === '') this.value = '00';"
                                        :value="session('enter_saving_amount_step3.amount') ? (explode('.', session('enter_saving_amount_step3.amount')->getAmount())[1] ?? '00') : '00'"
                                        class="block w-full"
                                        required
                                    />
                                </div>
                            @endif

                        </div>

                        <div class="flex items-center gap-4 mt-1">
                            <p id="formatted_saving_amount" class="text-gray-500 text-sm italic"></p>
                            <p class="text-gray-400 text-xs mt-1">{{ __('minimum amount 10') }},</p>
                        </div>

                        <!-- Error messages -->
                        <div class="mt-2">
                            <x-input-error :messages="$errors->get('saving_amount_whole')" />
                            <x-input-error :messages="$errors->get('saving_amount_cents')" />
                        </div>


                    </div>


                    <div class="mb-8 bg-gray-50 p-6 rounded-lg border border-gray-200">

                        <div class="space-y-4">


                            <!-- Price Display -->
                            <div class="flex justify-between items-baseline">
                                <span class="text-gray-700 break-normal">{{ __('Item Price') }}:</span>
                                <div class="flex items-baseline gap-2 text-right flex-wrap justify-end min-w-[120px]">
                                    {{ session('pick_date_step1.price')->formatTo(App::getLocale()) }}
                                </div>
                            </div>

                            <!-- Starting Amount Display (only shown if exists) -->
                            @if(session('pick_date_step1.starting_amount'))
                                <div class="flex justify-between items-baseline">
                                    <span class="text-gray-700 break-normal">{{ __('Initial Deposit') }}:</span>
                                    <div class="flex items-baseline gap-2 text-right flex-wrap justify-end min-w-[120px]">
                                        {{ session('pick_date_step1.starting_amount')->formatTo(App::getLocale()) }}
                                    </div>
                                </div>
                            @endif


                            <!-- Target Amount Display -->
                            @php
                                $startingAmount = session('pick_date_step1.starting_amount');
                                // Only proceed with calculation if starting amount exists and is not zero
                                if ($startingAmount && !$startingAmount->isZero()) {
                                    $price = session('pick_date_step1.price');
                                    $targetAmount = $price->minus($startingAmount);
                            @endphp
                            <div class="flex justify-between items-baseline">
                                <span class="text-gray-700 break-normal">{{ __('Target Amount') }}:</span>
                                <div class="flex items-baseline gap-2 text-right flex-wrap justify-end min-w-[120px]">
                                    {{ $targetAmount->formatTo(App::getLocale()) }}
                                </div>
                            </div>
                            @php
                                }
                            @endphp


                        </div>


                    </div>


                    {{-- Frequency Options Container --}}
                    <div id="frequencyOptions" class="mt-8 hidden">
                        <h2 id="frequencyTitle" class="text-lg font-semibold mb-6">{{ __('Select your saving frequency') }}</h2>

                        <div class="space-y-6">
                            <!-- Short-term Saving Options -->
                            <div>
                                <h3 class="text-md font-medium text-gray-800 mb-4">{{ __('Short-term Saving Options') }}</h3>
                                <div id="shortTermOptions" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <!-- Long-term Saving Options -->
                            <div>
                                <h3 class="text-md font-medium text-gray-800 mb-4">{{ __('Long-term Saving Options') }}</h3>
                                <div id="longTermOptions" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>
                        </div>

                    </div>

                    <script>
                        window.Laravel = {
                            locale: "{{ app()->getLocale() }}",
                            translations: @json(trans()->getLoader()->load(app()->getLocale(), '*', '*')),
                            routes: {
                                calculateTargetDates: '{{ localizedRoute("localized.create-piggy-bank.enter-saving-amount.calculate-target-dates", ["locale" => app()->getLocale()]) }}'
                            },
                            defaultImage: '{{ asset("images/default_piggy_bank.png") }}',
                            maxSavingAmount: @php
                                $startingAmount = session('pick_date_step1.starting_amount');
                                if ($startingAmount && !$startingAmount->isZero()) {
                                    $maxAmount = session('pick_date_step1.price')->minus($startingAmount);
                                } else {
                                    $maxAmount = session('pick_date_step1.price');
                                }
                                echo $maxAmount->getAmount()->toFloat() - 0.01; // Subtract 0.01 to make it "less than"
                            @endphp
                        };

                        const translations = {
                            formattedSavingAmount: @json(__('formatted: :value')),
                            savingsPlan: @json(__('Savings plan')),
                            periodicSavingAmount: @json(__('Periodic Saving Amount')),
                            targetDate: @json(__('Target Date')),
                            total: @json(__('Total')),
                            daily: @json(__('daily')),
                            weekly: @json(__('weekly')),
                            monthly: @json(__('monthly')),
                            yearly: @json(__('yearly'))
                        };
                    </script>
                    @vite(['resources/js/enter-saving-amount-strategy.js'])


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
                                        <form action="{{ localizedRoute('localized.create-piggy-bank.cancel') }}" method="POST" class="block">
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

                        <form method="GET" action="{{ localizedRoute('localized.create-piggy-bank.step-2.get') }}" class="inline">
                            <x-secondary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                {{ __('Previous') }}
                            </x-secondary-button>
                        </form>

                        <form method="POST" action="{{ localizedRoute('localized.create-piggy-bank.enter-saving-amount.process-step-3') }}">
                            @csrf
                            <!-- Add hidden fields to send the data -->
                            <input type="hidden" name="selected_frequency" id="selectedFrequencyInput">
                            <input type="hidden" name="saving_amount" id="savingAmountInput">
                            <input type="hidden" name="target_dates" id="targetDatesInput">

                            <x-primary-button type="submit" id="nextButton" class="w-[200px] sm:w-auto justify-center sm:justify-start disabled:bg-gray-400 disabled:cursor-not-allowed disabled:hover:bg-gray-300">
                                {{ __('Next') }}
                            </x-primary-button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

