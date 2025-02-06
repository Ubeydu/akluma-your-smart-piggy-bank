<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Piggy Bank Details') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="py-6 px-8">
                    <!-- Editable Fields Form -->
                    <form method="POST" action="{{ route('piggy-banks.update', $piggyBank) }}" class="space-y-6" x-data="{ isEditing: false }">
                        @csrf
                        @method('PUT')

                        <!-- Name (Editable) -->
                        <div>
                            <x-input-label for="name" :value="__('Product Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name', $piggyBank->name)" required
                                          x-bind:disabled="!isEditing" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Details (Editable) -->
                        <div>
                            <x-input-label for="details" :value="__('Details')" />
                            <textarea id="details"
                                      name="details"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                      rows="3"
                                      :disabled="!isEditing">{{ old('details', $piggyBank->details) }}</textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>


                        <!-- Save and Cancel Buttons -->
                        <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-end sm:space-y-0 sm:gap-3">
                            <!-- Edit button -->
                            <template x-if="!isEditing">
                                <x-secondary-button type="button" @click="isEditing = true">
                                    {{ __('Edit') }}
                                </x-secondary-button>
                            </template>

                            <!-- Save and Cancel buttons -->
                            <template x-if="isEditing">
                                <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3">
                                    <div x-data="{ showConfirmCancel: false }">
                                        <x-danger-button type="button" @click.prevent="showConfirmCancel = true" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                            {{ __('Cancel') }}
                                        </x-danger-button>

                                        <template x-if="showConfirmCancel">
                                            <x-confirmation-dialog>
                                                <x-slot:title>
                                                    {{ __('Are you sure you want to cancel?') }}
                                                </x-slot>

                                                <x-slot:actions>
                                                    <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                        <form action="{{ route('piggy-banks.cancel', $piggyBank) }}" method="POST" class="block">
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
                                        </template>
                                    </div>

                                    <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                        {{ __('Save') }}
                                    </x-primary-button>
                                </div>
                            </template>
                        </div>



                    </form>

                    <!-- Non-editable Fields -->
                    <div class="mt-8 space-y-6">
                        <!-- Image -->
                        <div class="w-32 md:w-48 mx-auto">
                            <div class="aspect-square relative overflow-hidden rounded-lg shadow-sm bg-gray-50">
                                <img src="{{ asset($piggyBank->preview_image) }}"
                                     alt="{{ $piggyBank->name }}"
                                     class="absolute inset-0 w-full h-full object-contain" />
                            </div>
                        </div>

                        <!-- Other Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Financial Information -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Item Price') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->price, $piggyBank->currency) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Starting Amount') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->starting_amount, $piggyBank->currency) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Final Total') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->final_total, $piggyBank->currency) }}</p>
                                </div>


                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Current Balance') }}</h3>
                                    <p id="current-balance-{{ $piggyBank->id }}" class="mt-1 text-base text-gray-900" data-currency="{{ $piggyBank->currency }}"
                                       data-locale="{{ app()->getLocale() }}">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->current_balance, $piggyBank->currency) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('remaining_amount') }}</h3>
                                    <p id="remaining-amount-{{ $piggyBank->id }}" class="mt-1 text-base text-gray-900" data-currency="{{ $piggyBank->currency }}"
                                       data-locale="{{ app()->getLocale() }}">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}</p>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('piggy_bank_ID') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $piggyBank->id }}</p>
                                </div>


                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Status') }}</h3>

                                    <select id="piggy-bank-status-{{ $piggyBank->id }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            data-initial-status="{{ $piggyBank->status }}">
                                        @foreach(\App\Models\PiggyBank::getStatusOptions() as $statusOption)
                                            <option value="{{ $statusOption }}" {{ $piggyBank->status === $statusOption ? 'selected' : '' }}>
                                                {{ __(strtolower($statusOption)) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <p id="status-text-{{ $piggyBank->id }}" class="mt-1 text-base text-gray-900">
                                        {{ ucfirst($piggyBank->status) }}
                                    </p>
                                </div>


                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ __(strtolower($piggyBank->selected_frequency)) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('saving_goal_reach_date') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ $piggyBank->scheduledSavings()->orderByDesc('saving_number')->first()->saving_date->translatedFormat('d F Y') }}
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Link') }}</h3>
                                    @if($piggyBank->link)
                                        <a href="{{ $piggyBank->link }}" target="_blank"
                                           class="mt-1 text-base text-blue-600 hover:text-blue-800 break-all">
                                            {{ $piggyBank->link }}
                                        </a>
                                    @else
                                        <p class="mt-1 text-base text-gray-900">-</p>
                                    @endif
                                </div>


                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('created_at') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ $piggyBank->created_at?->translatedFormat('d F Y H:i:s') ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('updated_at') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ $piggyBank->updated_at?->translatedFormat('d F Y H:i:s') ?? '-' }}
                                    </p>
                                </div>


                            </div>
                        </div>


                        @if(app()->environment('local'))
                            <div class="bg-yellow-50 p-4 rounded-lg mb-4 border border-yellow-200">
                                <h3 class="font-semibold text-yellow-800 mb-2">Test Tools</h3>
                                <div class="flex items-center gap-4">
                                    <form action="{{ route('test.set-date', $piggyBank->id) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input
                                                type="date"
                                                name="test_date"
                                                class="rounded-md border-gray-300"
                                                value="{{ session('test_date') ?? now()->format('Y-m-d') }}"
                                        >
                                        <button type="submit" class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                            Set Test Date
                                        </button>
                                    </form>
                                    @if(session('test_date'))
                                        <form action="{{ route('test.clear-date', $piggyBank->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                                Clear Test Date
                                            </button>
                                        </form>
                                    @endif
                                    <div class="text-sm text-gray-600">
                                        Current test date:
                                        <span class="font-medium">
                    {{ session('test_date') ? Carbon\Carbon::parse(session('test_date'))->format('Y-m-d') : 'Not set' }}
                </span>
                                    </div>
                                </div>
                            </div>
                        @endif


                        <!-- Savings Schedule -->
                        <div id="schedule-container">
                            @include('partials.schedule')
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.piggyBankTranslations = {
            active: "{{ __('active') }}",
            paused: "{{ __('paused') }}",
            done: "{{ __('done') }}",
            cancelled: "{{ __('cancelled') }}",
            success: "{{ __('success') }}",
            info: "{{ __('info') }}",
            goal_completed: "{{ __('You have successfully completed your savings goal.') }}",
            paused_message: "{{ __('paused_message') }}"
        };
    </script>



    @vite(['resources/js/scheduled-savings.js'])


</x-app-layout>



