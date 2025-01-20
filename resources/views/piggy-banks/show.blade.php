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
                    <form method="POST" action="{{ route('piggy-banks.update', $piggyBank) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Name (Editable) -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name', $piggyBank->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Details (Editable) -->
                        <div>
                            <x-input-label for="details" :value="__('Details')" />
                            <x-textarea-input id="details" name="details" class="mt-1 block w-full"
                                              rows="3">{{ old('details', $piggyBank->details) }}</x-textarea-input>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>

                        <!-- Save Changes Button -->
                        <div class="flex justify-end">
                            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        </div>
                    </form>

                    <!-- Non-editable Fields -->
                    <div class="mt-8 space-y-6">
                        <!-- Image -->
                        <div class="w-48 mx-auto">
                            <div class="aspect-square h-32 md:aspect-auto md:h-32 relative overflow-hidden rounded-lg shadow-sm bg-gray-50">
                                <img src="{{ $piggyBank->preview_image ?? asset('images/default_piggy_bank.png') }}"
                                     alt="{{ $piggyBank->name }}"
                                     class="absolute inset-0 w-full h-full object-contain" />
                            </div>
                        </div>

                        <!-- Other Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Financial Information -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Price') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->price, $piggyBank->currency) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Starting Amount') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->starting_amount, $piggyBank->currency) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Current Balance') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->current_balance, $piggyBank->currency) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Remaining Amount') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}</p>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Status') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ __($piggyBank->status) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ __(strtolower($piggyBank->selected_frequency)) }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Goal Reach Date') }}</h3>
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
                            </div>
                        </div>

                        <!-- Saving Schedule Link -->
                        <div class="mt-8">
                            <a href="{{ route('piggy-banks.schedule', $piggyBank) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                {{ __('View Saving Schedule') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
