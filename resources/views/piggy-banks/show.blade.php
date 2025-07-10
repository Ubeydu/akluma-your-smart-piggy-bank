<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Piggy Bank Details') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-6 px-8">
                    <!-- Editable Fields Form -->
                    <form method="POST" action="{{ localizedRoute('localized.piggy-banks.update', ['piggy_id' => $piggyBank->id]) }}" class="space-y-6" x-data="{ isEditing: false }" x-ref="editForm">
                        @csrf
                        @method('PUT')

                        <!-- Name (Editable) -->
                        <div>
                            <x-input-label for="name" :value="__('Product Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name', $piggyBank->name)" required
                                          x-bind:disabled="!isEditing" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Details (Editable) -->
                        <div>
                            <x-input-label for="details" :value="__('Details')" />
                            <textarea id="details"
                                      name="details"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-xs"
                                      rows="3"
                                      :disabled="!isEditing">{{ old('details', $piggyBank->details) }}</textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>

                        <!-- Vault Selection (Editable) -->
                        <div>
                            <x-input-label for="vault_id" :value="__('Vault')" />
                            <select id="vault_id"
                                    name="vault_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-xs"
                                    x-bind:disabled="!isEditing">
                                <option value="">{{ __('No vault selected') }}</option>
                                @foreach(auth()->user()->vaults as $vault)
                                    <option value="{{ $vault->id }}" {{ old('vault_id', $piggyBank->vault_id) == $vault->id ? 'selected' : '' }}>
                                        {{ $vault->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('vault_id')" class="mt-2" />
                        </div>


                        <!-- Save and Cancel Buttons -->
                        <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-end sm:space-y-0 sm:gap-3">
                            <!-- Edit button -->
                            <template x-if="!isEditing">
                                <x-secondary-button type="button" @click="isEditing = true">
                                    {{ __('Edit') }}
                                </x-secondary-button>
                            </template>

                            <!-- Save and Cancel buttons -->
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

                            @include('partials.piggy-bank-financial-summary', ['piggyBank' => $piggyBank])

                            <!-- Additional Information -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('piggy_bank_ID') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $piggyBank->id }}</p>
                                </div>



                                <div x-data="{
                                    showConfirmCancel: false,
                                    statusChangeAction: '',
                                    statusChangeMessage: ''
                                }">

                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Current Status') }}</h3>

                                    <div class="mt-1 space-y-2 sm:space-y-0">

                                        {{-- Current Status Display --}}
                                        <div class="mb-3">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-md
                                                    {{ $piggyBank->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $piggyBank->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $piggyBank->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $piggyBank->status === 'done' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                    <div id="status-text-{{ $piggyBank->id }}" class="font-medium">
                                                        {{ ucfirst(__(strtolower($piggyBank->status))) }}
                                                    </div>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Status Change Actions --}}
                                        <div class="relative inline-block w-full sm:w-64 z-30">
                                            <label for="piggy-bank-status-{{ $piggyBank->id }}" class="text-sm text-gray-500 block mb-1">
                                                {{ __('Change Status To') }}
                                            </label>
                                            <select id="piggy-bank-status-{{ $piggyBank->id }}"
                                                    class="block w-full text-base border-gray-300 rounded-md shadow-xs focus:ring-blue-500 focus:border-blue-500 cursor-pointer disabled:cursor-not-allowed {{ in_array($piggyBank->status, ['done', 'cancelled']) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    data-initial-status="{{ $piggyBank->status }}"
                                                    {{ in_array($piggyBank->status, ['done', 'cancelled']) ? 'disabled' : '' }}>
                                                @foreach(\App\Models\PiggyBank::getStatusOptions() as $statusOption)
                                                    <option value="{{ $statusOption }}" {{ $piggyBank->status === $statusOption ? 'selected' : '' }}>
                                                        {{ __(strtolower($statusOption)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <template x-if="showConfirmCancel">
                                            <x-confirmation-dialog>
                                                <x-slot:title>
                                                    <span x-text="statusChangeMessage"></span>
                                                </x-slot>

                                                <x-slot:actions>
                                                    <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                        <x-danger-button
                                                                @click="await statusChangeAction(); showConfirmCancel = false;"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('Yes, proceed') }}
                                                        </x-danger-button>

                                                        <x-secondary-button
                                                                @click="showConfirmCancel = false; console.log('No clicked')"
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


                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ __(strtolower($piggyBank->selected_frequency)) }}</p>
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


                        @if(app()->environment('local'))
                            <div class="bg-yellow-50 p-4 rounded-lg mb-4 border border-yellow-200">
                                <h3 class="font-semibold text-yellow-800 mb-2">Test Tools</h3>
                                <div class="flex items-center gap-4">
                                    <form action="{{ localizedRoute('localized.test.set-date', ['piggy_id' => $piggyBank->id]) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input
                                                type="date"
                                                name="test_date"
                                                class="rounded-md border-gray-300"
                                                value="{{ session('test_date') ?? now()->format('Y-m-d') }}"
                                        >
                                        <button type="submit" class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                            Set Test Date
                                        </button>
                                    </form>
                                    @if(session('test_date'))
                                        <form action="{{ localizedRoute('localized.test.clear-date', ['piggy_id' => $piggyBank->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                                Clear Test Date
                                            </button>
                                        </form>
                                    @endif
                                    <div class="text-sm text-gray-600">
                                        Current test date:
                                        <span class="font-medium">
                                            {{ session('test_date') ? Carbon\Carbon::parse(session('test_date'))->format('Y-m-d') : 'Not set' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Savings Guide (Collapsible) -->
                        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
                            <!-- Title + Icon + Show Guide Button, responsive row -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex items-center">
                                    <div class="shrink-0 mt-1">
                                        <svg class="h-6 w-6 min-w-[1.5rem] text-blue-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-blue-800">
                                        {{ __('How to mark your savings as complete') }}
                                    </h3>
                                </div>
                                <button type="button" id="savingsGuideToggle"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-800 border border-gray-200 shadow-xs transition-colors duration-200 cursor-pointer mt-3 sm:mt-0">
                                    <span id="savingsToggleText">{{ __('Show Guide') }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform" id="savingsToggleIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- All remaining content flows below, full width -->
                            <div class="mt-3">
                                <div id="savingsGuideContainer" class="hidden">
                                    <!-- Mobile-friendly mini table with arrow space -->
                                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-3 opacity-75">
                                        <div class="bg-gray-50 px-6 sm:px-8 py-2 border-b border-gray-200">
                                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-4 text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                <div class="text-center text-[10px] sm:text-xs">{{ __('Saved') }}</div>
                                                <div class="text-[10px] sm:text-xs">#</div>
                                                <div class="text-[10px] sm:text-xs hidden sm:block">{{ __('Date') }}</div>
                                                <div class="text-[10px] sm:text-xs">{{ __('Amount') }}</div>
                                                <div class="text-[10px] sm:text-xs hidden sm:block">{{ __('Status') }}</div>
                                            </div>
                                        </div>
                                        <div class="divide-y divide-gray-200">
                                            <!-- Completed Row -->
                                            <div class="px-6 sm:px-8 py-3">
                                                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-4 items-center text-xs sm:text-sm">
                                                    <div class="flex justify-center">
                                                        <div class="w-4 h-4 bg-blue-500 border-2 border-blue-500 rounded flex items-center justify-center">
                                                            <svg class="w-2 h-2 sm:w-3 sm:h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="text-gray-900">1</div>
                                                    <div class="text-gray-900 hidden sm:block">09 Jul</div>
                                                    <div class="text-gray-900 text-xs sm:text-sm">€70</div>
                                                    <div class="text-green-600 font-medium text-xs hidden sm:block">{{ __('saved') }}</div>
                                                </div>
                                            </div>

                                            <!-- Pending Row with Arrow -->
                                            <div class="px-6 sm:px-8 py-4 relative">
                                                <!-- Large visible arrow pointing to checkbox -->
                                                <div class="absolute left-16 sm:left-32 top-1/2 transform -translate-y-1/2 flex items-center">
                                                    <div class="bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-bold animate-pulse shadow-lg">
                                                        ← {{ __('Click') }}
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-4 items-center text-xs sm:text-sm">
                                                    <div class="flex justify-center">
                                                        <div class="w-4 h-4 border-2 border-gray-300 rounded bg-white"></div>
                                                    </div>
                                                    <div class="text-gray-900">2</div>
                                                    <div class="text-gray-900 hidden sm:block">16 Jul</div>
                                                    <div class="text-gray-900 text-xs sm:text-sm">€70</div>
                                                    <div class="text-yellow-600 font-medium text-xs hidden sm:block">{{ __('pending') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Instruction Text -->
                                    <p class="text-sm sm:text-base text-black-700">
                                        ✓ {{ __('Each time you save money according to your schedule, tick the checkbox to mark it as saved and track your progress.') }}
                                    </p>
                                </div>
                            </div>
                        </div>


                        <!-- Savings Schedule -->
                        @include('partials.schedule')

                        <!-- Manual Add/Remove Money Section -->
                        <div id="manual-money-section" class="mb-6 bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                                <div class="flex items-center">
                                    <svg class="h-6 w-6 min-w-[1.5rem] text-blue-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <h3 class="text-lg sm:text-lg font-medium text-blue-800 leading-snug">
                                        {{ __('manual_money_manual_title') }}
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    id="toggle-manual-money"
                                    class="w-full sm:w-auto mt-3 sm:mt-0 inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-800 border border-gray-200 shadow-xs transition-colors duration-200 cursor-pointer"
                                    onclick="const c=document.getElementById('manual-money-collapsible'); const t=document.getElementById('toggle-manual-money-text'); c.classList.toggle('hidden'); t.innerText = c.classList.contains('hidden') ? '{{ __('manual_money_show_buttons') }}' : '{{ __('manual_money_hide_buttons') }}';"
                                >
                                    <span id="toggle-manual-money-text">{{ __('manual_money_show_buttons') }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                            <div id="manual-money-collapsible" class="hidden mt-4">

                                <form id="manual-money-form"
                                      method="POST"
                                      action="{{ localizedRoute('localized.piggy-banks.add-remove-money', ['piggy_id' => $piggyBank->id]) }}"
                                      class="mt-3 flex flex-col md:flex-row md:items-end gap-4">
                                    @csrf

                                    <div>
                                        <x-input-label for="manual-amount" :value="__('manual_amount_label')" />
                                        <x-text-input
                                            id="manual-amount"
                                            name="amount"
                                            type="text"
                                            inputmode="decimal"
                                            pattern="^\d{1,10}(\.\d{1,2})?$"
                                            maxlength="12"
                                            class="mt-1 block w-full"
                                            required
                                            autocomplete="off"
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
                                        />
                                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="manual-note" :value="__('manual_note_label')" />
                                        <x-text-input
                                            id="manual-note"
                                            name="note"
                                            type="text"
                                            maxlength="255"
                                            class="mt-1 block w-full"
                                            autocomplete="off"
                                        />
                                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                                    </div>

                                    <input type="hidden" name="type" id="manual-type" value="manual_add" />

                                    <div class="flex gap-2 mt-2 md:mt-0">
                                        <button
                                            type="submit"
                                            onclick="document.getElementById('manual-type').value='manual_add'"
                                            class="flex-1 min-w-0 px-4 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold shadow focus:outline-none transition cursor-pointer">
                                            {{ __('manual_add_money_button') }}
                                        </button>
                                        <button
                                            type="submit"
                                            onclick="document.getElementById('manual-type').value='manual_withdraw'"
                                            class="flex-1 min-w-0 px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold shadow focus:outline-none transition cursor-pointer">
                                            {{ __('manual_withdraw_money_button') }}
                                        </button>
                                    </div>
                                </form>

                                <div class="mt-4">
                                    <p class="text-sm sm:text-base text-black-700">
                                        {{ __('manual_money_info_message') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.piggyBankTranslations = {
            active: "{{ __('active') }}",
            paused: "{{ __('paused') }}",
            done: "{{ __('done') }}",
            cancelled: "{{ __('cancelled') }}",
            success: "{{ __('success') }}",
            info: "{{ __('info') }}",
            goal_completed: "{{ __('You have successfully completed your savings goal.') }}",
            paused_message: "{{ __('paused_message') }}",
            confirm_pause: "{{ __('Are you sure you want to pause this piggy bank?') }}",
            confirm_cancel: "{{ __('Are you sure you want to cancel this piggy bank?') }}",
            confirm_cancel_paused: "{{ __('Are you sure you want to cancel this paused piggy bank?') }}",
            confirm_resume: "{!! __('Are you sure you want to resume this piggy bank? Dates in your saving schedule may be updated if you proceed.') !!}",
            piggy_bank_cancelled: "{{ __('Piggy bank has been cancelled.') }}",
            saving_marked_as_saved: "{{ __('You successfully marked your saving as saved.') }}",
            saving_marked_as_unsaved: "{{ __('You successfully marked your scheduled saving as pending.') }}",
            error: "{{ __('error') }}",
        };
    </script>



    @vite(['resources/js/scheduled-savings.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('savingsGuideToggle');
            const container = document.getElementById('savingsGuideContainer');
            const toggleText = document.getElementById('savingsToggleText');
            const toggleIcon = document.getElementById('savingsToggleIcon');

            if (toggle && container) {
                toggle.addEventListener('click', function() {
                    if (container.classList.contains('hidden')) {
                        container.classList.remove('hidden');
                        toggleText.textContent = '{{ __("Hide Guide") }}';
                        toggleIcon.style.transform = 'rotate(180deg)';
                    } else {
                        container.classList.add('hidden');
                        toggleText.textContent = '{{ __("Show Guide") }}';
                        toggleIcon.style.transform = 'rotate(0deg)';
                    }
                });
            }
        });
    </script>


</x-app-layout>
