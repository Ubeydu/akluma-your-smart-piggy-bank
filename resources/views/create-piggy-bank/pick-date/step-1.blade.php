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
                    <h1 class="text-lg font-semibold mb-4">{{ __('Step 1 of 3') }}</h1>
                    <p class="text-gray-600 mb-6">{{ __('Provide information about your goal') }}</p>

                    <form method="POST" action="{{ route('create-piggy-bank.pick-date.step-2') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('1. I am saving for a (required field)')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required maxlength="255" />
                            <p id="name-count" class="text-gray-500 text-sm mt-1">0 / 255</p>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <x-input-label for="price" :value="__('2. Price of the item (required field)')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <!-- Link (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="link" :value="__('3. Product link (optional)')" />
                            <x-text-input id="link" name="link" type="url" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('link')" class="mt-2" />
                        </div>

                        <!-- Details (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="details" :value="__('4. Details (optional)')" />
                            <textarea id="details" name="details" rows="4" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring focus:ring-opacity-50"></textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>

                        <!-- Starting Amount (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="starting_amount" :value="__('5. I already saved some money (optional)')" />
                            <x-text-input id="starting_amount" name="starting_amount" type="number" step="0.01" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('starting_amount')" class="mt-2" />
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between mt-6">
                            <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">
                                {{ __('Cancel') }}
                            </x-danger-button>
                            <x-primary-button id="nextButton" type="submit" disabled>
                                {{ __('Next') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Enable/disable Next button based on form input validation
            const form = document.querySelector('form');
            const nextButton = document.getElementById('nextButton');

            form.addEventListener('input', function () {
                const isValid = form.checkValidity();
                nextButton.disabled = !isValid;
            });

            // Character count logic
            const fields = [
                { id: 'name', max: 255 },
                { id: 'link', max: 255 },
                { id: 'details', max: 5000 },
            ];

            fields.forEach(field => {
                const input = document.getElementById(field.id);
                const countDisplay = document.getElementById(`${field.id}-count`);

                input.addEventListener('input', function () {
                    const length = input.value.length;
                    countDisplay.textContent = `${length} / ${field.max}`;
                });
            });
        });
    </script>
</x-app-layout>
