<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Vaults') }}
            </h2>
            <a href="{{ localizedRoute('localized.vaults.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                {{ __('Create Vault') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($vaults->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($vaults as $vault)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <h3 class="font-semibold text-lg mb-2">{{ $vault->name }}</h3>

                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Saved') }}</p>

                                        <div class="text-xl font-bold text-green-600">
                                            @if(empty($vault->total_saved))
                                                0
                                            @elseif(count($vault->total_saved) === 1)
                                                @php
                                                    $currency = array_keys($vault->total_saved)[0];
                                                    $amount = array_values($vault->total_saved)[0];
                                                @endphp
                                                {{ number_format($amount, 2) }} {{ $currency }}
                                            @else
                                                @foreach($vault->total_saved as $currency => $amount)
                                                    <div>{{ number_format($amount, 2) }} {{ $currency }}</div>
                                                @endforeach
                                            @endif
                                        </div>

                                    </div>

                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Connected Piggy Banks') }}</p>
                                        <p class="font-medium">{{ $vault->piggyBanks->count() }}</p>
                                    </div>

                                    @if($vault->details)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ Str::limit($vault->details, 100) }}</p>
                                    @endif

                                    <div class="flex space-x-2">
                                        <a href="{{ localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]) }}"
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                            {{ __('View') }}
                                        </a>
                                        <a href="{{ localizedRoute('localized.vaults.edit', ['vault_id' => $vault->id]) }}"
                                           class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                                            {{ __('Edit') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="mb-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('No vaults yet') }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ __('Create your first vault to organize your piggy banks.') }}</p>
                            <a href="{{ localizedRoute('localized.vaults.create') }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                                {{ __('Create Your First Vault') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
