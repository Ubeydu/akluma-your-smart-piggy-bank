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
                        <p id="dateDisplay" class="text-gray-700 font-medium mt-2 hidden" data-message="{{ __('You picked') }}"></p>
                        <p id="dateError" class="text-red-500 text-sm mt-1 hidden">
                            {{ __('Please pick a valid future date.') }}
                        </p>

                        <div class="hidden text-gray-400"></div>

                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            const dateInput = document.getElementById("saving_date");
                            const dateLabel = document.getElementById("saving_date_label");
                            const dateDisplay = document.getElementById("dateDisplay");


                            // Handle date input changes and display the formatted date
                            dateInput.addEventListener("input", async function () {
                                if (dateInput instanceof HTMLInputElement && dateLabel && dateInput.value) {
                                    dateLabel.classList.add("visibility-hidden");
                                    try {
                                        const response = await fetch(`/format-date?date=${dateInput.value}`);
                                        if (response.ok) {
                                            const data = await response.json();
                                            const message = dateDisplay.getAttribute("data-message");
                                            dateDisplay.textContent = `${message} ${data.formatted_date}`;
                                            dateDisplay.classList.remove("hidden");
                                        }
                                    } catch (error) {
                                        console.error("Error fetching formatted date:", error);
                                    }
                                } else if (dateLabel) {
                                    dateLabel.classList.remove("visibility-hidden");
                                    dateDisplay.classList.add("hidden");
                                }
                            });
                        });
                    </script>


                    <!-- Action Buttons -->
                    <div class="flex justify-between mt-6">
                        <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">
                            {{ __('Cancel') }}
                        </x-danger-button>
                        <x-secondary-button type="button" onclick="window.location='{{ route('create-piggy-bank.step-2.get') }}'">
                            {{ __('Previous') }}
                        </x-secondary-button>
{{--                        <x-primary-button id="nextButton" type="submit">--}}
{{--                            {{ __('Next') }}--}}
{{--                        </x-primary-button>--}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
