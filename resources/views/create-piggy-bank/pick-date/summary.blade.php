<x-app-layout>
{{--    @if(request()->has('debug')) @dump(session()->all()) @endif--}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
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
                                <div class="aspect-square h-32 md:aspect-auto md:h-32 relative overflow-hidden rounded-lg shadow-sm bg-gray-50 mx-auto">
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
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Target Date') }}</h3>
                                <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step3']['date'] instanceof Carbon\Carbon
    ? $summary['pick_date_step3']['date']->copy()->setTimezone(config('app.timezone'))->locale(App::getLocale())->isoFormat('LL')
    : Carbon\Carbon::parse($summary['pick_date_step3']['date'])->utc()->setTimezone(config('app.timezone'))->locale(App::getLocale())->isoFormat('LL') }}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ ucfirst(__(strtolower($summary['pick_date_step3']['selected_frequency']))) }}
                                </p>
                            </div>

                            @if($dateMessage)
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                    <p class="text-blue-700">{{ $dateMessage }}</p>
                                </div>
                            @endif

                            @if(isset($summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['message']))
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Additional Information') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['message'] }}</p>
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
                                    {{ isset($summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['target_amount']['amount']) ?
                                    $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['target_amount']['amount']->formatTo(App::getLocale()) : '-' }}
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
                                            {{ __('Extra Savings Tooltip Info') }}
                                    </div>
                                    </span>
                                </h3>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">
                                            {{ isset($summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['extra_savings']['amount']) ?
                                                $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['extra_savings']['amount']->formatTo(App::getLocale()) : '-' }}
                                    </p>
                            </div>



                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Total Savings') }}</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                  {{ isset($summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['total_savings']['amount']) ?
                                  $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['total_savings']['amount']->formatTo(App::getLocale()) : '-' }}
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
                        <h3 class="text-sm font-medium text-gray-500">{{ __('Final Total') }}</h3>
                        <p class="mt-1 text-xl font-bold text-gray-900">
                            @php
                                $startingAmount = $summary['pick_date_step1']['starting_amount'] ?? null;
                                $totalSavings = $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['total_savings']['amount'] ?? null;

                                // If both amounts exist, add them. If only one exists, use that.
                                if ($startingAmount && $totalSavings) {
                                    $finalTotal = $startingAmount->plus($totalSavings);
                                } elseif ($startingAmount) {
                                    $finalTotal = $startingAmount;
                                } elseif ($totalSavings) {
                                    $finalTotal = $totalSavings;
                                } else {
                                    $finalTotal = null;
                                }
                            @endphp

                            {{ $finalTotal ? $finalTotal->formatTo(App::getLocale()) : '-' }}
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
                                        <form action="{{ route('create-piggy-bank.cancel') }}" method="POST" class="block">
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

                        <!-- Previous and Create buttons  -->
                        <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                            <x-secondary-button type="button" class="w-[200px] sm:w-auto justify-center sm:justify-start" onclick="window.location='{{ route('create-piggy-bank.pick-date.step-3') }}'">
                                {{ __('Previous') }}
                            </x-secondary-button>



                            <form method="POST" action="{{ route('create-piggy-bank.pick-date.store') }}" class="mt-4">
                                @csrf
                                <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                    {{ __('Create Piggy Bank') }}
                                </x-primary-button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



