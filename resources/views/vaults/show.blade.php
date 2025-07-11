<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900  leading-tight">
                {{ $vault->name }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ localizedRoute('localized.vaults.edit', ['vault_id' => $vault->id]) }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md">
                    {{ __('Edit Vault') }}
                </a>
                <a href="{{ localizedRoute('localized.vaults.index') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                    {{ __('Back to Vaults') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
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

                                    <div class="mt-4">
                                        <a href="{{ localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]) }}"
                                           class="text-blue-300 hover:text-blue-500 text-sm font-medium">
                                            {{ __('View Details') }} →
                                        </a>
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
                                {{ __('Connect piggy banks to this vault to track your savings by storage location.') }}
                            </p>
                            <a href="{{ localizedRoute('localized.piggy-banks.index') }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                                {{ __('View Your Piggy Banks') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-red-600 mb-4">{{ __('Danger Zone') }}</h3>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-base text-red-800 dark:text-red-300 font-medium">{{ __('Delete Vault') }}</h4>
                                <p class="text-red-700 dark:text-red-400 text-sm mt-1">
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
