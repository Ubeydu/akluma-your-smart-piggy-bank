<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                {{ __('Piggy Bank Details') }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ localizedRoute('localized.piggy-banks.index') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-center inline-block">
                    {{ __('Back to Piggy Banks') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-6 px-8">
                    <!-- Editable Fields Form -->
                    <form method="POST" action="{{ localizedRoute('localized.piggy-banks.update', ['piggy_id' => $piggyBank->id]) }}" class="space-y-6" x-data="{ isEditing: false, showVaultWarning: false, isStatusDisabled: {{ in_array($piggyBank->status, ['cancelled', 'done']) ? 'true' : 'false' }} }" x-ref="editForm">
                        @csrf
                        @method('PUT')

                        <!-- Name (Editable) -->
                        <div>
                            <x-input-label for="name" :value="__('Product Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name', $piggyBank->name)" required
                                          x-bind:disabled="!isEditing"
                                          x-bind:class="{ 'cursor-not-allowed': !isEditing }" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Details (Editable) -->
                        <div>
                            <x-input-label for="details" :value="__('Details')" />
                            <textarea id="details"
                                      name="details"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-xs"
                                      rows="3"
                                      x-bind:disabled="!isEditing"
                                      x-bind:class="{ 'cursor-not-allowed': !isEditing }">{{ old('details', $piggyBank->details) }}</textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>

                        <!-- Vault Selection (Editable) -->
                        <div>
                            <div class="flex items-center">
                            <x-input-label for="vault_id" :value="__('Vault')"/>

                            <span x-data="{ showTooltip: false }" class="relative cursor-help inline-block ml-1">
                                <svg @mouseenter="showTooltip = true"
                                     @mouseleave="showTooltip = false"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="2"
                                     stroke="currentColor"
                                     class="w-4 h-4 text-gray-600 hover:text-gray-800 transition-colors duration-200">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <div x-show="showTooltip"
                                     x-cloak
                                     class="absolute z-10 w-48 px-3 py-2 mt-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg"
                                     :class="{
                                        'left-0 ml-2': $el.getBoundingClientRect().left < 150,
                                        'right-0 mr-2': $el.getBoundingClientRect().right > window.innerWidth - 150,
                                        'left-1/2 -translate-x-1/2': $el.getBoundingClientRect().left >= 150 && $el.getBoundingClientRect().right <= window.innerWidth - 150
                                     }"
                                     role="tooltip">
                                    {{ __('vault_definition') }}
                                </div>
                            </span>
                            </div>

                            <div class="relative">
                            <select id="vault_id"
                                    name="vault_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-xs"
                                    x-bind:disabled="!isEditing || {{ in_array($piggyBank->status, ['cancelled', 'done']) ? 'true' : 'false' }}"
                                    x-bind:class="{
                                        'cursor-pointer': isEditing && {{ in_array($piggyBank->status, ['cancelled', 'done']) ? 'false' : 'true' }},
                                        'cursor-not-allowed opacity-60': !isEditing || {{ in_array($piggyBank->status, ['cancelled', 'done']) ? 'true' : 'false' }}
                                    }">
                                <option value="">{{ __('No vault selected') }}</option>
                                @foreach(auth()->user()->vaults as $vault)
                                    <option value="{{ $vault->id }}" {{ old('vault_id', $piggyBank->vault_id) == $vault->id ? 'selected' : '' }}>
                                        {{ $vault->name }}
                                    </option>
                                @endforeach
                            </select>
                                <div
                                    x-show="isEditing && isStatusDisabled"
                                    @click="
                                        showVaultWarning = true;
                                        setTimeout(() => { showVaultWarning = false }, 5000);
                                    "
                                    class="absolute inset-0 z-10 cursor-not-allowed"
                                    style="background: transparent"
                                ></div>
                            </div>

                            @if(auth()->user()->vaults->count() === 0)
                                <div class="mt-2">
                                    <a href="{{ localizedRoute('localized.vaults.create') }}"
                                       class="text-blue-600 hover:text-blue-700 underline text-sm">
                                        {{ __('Create Vault') }}
                                    </a>
                                </div>
                            @endif

                            <x-input-error :messages="$errors->get('vault_id')" class="mt-2" />

                            <template x-if="showVaultWarning && isStatusDisabled">
                                <div class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('vault_cant_connect_status', ['status' => strtolower(__($piggyBank->status))]) }}
                                </div>
                            </template>

                        </div>

                        <!-- Save and Cancel Buttons -->
                        <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-end sm:space-y-0 sm:gap-3">
                            <template x-if="!isEditing">
                                <x-secondary-button type="button" @click="isEditing = true">
                                    {{ __('Edit') }}
                                </x-secondary-button>
                            </template>

                            <template x-if="isEditing">
                                <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3">
                                    <div x-data="{ showConfirmCancel: false }">
                                        <x-danger-button type="button" @click.prevent="showConfirmCancel = true" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                            {{ __('Cancel') }}
                                        </x-danger-button>

                                        <template x-if="showConfirmCancel">
                                            <x-confirmation-dialog>
                                                <x-slot:title>
                                                    {{ __('Are you sure you want to cancel?') }}
                                                </x-slot>

                                                <x-slot:actions>
                                                    <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                        <x-danger-button
                                                                @click="isEditing = false;
                                                                    showConfirmCancel = false;
                                                                    $refs.editForm.reset();
                                                                    window.location.href = '{{ localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]) }}?cancelled=1';"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('Yes, proceed') }}
                                                        </x-danger-button>

                                                        <x-secondary-button
                                                                @click="showConfirmCancel = false"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('No, cancel') }}
                                                        </x-secondary-button>
                                                    </div>
                                                </x-slot:actions>
                                            </x-confirmation-dialog>
                                        </template>
                                    </div>

                                    <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                        {{ __('Save') }}
                                    </x-primary-button>
                                </div>
                            </template>
                        </div>

                    </form>

                    <!-- Non-editable Fields -->
                    <div class="mt-8 space-y-6">
                        <!-- Image -->
                        <div class="w-32 md:w-48 mx-auto">
                            <div class="aspect-square relative overflow-hidden rounded-lg shadow-xs bg-gray-50">
                                <img src="{{ asset($piggyBank->preview_image) }}"
                                     alt="{{ $piggyBank->name }}"
                                     class="absolute inset-0 w-full h-full object-contain" />
                            </div>
                        </div>

                        <!-- Other Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            @include('partials.classic-piggy-bank-financial-summary', ['piggyBank' => $piggyBank, 'manualTransactions' => $manualTransactions])

                            <!-- Status + More Info -->
                            <div class="space-y-4" x-data="{ showMoreInfo: false }">
                                <div x-data="{
                                    showConfirmStatus: false,
                                    statusChangeAction: '',
                                    statusChangeMessage: ''
                                }">

                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Current Status') }}</h3>

                                    <div class="mt-1 space-y-2 sm:space-y-0">
                                        <div class="mb-3">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-md
                                                    {{ $piggyBank->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $piggyBank->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $piggyBank->status === 'done' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                    <div id="status-text-{{ $piggyBank->id }}" class="font-medium">
                                                        {{ ucfirst(__(strtolower($piggyBank->status))) }}
                                                    </div>
                                                </span>
                                            </div>
                                        </div>

                                        @if($piggyBank->status === 'active')
                                        <div class="relative inline-block w-full sm:w-64 z-30 cursor-pointer">
                                            <label for="piggy-bank-status-{{ $piggyBank->id }}" class="text-sm text-gray-500 block mb-1 cursor-pointer">
                                                {{ __('Change Status To') }}
                                            </label>
                                            <select id="piggy-bank-status-{{ $piggyBank->id }}"
                                                    class="block w-full text-base border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
                                                    data-piggy-bank-id="{{ $piggyBank->id }}"
                                                    data-initial-status="{{ $piggyBank->status }}">
                                                <option value="active" selected>{{ __('active') }}</option>
                                                <option value="done">{{ __('done') }}</option>
                                                <option value="cancelled">{{ __('cancelled') }}</option>
                                            </select>
                                        </div>
                                        @endif

                                        <template x-if="showConfirmStatus">
                                            <x-confirmation-dialog show="showConfirmStatus">
                                                <x-slot:title>
                                                    <span x-text="statusChangeMessage"></span>
                                                </x-slot>

                                                <x-slot:actions>
                                                    <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                        <x-danger-button
                                                                @click="await statusChangeAction(); showConfirmStatus = false;"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('Yes, proceed') }}
                                                        </x-danger-button>

                                                        <x-secondary-button
                                                                @click="showConfirmStatus = false"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('No, cancel') }}
                                                        </x-secondary-button>
                                                    </div>
                                                </x-slot:actions>
                                            </x-confirmation-dialog>
                                        </template>
                                    </div>
                                </div>

                                <!-- Show More/Hide Info Toggle -->
                                <div class="pt-2">
                                    <button
                                        type="button"
                                        @click="showMoreInfo = !showMoreInfo"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-800 border border-gray-200 shadow-xs transition-colors duration-200 cursor-pointer"
                                    >
                                        <span x-text="showMoreInfo ? '{{ __('Hide Details') }}' : '{{ __('Show More') }}'"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-4 w-4 ml-2 transition-transform"
                                             :class="{ 'rotate-180': showMoreInfo }"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Collapsible Info -->
                                <div x-show="showMoreInfo"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 transform scale-100"
                                     x-transition:leave-end="opacity-0 transform scale-95"
                                     class="space-y-4 pt-2 border-t border-gray-200">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">{{ __('piggy_bank_ID') }}</h3>
                                        <p class="mt-1 text-base text-gray-900">{{ $piggyBank->id }}</p>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">{{ __('Product Link') }}</h3>
                                        @if($piggyBank->link)
                                            <a href="{{ $piggyBank->link }}" target="_blank"
                                               class="mt-1 text-base text-blue-600 hover:text-blue-800 break-all">
                                                {{ $piggyBank->link }}
                                            </a>
                                        @else
                                            <p class="mt-1 text-base text-gray-900">-</p>
                                        @endif
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">{{ __('created_at') }}</h3>
                                        <p class="mt-1 text-base text-gray-900">
                                            {{ $piggyBank->created_at?->translatedFormat('d F Y H:i:s') ?? '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">{{ __('updated_at') }}</h3>
                                        <p class="mt-1 text-base text-gray-900">
                                            {{ $piggyBank->updated_at?->translatedFormat('d F Y H:i:s') ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Add / Take Out Money Section -->
                        <div id="money-section" class="bg-green-50 rounded-xl p-6 border border-green-200 {{ in_array($piggyBank->status, ['done', 'cancelled']) ? 'opacity-50 pointer-events-none' : '' }}"
                             x-data="{ mode: 'add' }">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('classic_add_money_title') }}</h3>

                            <div id="money-success" class="hidden mb-4 p-3 bg-green-100 border border-green-300 rounded-lg text-green-800 text-sm font-medium transition-all duration-300"></div>
                            <div id="money-error" class="hidden mb-4 p-3 bg-red-100 border border-red-300 rounded-lg text-red-800 text-sm font-medium"></div>

                            @php
                                $currencyHasDecimals = \App\Helpers\CurrencyHelper::hasDecimalPlaces($piggyBank->currency);
                            @endphp

                            @if(!in_array($piggyBank->status, ['done', 'cancelled']))
                            <div class="flex gap-2 mb-4 p-1 bg-white rounded-lg border border-gray-200 inline-flex">
                                <button type="button"
                                        @click="mode = 'add'"
                                        :class="mode === 'add' ? 'bg-green-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors cursor-pointer">
                                    {{ __('classic_mode_add') }}
                                </button>
                                <button type="button"
                                        @click="mode = 'withdraw'"
                                        :class="mode === 'withdraw' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors cursor-pointer">
                                    {{ __('classic_mode_withdraw') }}
                                </button>
                            </div>
                            @endif

                            <form id="money-form"
                                  data-url="{{ localizedRoute('localized.piggy-banks.add-remove-money', ['piggy_id' => $piggyBank->id]) }}"
                                  class="space-y-4">
                                @csrf
                                <input type="hidden" name="type" id="money-type" :value="mode === 'add' ? 'manual_add' : 'manual_withdraw'" />

                                <div>
                                    <label for="money-amount" class="block text-sm font-medium text-gray-700 mb-1">
                                        <span x-text="mode === 'add' ? '{{ __('classic_amount_to_add_label') }}' : '{{ __('classic_amount_to_take_out_label') }}'"></span>
                                        <span class="text-gray-400">({{ $piggyBank->currency }})</span>
                                    </label>
                                    <input
                                        id="money-amount"
                                        name="amount"
                                        type="text"
                                        inputmode="{{ $currencyHasDecimals ? 'decimal' : 'numeric' }}"
                                        pattern="{{ $currencyHasDecimals ? '^\d{1,10}(\.\d{1,2})?$' : '^\d{1,12}$' }}"
                                        maxlength="12"
                                        required
                                        autocomplete="off"
                                        placeholder="{{ $currencyHasDecimals ? '0.00' : '0' }}"
                                        class="block w-full text-xl font-semibold rounded-lg border-gray-300 shadow-xs focus:border-green-500 focus:ring-green-500 py-3 px-4"
                                        {{ in_array($piggyBank->status, ['done', 'cancelled']) ? 'disabled' : '' }}
                                        @if($currencyHasDecimals)
                                        oninput="
                                            let v = this.value.replace(/[^0-9.]/g, '');
                                            v = v.replace(/^0+(\d)/, '$1');
                                            v = v.replace(/(\..*)\./g, '$1');
                                            if (v.indexOf('.') > -1) {
                                                let parts = v.split('.');
                                                parts[1] = parts[1].slice(0, 2);
                                                v = parts[0].slice(0, 10) + '.' + parts[1];
                                            } else {
                                                v = v.slice(0, 12);
                                            }
                                            this.value = v;
                                        "
                                        @else
                                        oninput="
                                            let v = this.value.replace(/[^0-9]/g, '');
                                            v = v.replace(/^0+(\d)/, '$1');
                                            this.value = v.slice(0, 12);
                                        "
                                        @endif
                                    />
                                </div>

                                <div>
                                    <label for="money-note" class="block text-xs text-gray-500 mb-1">{{ __('classic_note_label') }}</label>
                                    <input
                                        id="money-note"
                                        name="note"
                                        type="text"
                                        maxlength="255"
                                        autocomplete="off"
                                        placeholder="{{ __('classic_note_placeholder') }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-xs focus:border-green-500 focus:ring-green-500 text-sm py-2 px-3"
                                        {{ in_array($piggyBank->status, ['done', 'cancelled']) ? 'disabled' : '' }}
                                    />
                                </div>

                                @if(!in_array($piggyBank->status, ['done', 'cancelled']))
                                <button
                                    type="submit"
                                    id="money-submit-btn"
                                    :class="mode === 'add' ? 'bg-green-500 hover:bg-green-600 active:bg-green-700' : 'bg-orange-500 hover:bg-orange-600 active:bg-orange-700'"
                                    class="w-full py-3 px-6 rounded-lg text-white text-lg font-bold shadow-md transition-all duration-200 cursor-pointer"
                                >
                                    <span x-text="mode === 'add' ? '{{ __('classic_add_money_button') }}' : '{{ __('classic_withdraw_button') }}'"></span>
                                </button>
                                @endif
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.piggyBankTranslations = {
            confirm_done: "{{ __('Are you sure you want to mark this piggy bank as done?') }}",
            confirm_cancel: "{{ __('Are you sure you want to cancel this piggy bank?') }}",
        };
    </script>

    @vite(['resources/js/classic-piggy-bank.js'])

</x-app-layout>
