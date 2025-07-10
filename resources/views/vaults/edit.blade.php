<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Vault') }}: {{ $vault->name }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]) }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md">
                    {{ __('Back to Vault') }}
                </a>
                <a href="{{ localizedRoute('localized.vaults.index') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                    {{ __('All Vaults') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ localizedRoute('localized.vaults.update', ['vault_id' => $vault->id]) }}">
                        @csrf
                        @method('PUT')

                        <!-- Vault Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Vault Name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $vault->name) }}"
                                   required
                                   maxlength="255"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900"
                                   placeholder="{{ __('Enter vault name (e.g., "Bank Account Savings", "Physical Piggy Bank")') }}">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Vault Details -->
                        <div class="mb-6">
                            <label for="details" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Details') }} <span class="text-gray-500 text-xs">({{ __('Optional') }})</span>
                            </label>
                            <textarea id="details"
                                      name="details"
                                      rows="4"
                                      maxlength="5000"
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900"
                                      placeholder="{{ __('Describe what this vault represents (e.g., which bank account, which physical location, etc.)') }}">{{ old('details', $vault->details) }}</textarea>
                            @error('details')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">{{ __('Maximum 5000 characters') }}</p>
                        </div>

                        <!-- Current Vault Info -->
                        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                        {{ __('Current Vault Status') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                        <p>{{ __('Connected piggy banks: :count', ['count' => $vault->piggyBanks->count()]) }}</p>
                                        <p>{{ __('Created: :date', ['date' => $vault->created_at->format('M j, Y')]) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]) }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md cursor-pointer">
                                {{ __('Update Vault') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
