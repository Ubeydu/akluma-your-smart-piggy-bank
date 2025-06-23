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
                    <h1 class="text-lg font-semibold mb-4">{{ __('Step 2 of 3') }}</h1>
                    <p class="text-gray-600 mb-6">{{ __('Choose your strategy') }}</p>

                    <div class="grid gap-6 mb-6">
                        <!-- Pick Date Strategy -->
                        <form action="{{ localizedRoute('localized.create-piggy-bank.choose-strategy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="strategy" value="pick-date">
                            <button type="submit" class="w-full p-6 text-left border rounded-lg hover:border-indigo-500 focus:outline-hidden focus:border-indigo-500 transition-colors duration-200 cursor-pointer">
                                <h3 class="text-xl font-semibold mb-3">{{ __('Pick Date') }}</h3>
                                <p class="text-gray-600">{{ __('pick_date_strategy_definition') }}</p>
                            </button>
                        </form>

                        <!-- Enter Saving Amount Strategy -->
                        <form method="POST" action="{{ localizedRoute('localized.create-piggy-bank.choose-strategy') }}" class="relative w-full">
                            @csrf
                            <input type="hidden" name="strategy" value="enter-saving-amount">

                            <!-- Button with grayed out text and background -->
                            <button
                                type="submit"
                                disabled
                                class="w-full p-6 text-left border rounded-lg transition-colors duration-200 cursor-not-allowed pointer-events-none bg-gray-100"
                            >
                                <h3 class="text-xl font-semibold mb-3 text-gray-500">{{ __('Enter Saving Amount') }}</h3>
                                <p class="text-gray-500">{{ __('enter_saving_amount_strategy_definition') }}</p>
                            </button>

                            <!-- Badge positioned well above the title -->
                            <span class="absolute right-0 z-10" style="top: -12px;">
                                <span class="inline-block bg-linear-to-r from-yellow-400 to-orange-500 text-sm text-gray-700 font-medium px-4 py-2 rounded-full shadow-lg" style="background: linear-gradient(to right, #FBBF24, #F97316);">
                                    {{ __('Coming Soon ✨') }}
                                </span>
                            </span>

                        </form>


                    </div>

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

                        <x-secondary-button type="button" onclick="window.location='{{ localizedRoute('localized.create-piggy-bank.step-1') }}'" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                            {{ __('Previous') }}
                        </x-secondary-button>
                    </div>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
