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
                    <form method="POST" action="{{ route('piggy-banks.update', $piggyBank) }}" class="space-y-6" x-data="{ isEditing: false }" x-ref="editForm">
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

                                                        <x-danger-button
                                                                @click="isEditing = false;
                                                                    showConfirmCancel = false;
                                                                    $refs.editForm.reset();
                                                                    window.location.href = '{{ route('piggy-banks.show', $piggyBank) }}?cancelled=1';"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >

                                                            {{ __('Yes, proceed') }}
                                                        </x-danger-button>

                                                        <x-secondary-button
                                                                @click="showConfirmCancel = false"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('No, cancel') }}
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



                                <div x-data="{
    showConfirmCancel: false,
    statusChangeAction: '',
    statusChangeMessage: ''
}">

                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Status') }}</h3>

                                    <div class="mt-1 space-y-2 sm:space-y-0">

                                        {{-- Current Status Display --}}
                                        <div class="mb-3">
                                            <span class="text-sm text-gray-500 block mb-1">{{ __('Current Status') }}</span>
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-md
                                                    {{ $piggyBank->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $piggyBank->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $piggyBank->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $piggyBank->status === 'done' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                    <div id="status-text-{{ $piggyBank->id }}" class="font-medium">
                                                        {{ ucfirst(__(strtolower($piggyBank->status))) }}
                                                    </div>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Status Change Actions --}}
                                        <div class="relative inline-block w-full sm:w-64 z-30">
                                            <label for="piggy-bank-status-{{ $piggyBank->id }}" class="text-sm text-gray-500 block mb-1">
                                                {{ __('Change Status') }}
                                            </label>
                                            <select id="piggy-bank-status-{{ $piggyBank->id }}"
                                                    class="block w-full text-base border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 {{ in_array($piggyBank->status, ['done', 'cancelled']) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    data-initial-status="{{ $piggyBank->status }}"
                                                    {{ in_array($piggyBank->status, ['done', 'cancelled']) ? 'disabled' : '' }}>
                                                @foreach(\App\Models\PiggyBank::getStatusOptions() as $statusOption)
                                                    <option value="{{ $statusOption }}" {{ $piggyBank->status === $statusOption ? 'selected' : '' }}>
                                                        {{ __(strtolower($statusOption)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <template x-if="showConfirmCancel">
                                            <x-confirmation-dialog>
                                                <x-slot:title>
                                                    <span x-text="statusChangeMessage"></span>
                                                </x-slot>

                                                <x-slot:actions>
                                                    <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                        <x-danger-button
                                                                @click="await statusChangeAction(); showConfirmCancel = false;"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('Yes, proceed') }}
                                                        </x-danger-button>

                                                        <x-secondary-button
                                                                @click="showConfirmCancel = false; console.log('No clicked')"
                                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                        >
                                                            {{ __('No, cancel') }}
                                                        </x-secondary-button>
                                                    </div>
                                                </x-slot:actions>
                                            </x-confirmation-dialog>
                                        </template>


                                    </div>
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
                        <div id="schedule-container" class="bg-rose-50 rounded-lg">
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
            paused_message: "{{ __('paused_message') }}",
            confirm_pause: "{{ __('Are you sure you want to pause this piggy bank?') }}",
            confirm_cancel: "{{ __('Are you sure you want to cancel this piggy bank?') }}",
            confirm_cancel_paused: "{{ __('Are you sure you want to cancel this paused piggy bank?') }}",
            confirm_resume: "{{ __('Are you sure you want to resume this piggy bank? Dates in your saving schedule may be updated if you proceed.') }}",
            piggy_bank_cancelled: "{{ __('Piggy bank has been cancelled.') }}",
        };
    </script>



    @vite(['resources/js/scheduled-savings.js'])


</x-app-layout>



