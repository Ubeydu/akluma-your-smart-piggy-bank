@php use Brick\Money\Money; @endphp
<x-app-layout>
    {{--    @if(request()->has('debug')) @dump(session()->all()) @endif--}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-6 px-8">
                    <h1 class="text-2xl font-semibold mb-6">{{ __('Summary') }}</h1>

                    <!-- Product Information Section -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Product Details') }}</h2>
                        <!-- This grid will only contain the basic info and image -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Name') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step1']['name'] }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Price') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ isset($summary['pick_date_step1']['price']) ? $summary['pick_date_step1']['price']->formatTo(App::getLocale()) : '-' }}
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Starting Amount') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ isset($summary['pick_date_step1']['starting_amount']) ? $summary['pick_date_step1']['starting_amount']->formatTo(App::getLocale()) : '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="w-48 mx-auto mt-1">
                                <div class="aspect-square h-32 md:aspect-auto md:h-32 relative overflow-hidden rounded-lg shadow-xs bg-gray-50 mx-auto">
                                    <div class="relative w-full h-full">
                                        @php
                                            $previewImage = $summary['pick_date_step1']['preview']['image'] ?? null;
                                            $imageUrl = $previewImage ?: asset('images/default_piggy_bank.png');
                                        @endphp
                                        <img
                                            src="{{ $summary['pick_date_step1']['preview']['image'] ?? asset('images/default_piggy_bank.png') }}"
                                            alt="{{ $summary['pick_date_step1']['name'] }}"
                                            class="absolute inset-0 w-full h-full object-contain"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Moved outside the grid -->
                        <div class="space-y-4 mt-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Product Link') }}</h3>
                                @if(isset($summary['pick_date_step1']['link']))
                                    <a href="{{ $summary['pick_date_step1']['link'] }}" target="_blank" class="mt-1 text-base text-blue-600 hover:text-blue-800 break-all">{{ $summary['pick_date_step1']['link'] }}</a>
                                @else
                                    <p class="mt-1 text-base text-gray-900">-</p>
                                @endif
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Details') }}</h3>
                                <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step1']['details'] ?? '-' }}</p>
                            </div>
                        </div>
                    </div>


                    <!-- Savings Plan Section -->
                    <div class="mb-8">
                        {{--                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Savings Plan') }}</h2>--}}
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ ucfirst(__(strtolower($summary['enter_saving_amount_step3']['selected_frequency']))) }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Target Date') }}</h3>
                                <p class="mt-1 text-base text-gray-900">{{ $summary['enter_saving_amount_step3']['target_dates'][$summary['enter_saving_amount_step3']['selected_frequency']]['target_date'] }}</p>
                            </div>

                            @if($dateMessage)
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                    <p class="text-blue-700">{{ $dateMessage }}</p>
                                </div>
                            @endif

                            @if(isset($summary['enter_saving_amount_step3']['target_dates'][$summary['enter_saving_amount_step3']['selected_frequency']]['message']))
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Additional Information') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $summary['enter_saving_amount_step3']['target_dates'][$summary['enter_saving_amount_step3']['selected_frequency']]['message'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>


                    <!-- Financial Summary Section -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Financial Summary') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Target Amount') }}</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    @php
                                        $startingAmount = session('pick_date_step1.starting_amount');
                                        if ($startingAmount && !$startingAmount->isZero()) {
                                            $price = session('pick_date_step1.price');
                                            $targetAmount = $price->minus($startingAmount);
                                        } else {
                                            $targetAmount = session('pick_date_step1.price');
                                        }
                                    @endphp
                                    {{ $targetAmount ? $targetAmount->formatTo(App::getLocale()) : '-' }}
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500 flex items-center gap-1">
                                    {{ __('Extra Savings') }}
                                    <span x-data="{ showTooltip: false }" class="relative cursor-help">
                                        <svg @mouseenter="showTooltip = true"
                                             @mouseleave="showTooltip = false"
                                             xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             class="w-4 h-4 text-gray-500 hover:text-gray-800 transition-colors duration-200">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                        </svg>

                                    <div x-show="showTooltip"
                                         x-cloak
                                         class="absolute z-10 w-64 px-4 py-2 mt-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg -translate-x-1/2 left-1/2"
                                         role="tooltip">
                                            {{ __('Extra Savings Tooltip Info For Enter Saving Amount Strategy') }}
                                    </div>
                                    </span>
                                </h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $extraSavings ? $extraSavings->formatTo(App::getLocale()) : '-' }}
                                </p>
                            </div>



                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Total Savings') }}</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $summary['enter_saving_amount_step3']['target_dates'][$summary['enter_saving_amount_step3']['selected_frequency']]['total_amount']['formatted_value'] ?? '-' }}
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- Payment Schedule Section -->
                    @if(isset($paymentSchedule) && count($paymentSchedule) > 0)
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Saving Schedule') }}</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words max-w-[40px]">
                                            {{ __('Saving #') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Date') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Amount') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paymentSchedule as $payment)
                                        <tr>
                                            <td class="px-1 py-4 whitespace-normal text-sm font-medium text-gray-900">
                                                {{ $payment['payment_number'] ?? '-' }}
                                            </td>
                                            <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $payment['formatted_date'] ?? '-' }}
                                            </td>
                                            <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payment['amount']->formatTo(App::getLocale()) ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif


                    <div class="bg-gray-100 rounded-lg p-4 border-2 border-gray-800 shadow-md">
                        <h3 class="text-sm font-medium text-gray-500">{{ __('Planned Final Total') }}</h3>
                        <p class="mt-1 text-xl font-bold text-gray-900">
                            {{ $plannedFinalTotal ? $plannedFinalTotal->formatTo(App::getLocale()) : '-' }}
                        </p>
                    </div>



                    <!-- Action Buttons -->
                    <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-between sm:space-y-0 mt-8">

                        <!-- Cancel button with confirmation dialog -->
                        <div x-data="{ showConfirmCancel: false }">
                            <x-danger-button
                                @click="showConfirmCancel = true"
                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                            >
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

                        <!-- Previous, Save as Draft, and Create buttons  -->
                        <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                            <x-secondary-button type="button" class="w-[200px] sm:w-auto justify-center sm:justify-start" onclick="window.location='{{ localizedRoute('localized.create-piggy-bank.enter-saving-amount.step-3') }}'">
                                {{ __('Previous') }}
                            </x-secondary-button>

                            @auth
                                @if(auth()->user()->hasVerifiedEmail())
                                    <!-- Save as Draft Button -->
                                    <form method="POST" action="{{ localizedRoute('localized.draft-piggy-banks.store') }}">
                                        @csrf
                                        <x-secondary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                            {{ __('Save as Draft') }}
                                        </x-secondary-button>
                                    </form>

                                    <!-- Create Button -->
                                    <form method="POST" action="{{ localizedRoute('localized.create-piggy-bank.enter-saving-amount.store') }}">
                                        @csrf
                                        @if($activePiggyBanksCount >= $maxActivePiggyBanks)
                                            <x-primary-button type="button" disabled class="w-[200px] sm:w-auto justify-center sm:justify-start opacity-50 cursor-not-allowed">
                                                {{ __('Create New Piggy Bank') }}
                                            </x-primary-button>
                                        @else
                                            <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                {{ __('Create New Piggy Bank') }}
                                            </x-primary-button>
                                        @endif
                                    </form>
                                @else
                                    <!-- Email not verified - disabled buttons with tooltips -->
                                    <div x-data="{ showTooltip: false }" class="relative">
                                        <x-secondary-button
                                            type="button"
                                            disabled
                                            @mouseenter="showTooltip = true"
                                            @mouseleave="showTooltip = false"
                                            class="w-[200px] sm:w-auto justify-center sm:justify-start opacity-50 !cursor-not-allowed pointer-events-auto"
                                        >
                                            {{ __('Save as Draft') }}
                                        </x-secondary-button>
                                        <div x-show="showTooltip" x-cloak class="absolute z-10 px-3 py-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg -translate-x-1/2 left-1/2 bottom-full mb-2 whitespace-nowrap">
                                            {{ __('Please verify your email to use this feature') }}
                                        </div>
                                    </div>

                                    <div x-data="{ showTooltip: false }" class="relative">
                                        <x-secondary-button
                                            type="button"
                                            disabled
                                            @mouseenter="showTooltip = true"
                                            @mouseleave="showTooltip = false"
                                            class="w-[200px] sm:w-auto justify-center sm:justify-start opacity-50 !cursor-not-allowed pointer-events-auto"
                                        >
                                            {{ __('Create New Piggy Bank') }}
                                        </x-secondary-button>
                                        <div x-show="showTooltip" x-cloak class="absolute z-10 px-3 py-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg -translate-x-1/2 left-1/2 bottom-full mb-2 whitespace-nowrap">
                                            {{ __('Please verify your email to use this feature') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                {{-- Guest: Email input + Save as Draft --}}
                                <form method="POST" action="{{ localizedRoute('localized.draft-piggy-banks.guest-store') }}" class="flex flex-col sm:flex-row items-center gap-4">
                                    @csrf
                                    <div class="w-full sm:w-auto">
                                        <x-text-input
                                            type="email"
                                            name="email"
                                            required
                                            placeholder="{{ __('Enter your email') }}"
                                            class="w-full sm:w-64"
                                        />
                                        @error('email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center">
                                        {{ __('Save as Draft') }}
                                    </x-primary-button>
                                </form>
                            @endauth

                        </div>
                    </div>


                    <!-- Maximum piggy banks limit warning -->
                    @auth
                        @if($activePiggyBanksCount >= $maxActivePiggyBanks)
                            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400 mt-6">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <svg class="h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm text-blue-800">
                                            {{ __("You've reached the maximum limit of :limit active or paused piggy banks. To create this piggy bank, you can either save it as a draft using the 'Save as Draft' button above, or ", ['limit' => $maxActivePiggyBanks]) }}
                                            <a href="{{ localizedRoute('localized.piggy-banks.index') }}" class="font-medium underline hover:text-blue-900">
                                                {{ __('cancel some of your active or paused piggy banks') }}
                                            </a>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endauth

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
