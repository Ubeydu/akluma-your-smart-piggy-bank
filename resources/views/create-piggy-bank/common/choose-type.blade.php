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
                    <h1 class="text-lg font-semibold mb-2">{{ __('What kind of piggy bank would you like?') }}</h1>
                    <p class="text-gray-600 mb-6">{{ __('Choose how you want to save') }}</p>

                    <div class="grid gap-6 mb-6">
                        {{-- Classic Piggy Bank --}}
                        <form method="POST" action="{{ localizedRoute('localized.create-piggy-bank.store-type') }}">
                            @csrf
                            <input type="hidden" name="type" value="classic">
                            @auth
                                <input type="hidden" name="remember_choice" id="remember_choice_classic" value="0">
                            @endauth

                            <button
                                type="submit"
                                class="w-full p-6 text-left border rounded-lg hover:border-indigo-500 focus:outline-hidden focus:border-indigo-500 transition-colors duration-200 cursor-pointer"
                            >
                                <h3 class="text-xl font-semibold mb-3">{{ __('Classic Piggy Bank') }}</h3>
                                <p class="text-gray-600">{{ __('classic_piggy_bank_choice_description') }}</p>
                            </button>
                        </form>

                        {{-- Save with a Plan --}}
                        <form method="POST" action="{{ localizedRoute('localized.create-piggy-bank.store-type') }}">
                            @csrf
                            <input type="hidden" name="type" value="scheduled">
                            @auth
                                <input type="hidden" name="remember_choice" id="remember_choice_scheduled" value="0">
                            @endauth

                            <button
                                type="submit"
                                class="w-full p-6 text-left border rounded-lg hover:border-indigo-500 focus:outline-hidden focus:border-indigo-500 transition-colors duration-200 cursor-pointer"
                            >
                                <h3 class="text-xl font-semibold mb-3">{{ __('Save with a Plan') }}</h3>
                                <p class="text-gray-600">{{ __('scheduled_piggy_bank_choice_description') }}</p>
                            </button>
                        </form>
                    </div>

                    {{-- Remember my choice checkbox (authenticated users only) --}}
                    @auth
                        <div class="mb-6" x-data="{ checked: false }">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer"
                                    x-model="checked"
                                    @change="
                                        document.getElementById('remember_choice_classic').value = checked ? '1' : '0';
                                        document.getElementById('remember_choice_scheduled').value = checked ? '1' : '0';
                                    "
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ __('Remember my choice for next time') }}</span>
                            </label>
                        </div>
                    @endauth

                    {{-- Cancel button --}}
                    <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-between sm:space-y-0 mt-6">
                        <div x-data="{ showConfirmCancel: false }">
                            <x-danger-button @click="showConfirmCancel = true" class="w-[200px] sm:w-auto justify-center sm:justify-start">
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
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
