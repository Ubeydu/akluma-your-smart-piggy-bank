<?php
$placeholders = [
'tr' => 'gg.aa.yyyy',
'en' => 'mm/dd/yyyy',
'fr' => 'jj/mm/aaaa',
];

// Get the current language
$language = app()->getLocale(); // Gets the current locale, e.g., 'tr', 'en', etc.
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


                        <div x-data="{ showConfirmCancel: false }">
                            <!-- Cancel button -->
                            <x-danger-button @click="showConfirmCancel = true">
                                {{ __('Cancel') }}
                            </x-danger-button>

                            <!-- Confirmation dialog component -->
                            <x-confirmation-dialog>
                                <x-slot:title>
                                    {{ __('Are you sure you want to cancel?') }}
                                </x-slot>

                                <x-slot:actions>

                                    <div class="flex flex-row items-stretch gap-3 justify-end">
                                        <form action="{{ route('create-piggy-bank.cancel') }}" method="POST" class="block">
                                            @csrf
                                            <x-danger-button type="submit" class="justify-center">
                                                {{ __('Yes, cancel') }}
                                            </x-danger-button>
                                        </form>

                                        <x-secondary-button
                                            @click="showConfirmCancel = false"
                                            class="justify-center"
                                        >
                                            {{ __('No, continue') }}
                                        </x-secondary-button>
                                    </div>

                                </x-slot:actions>

                            </x-confirmation-dialog>
                        </div>


                        <form method="GET" action="{{ route('create-piggy-bank.step-2.get') }}" class="inline">
                            <x-secondary-button type="submit">
                                {{ __('Previous') }}
                            </x-secondary-button>
                        </form>

{{--                        <x-primary-button id="nextButton" type="button" class="hidden">--}}
{{--                            {{ __('Next') }}--}}
{{--                        </x-primary-button>--}}
                        <form method="POST" action="{{ route('create-piggy-bank.pick-date.show-summary') }}">
                            @csrf
                            <x-primary-button type="submit" id="nextButton" disabled>
                                {{ __('Next') }}
                            </x-primary-button>
                        </form>
                    </div>


{{--                    @if(session('debug_summary'))--}}
{{--                        <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200 font-mono">--}}
{{--                            <pre class="whitespace-pre-wrap">{{ print_r(session('debug_summary'), true) }}</pre>--}}
{{--                        </div>--}}
{{--                    @endif--}}


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
