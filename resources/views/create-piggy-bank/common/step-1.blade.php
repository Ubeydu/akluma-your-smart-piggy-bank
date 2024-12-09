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






                    <form id="mainForm" method="POST" action="{{ route('create-piggy-bank.step-2') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name">
                                {!! __('1. I am saving for a (required field)') !!}
                            </x-input-label>
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required maxlength="255" autocomplete="on" :value="session('pick_date_step1.name')" />
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
                                        pattern="[1-9][0-9]{2,14}"
                                        min="100"
                                        :value="session('pick_date_step1.price') ? explode('.', session('pick_date_step1.price')->getAmount())[0] : ''"
                                        onkeypress="return (function(evt) {
                                            const value = this.value;
                                            return /[0-9]/.test(evt.key) && !(value === '' && evt.key === '0') && value.length < 15;
                                        }).call(this, window.event || arguments[0])"
                                        class="block w-full"
                                        required
                                    />
                                </div>

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

                                <!-- Currency -->
                                <select
                                    id="currency"
                                    name="currency"
                                    class="block w-24 text-center border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    onchange="window.location.href = '{{ url('currency/switch') }}/' + this.value;"
                                >
                                    @foreach(config('app.currencies') as $code => $name)
                                        <option
                                            value="{{ $code }}"
                                            {{ session('currency') === $code ? 'selected' : '' }}
                                        >
                                            {{ $code }}
                                        </option>
                                    @endforeach
                                </select>


                            </div>
                            <p class="text-gray-500 text-sm mt-1">{{ __('minimum amount 100') }}</p>

                            <!-- Error messages -->
                            <div class="mt-2">
                                <x-input-error :messages="$errors->get('price_whole')" />
                                <x-input-error :messages="$errors->get('price_cents')" />
                            </div>
                        </div>

                        <!-- Link (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="link" :value="__('3. Product link (optional)')" />
                            <x-text-input id="link" name="link" type="url" class="mt-1 block w-full" maxlength="1000" :value="session('pick_date_step1.link')" />
                            <p id="link-count" class="text-gray-500 text-sm mt-1">0 / 1000</p>
                            <x-input-error :messages="$errors->get('link')" class="mt-2" />
                        </div>

                        <!-- Details (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="details" :value="__('4. Details (optional)')" />
                            <textarea id="details" name="details" rows="4" maxlength="5000" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring focus:ring-opacity-50">{{ session('pick_date_step1.details') }}</textarea>
                            <p id="details-count" class="text-gray-500 text-sm mt-1">0 / 5000</p>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>


                        <!-- Starting Amount (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="starting_amount_whole" :value="__('5. I already saved some money (optional)')" />
                            <div class="flex gap-2 items-start mt-1">
                                <!-- Whole number part -->
                                <div class="flex-1 min-w-0">
                                    <x-text-input
                                        id="starting_amount_whole"
                                        name="starting_amount_whole"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[1-9][0-9]{0,14}"
                                        :value="session('pick_date_step1.starting_amount') ? explode('.', session('pick_date_step1.starting_amount')->getAmount())[0] : ''"
                                        onkeypress="return (function(evt) {
                                            const value = this.value;
                                            return /[0-9]/.test(evt.key) && !(value === '' && evt.key === '0') && value.length < 15;
                                        }).call(this, window.event || arguments[0])"
                                        oninput="document.getElementById('starting_amount_cents').value = (this.value === '' || this.value === '0') ? '' : '00';"
                                        class="block w-full"
                                    />
                                </div>

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

                                <!-- Currency -->
                                <div class="w-24">
                                    <x-text-input
                                        id="starting_amount_currency"
                                        type="text"
                                        class="block w-full text-center"
                                        readonly
                                        value="{{ session('currency') }}"
                                    />
                                </div>


                            </div>

                            <!-- Error messages -->
                            <div class="mt-2">
                                <x-input-error :messages="$errors->get('price_whole')" />
                                <x-input-error :messages="$errors->get('price_cents')" />
                                <p id="amount-warning" class="text-red-500 text-sm mt-2 hidden">
                                    {{ __('Starting amount cannot be greater than or equal to the price. Please put a smaller amount.') }}
                                </p>
                            </div>
                        </div>
                    </form>


                    <div class="flex justify-between mt-6">
                        <!-- Cancel "form" (not really a form, just a button) -->
                        <div>
                            <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('piggy-banks.index') }}'; }">
                                {{ __('Cancel') }}
                            </x-danger-button>
                        </div>

                        <!-- Clear form - completely separate from main form -->
                        <form action="{{ route('create-piggy-bank.clear') }}" method="POST">
                            @csrf
                            <x-secondary-button type="submit">
                                {{ __('Clear') }}
                            </x-secondary-button>
                        </form>

                        <!-- Next button that belongs to the main form -->
                        <x-primary-button form="mainForm" type="submit" id="nextButton" disabled class="disabled:bg-gray-300 disabled:cursor-not-allowed">
                            {{ __('Next') }}
                        </x-primary-button>
                    </div>


                    </div>
                </div>
            </div>

    </div>

    @vite(['resources/js/create-piggy.js'])

</x-app-layout>
