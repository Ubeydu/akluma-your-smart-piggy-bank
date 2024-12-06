<?php
$placeholders = [
'tr' => 'gg.aa.yyyy',
'en_US' => 'mm/dd/yyyy',
'en_GB' => 'dd/mm/yyyy',
'fr' => 'jj/mm/aaaa',
];

// Get the current language
$language = app()->getLocale(); // Gets the current locale, e.g., 'tr', 'en_US', etc.
$currentPlaceholder = $placeholders[$language];
?>

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
                    <h1 class="text-lg font-semibold mb-4">{{ __('Step 3 of 3') }}</h1>


                    <div class="mb-6">
                        <label id="saving_date_label" for="saving_date" class="block text-gray-700 font-medium mb-2">
                            {{ __('Pick Date') }}
                        </label>
                        <input type="date" id="saving_date" name="saving_date"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               min="{{ \Carbon\Carbon::now()->addDay()->format('Y-m-d') }}" />
                        <p id="dateError" class="text-red-500 text-sm mt-1 hidden">
                            {{ __('Please pick a valid future date.') }}
                        </p>

                        <div class="hidden text-gray-400"></div>

                    </div>

                    <div class="mb-8 bg-gray-50 p-6 rounded-lg border border-gray-200">

                        <div class="space-y-4">
                            <!-- Price Display -->
                            <div class="flex justify-between items-baseline">
                                <span class="text-gray-700 break-normal">{{ __('Item Price') }}:</span>
                                <div class="flex items-baseline gap-2 text-right flex-wrap justify-end min-w-[120px]">
                                    <span>{{ number_format((float)session('pick_date_step1.price')->getAmount()->__toString(), 0) }}</span>
                                    <span>{{ session('pick_date_step1.currency') }}</span>
                                </div>
                            </div>

                            <!-- Starting Amount Display (only shown if exists) -->
                            @if(session('pick_date_step1.starting_amount'))
                                <div class="flex justify-between items-baseline">
                                    <span class="text-gray-700 break-normal">{{ __('Initial Deposit') }}:</span>
                                    <div class="flex items-baseline gap-2 text-right flex-wrap justify-end min-w-[120px]">
                                        <span>{{ number_format((float)session('pick_date_step1.starting_amount')->getAmount()->__toString(), 0) }}</span>
                                        <span>{{ session('pick_date_step1.currency') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Frequency Options Container --}}

                    {{-- Frequency Options Container --}}
                    <div id="frequencyOptions" class="mt-8 hidden"> <!-- Container starts hidden -->
                        <h2 id="frequencyTitle" class="text-lg font-semibold mb-6">{{ __('Select your saving frequency') }}</h2>
                        <div class="space-y-6">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>

                    <script>
                        window.Laravel = {
                            locale: "{{ app()->getLocale() }}",
                            translations: @json(trans()->getLoader()->load(app()->getLocale(), '*', '*')),
                            routes: {
                                calculateFrequencies: '{{ route("create-piggy-bank.pick-date.calculate-frequencies") }}',
                                storeFrequency: '{{ route("create-piggy-bank.pick-date.store-frequency") }}'
                            }
                        }
                    </script>
                    @vite(['resources/js/pick-date-strategy-frequency-options.js'])


                    <!-- Action Buttons -->
                    <div class="flex justify-between mt-6">
                        <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">
                            {{ __('Cancel') }}
                        </x-danger-button>
                        <x-secondary-button type="button" onclick="window.location='{{ route('create-piggy-bank.step-2.get') }}'">
                            {{ __('Previous') }}
                        </x-secondary-button>
                        <x-primary-button id="nextButton" type="button" class="hidden">
                            {{ __('Next') }}
                        </x-primary-button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
