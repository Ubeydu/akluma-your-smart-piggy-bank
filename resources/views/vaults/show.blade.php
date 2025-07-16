<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900  leading-tight mr-4">
                {{ $vault->name }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ localizedRoute('localized.vaults.edit', ['vault_id' => $vault->id]) }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md text-center inline-block">
                    {{ __('Edit Vault') }}
                </a>
                <a href="{{ localizedRoute('localized.vaults.index') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-center inline-block">
                    {{ __('Back to Vaults') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Vault Summary Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Saved -->
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200 mb-2">
                                {{ __('Total Money Saved') }}
                            </h3>

                            @if(empty($vault->total_saved))
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                    0
                                </p>
                            @elseif(count($vault->total_saved) === 1)
                                @php
                                    $currency = array_keys($vault->total_saved)[0];
                                    $amount = array_values($vault->total_saved)[0];
                                @endphp
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                    {{ number_format($amount, 2) }} {{ $currency }}
                                </p>
                            @else
                                <div class="space-y-2">
                                    @foreach($vault->total_saved as $currency => $amount)
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-semibold text-green-900 dark:text-green-100">
                                                {{ number_format($amount, 2) }} {{ $currency }}
                                            </span>
                                        </div>
                                    @endforeach
                                    <p class="text-sm text-green-700 dark:text-green-300 mt-2 italic">
                                        {{ __('Multiple currencies - totals shown separately') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Connected Piggy Banks Count -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                {{ __('Connected Piggy Banks') }}
                            </h3>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                {{ $vault->piggyBanks->count() }}
                            </p>
                        </div>
                    </div>

                    @if($vault->details)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Vault Details') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                {{ $vault->details }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Connected Piggy Banks -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Connected Piggy Banks') }}
                        </h3>
                        @if($vault->piggyBanks->count() > 0)
                            <span class="text-sm text-gray-200">
                                {{ __(':count piggy banks in this vault', ['count' => $vault->piggyBanks->count()]) }}
                            </span>
                        @endif
                    </div>

                    @if($vault->piggyBanks->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($vault->piggyBanks as $piggyBank)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between mb-3">
                                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $piggyBank->name }}
                                            <span class="text-gray-600 dark:text-gray-200 font-normal text-sm ml-2">
                                                #{{ $piggyBank->id }}
                                            </span>
                                        </h4>
                                        @php
                                            $statusClasses = match($piggyBank->status) {
                                                'active' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300',
                                                'paused' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
                                                'done' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300'
                                            };
                                        @endphp
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                            {{ __($piggyBank->status) }}
                                        </span>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-200">{{ __('Saved:') }}</span>
                                            <span class="font-medium text-green-400">
                                                {{ number_format($piggyBank->actual_final_total_saved, 2) }} {{ $piggyBank->currency }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-200">{{ __('Goal:') }}</span>
                                            <span class="font-medium dark:text-white">
                                                {{ number_format($piggyBank->final_total, 2) }} {{ $piggyBank->currency }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex justify-between items-center">
                                        <a href="{{ localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]) }}"
                                           class="text-blue-300 hover:text-blue-500 text-sm font-medium">
                                            {{ __('View Details') }} →
                                        </a>

                                        <div x-data="{ showDisconnectConfirm: false }">
                                            <button type="button"
                                                    @click.prevent="showDisconnectConfirm = true"
                                                    class="text-red-400 hover:text-red-600 text-sm font-medium cursor-pointer">
                                                {{ __('Disconnect') }}
                                            </button>

                                            <template x-if="showDisconnectConfirm">
                                                <x-confirmation-dialog show="showDisconnectConfirm">
                                                    <x-slot:title>
                                                        {{ __('Are you sure you want to disconnect this piggy bank?') }}
                                                    </x-slot>

                                                    <x-slot:actions>
                                                        <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                            <form method="POST" action="{{ localizedRoute('localized.vaults.disconnect-piggy-bank', ['vault_id' => $vault->id]) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="piggy_bank_id" value="{{ $piggyBank->id }}">
                                                                <x-danger-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                                    {{ __('Yes, proceed') }}
                                                                </x-danger-button>
                                                            </form>

                                                            <x-secondary-button type="button"
                                                                                @click="showDisconnectConfirm = false"
                                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                                {{ __('No, cancel') }}
                                                            </x-secondary-button>
                                                        </div>
                                                    </x-slot:actions>
                                                </x-confirmation-dialog>
                                            </template>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="mb-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2">
                                {{ __('No piggy banks connected') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                {{ __('connect_piggy_bank') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>


            <!-- Connect Piggy Bank Section -->
            @if($unconnectedPiggyBanks->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Connect Piggy Bank') }}
                        </h3>

                        <form method="POST" action="{{ localizedRoute('localized.vaults.connect-piggy-bank', ['vault_id' => $vault->id]) }}" class="space-y-4">
                            @csrf

                            <div x-data="{
                                open: false,
                                selected: null,
                                selectedText: '{{ __('Select a piggy bank to connect') }}',
                                piggyBanks: @js($formattedPiggyBanks)
                            }" class="relative">


                                <!-- Hidden input for form submission -->
                                <input type="hidden" name="piggy_bank_id" :value="selected" required>

                                <!-- Dropdown button -->
                                <button type="button" @click="open = !open"
                                        class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-base">
                                    <span class="block truncate text-gray-900 dark:text-gray-300" x-text="selectedText"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 transition-transform duration-200"
                                             :class="{ 'rotate-180': open }"
                                             viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>

                                <!-- Dropdown menu -->
                                <div x-show="open" @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-lg ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none">

                                    <template x-for="piggyBank in piggyBanks" :key="piggyBank.id">
                                        <button type="button" @click="selected = piggyBank.id; selectedText = piggyBank.display; open = false"
                                                class="w-full text-left cursor-default select-none relative py-4 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 dark:text-gray-300 text-lg"
                                                :class="{ 'bg-blue-600 text-white': selected === piggyBank.id }">
                                            <span class="block truncate" x-text="piggyBank.display"></span>
                                            <span x-show="selected === piggyBank.id" class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md cursor-pointer">
                                    {{ __('Connect') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Danger Zone -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-red-700 dark:text-red-400 text-sm mt-1 mr-6">
                                    {{ __('This will delete the vault but keep all piggy banks. This action cannot be undone.') }}
                                </p>
                            </div>

                            <div x-data="{ showConfirmCancel: false }">
                                <button type="button"
                                        @click.prevent="showConfirmCancel = true"
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md cursor-pointer">
                                    {{ __('Delete Vault') }}
                                </button>

                                <template x-if="showConfirmCancel">
                                    <x-confirmation-dialog>
                                        <x-slot:title>
                                            {{ __('Are you sure you want to delete this vault? This action cannot be undone.') }}
                                        </x-slot>

                                        <x-slot:actions>
                                            <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                <form method="POST" action="{{ localizedRoute('localized.vaults.destroy', ['vault_id' => $vault->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-danger-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                        {{ __('Yes, proceed') }}
                                                    </x-danger-button>
                                                </form>

                                                <x-secondary-button type="button"
                                                                    @click="showConfirmCancel = false"
                                                                    class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                    {{ __('No, cancel') }}
                                                </x-secondary-button>
                                            </div>
                                        </x-slot:actions>
                                    </x-confirmation-dialog>
                                </template>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
