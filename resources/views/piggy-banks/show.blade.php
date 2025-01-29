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
                                    <p id="piggy-bank-status-{{ $piggyBank->id }}" class="mt-1 text-base text-gray-900">{{ __(strtolower($piggyBank->status)) }}</p>
                                </div>

                                <script>
                                    window.piggyBankTranslations = {
                                        active: "{{ __('active') }}",
                                        paused: "{{ __('paused') }}",
                                        done: "{{ __('done') }}",
                                        cancelled: "{{ __('cancelled') }}"
                                    };
                                </script>

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

                        <!-- Savings Schedule -->
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Saving Schedule') }}</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words max-w-[40px]">
                                            {{ __('in_piggy_bank') }}
                                        </th>
                                        <th scope="col" class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words max-w-[40px]">
                                            {{ __('Saving #') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Date') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Amount') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Status') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($piggyBank->scheduledSavings()->paginate(50) as $saving)
                                        <tr>
                                            <td class="px-1 py-4 whitespace-normal text-sm text-gray-900">
                                                <input type="checkbox"
                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                       {{ $saving->status === 'saved' ? 'checked' : '' }}
                                                       data-saving-id="{{ $saving->id }}"
                                                       data-piggy-bank-id="{{ $piggyBank->id }}"
                                                       data-amount="{{ $saving->amount }}">
                                            </td>
                                            <td class="px-1 py-4 whitespace-normal text-sm font-medium text-gray-900">
                                                {{ $saving->saving_number }}
                                            </td>
                                            <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $saving->saving_date->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \App\Helpers\MoneyFormatHelper::format($saving->amount, $piggyBank->currency) }}
                                            </td>
                                            <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ __(strtolower($saving->status)) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>


                            <div class="mt-4">
                                {{ $piggyBank->scheduledSavings()->paginate(50)->links() }}
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/scheduled-savings.js'])
</x-app-layout>
