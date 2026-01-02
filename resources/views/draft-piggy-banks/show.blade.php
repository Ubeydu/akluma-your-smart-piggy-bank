@php
    use Brick\Money\Money;
    use Carbon\Carbon;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Draft Details') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-6 px-8">

                    {{-- Draft Status Badge --}}
                    <div class="mb-4">
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            {{ __('Draft') }} • {{ __('draft.saved_ago', ['time' => $draft->created_at->diffForHumans()]) }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-semibold mb-6">{{ $summary['pick_date_step1']['name'] }}</h1>

                    {{-- Product Information Section --}}
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Product Details') }}</h2>
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

                                @if(isset($summary['pick_date_step1']['starting_amount']) && $summary['pick_date_step1']['starting_amount'])
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Starting Amount') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ $summary['pick_date_step1']['starting_amount']->formatTo(App::getLocale()) }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            <div class="w-48 mx-auto mt-1">
                                <div class="aspect-square h-32 md:aspect-auto md:h-32 relative overflow-hidden rounded-lg shadow-xs bg-gray-50 mx-auto">
                                    <div class="relative w-full h-full">
                                        <img
                                            src="{{ $summary['pick_date_step1']['preview']['image'] ?? asset('images/default_piggy_bank.png') }}"
                                            alt="{{ $summary['pick_date_step1']['name'] }}"
                                            class="absolute inset-0 w-full h-full object-contain"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Product Link and Details (outside grid) --}}
                        <div class="space-y-4 mt-6">
                            @if(isset($summary['pick_date_step1']['link']) && $summary['pick_date_step1']['link'])
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Product Link') }}</h3>
                                <a href="{{ $summary['pick_date_step1']['link'] }}"
                                   target="_blank"
                                   class="mt-1 text-base text-blue-600 hover:text-blue-800 break-all">
                                    {{ $summary['pick_date_step1']['link'] }}
                                </a>
                            </div>
                            @endif

                            @if(isset($summary['pick_date_step1']['details']) && $summary['pick_date_step1']['details'])
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Details') }}</h3>
                                <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step1']['details'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Savings Plan Section --}}
                    <div class="mb-8">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Target Date') }}</h3>
                                <p class="mt-1 text-base text-gray-900">
                                    @if($draft->strategy === 'pick-date')
                                        @php
                                            $targetDate = $summary['pick_date_step3']['date'];
                                            if ($targetDate instanceof Carbon) {
                                                echo $targetDate->translatedFormat('d F Y');
                                            } else {
                                                echo Carbon::parse($targetDate)->translatedFormat('d F Y');
                                            }
                                        @endphp
                                    @else
                                        @php
                                            $targetDateString = $summary['enter_saving_amount_step3']['target_dates'][$draft->frequency]['target_date'] ?? null;
                                            if ($targetDateString) {
                                                echo Carbon::parse($targetDateString)->translatedFormat('d F Y');
                                            } else {
                                                echo '-';
                                            }
                                        @endphp
                                    @endif
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ ucfirst(__(strtolower($draft->frequency))) }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Strategy') }}</h3>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ __('draft.strategy.' . $draft->strategy) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Date Message (early completion notice) --}}
                    @if(isset($dateMessage) && $dateMessage)
                        <div class="mb-8 bg-blue-50 border-l-4 border-blue-400 p-4">
                            <p class="text-blue-700">{{ $dateMessage }}</p>
                        </div>
                    @endif

                    {{-- Financial Summary Section --}}
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Financial Summary') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Target Amount') }}</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
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
                                            @if($draft->strategy === 'pick-date')
                                                {{ __('Extra Savings Tooltip Info') }}
                                            @else
                                                {{ __('Extra Savings Tooltip Info For Enter Saving Amount Strategy') }}
                                            @endif
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
                                    {{ $totalSavings ? $totalSavings->formatTo(App::getLocale()) : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Schedule Section --}}
                    @if(isset($paymentSchedule) && count($paymentSchedule) > 0)
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Saving Schedule') }}</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            {{ __('Saving #') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            {{ __('Date') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            {{ __('Amount') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paymentSchedule as $payment)
                                        <tr>
                                            <td class="px-2 py-4 text-sm font-medium text-gray-900">
                                                {{ $payment['payment_number'] ?? '-' }}
                                            </td>
                                            <td class="px-2 py-4 text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($payment['date'])->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-2 py-4 text-sm text-gray-900">
                                                {{ $payment['amount']->formatTo(App::getLocale()) ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Planned Final Total --}}
                    <div class="bg-gray-100 rounded-lg p-4 border-2 border-gray-800 shadow-md mb-8">
                        <h3 class="text-sm font-medium text-gray-500">{{ __('Planned Final Total') }}</h3>
                        <p class="mt-1 text-xl font-bold text-gray-900">
                            {{ $plannedFinalTotal ? $plannedFinalTotal->formatTo(App::getLocale()) : '-' }}
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-between sm:space-y-0 mt-8">

                        {{-- Back to List --}}
                        <a href="{{ localizedRoute('localized.draft-piggy-banks.index') }}"
                           class="w-[200px] sm:w-auto text-center">
                            <x-secondary-button type="button" class="w-full justify-center">
                                {{ __('Back to Drafts') }}
                            </x-secondary-button>
                        </a>

                        {{-- Resume and Delete --}}
                        <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">

                            {{-- Delete with confirmation --}}
                            <div x-data="{ showConfirmDelete: false }">
                                <x-danger-button
                                    @click="showConfirmDelete = true"
                                    class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                >
                                    {{ __('Delete Draft') }}
                                </x-danger-button>

                                <x-confirmation-dialog :show="'showConfirmDelete'">
                                    <x-slot:title>
                                        {{ __('Are you sure you want to delete this draft?') }}
                                    </x-slot>

                                    <x-slot:actions>
                                        <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                            <form action="{{ localizedRoute('localized.draft-piggy-banks.destroy', ['draft' => $draft->id]) }}"
                                                  method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                    {{ __('Yes, delete') }}
                                                </x-danger-button>
                                            </form>

                                            <x-secondary-button
                                                @click="showConfirmDelete = false"
                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                            >
                                                {{ __('Cancel') }}
                                            </x-secondary-button>
                                        </div>
                                    </x-slot:actions>
                                </x-confirmation-dialog>
                            </div>

                            {{-- Resume with session warning if needed --}}
                            <div x-data="{ showSessionWarning: false }">
                                @if($hasActiveSession)
                                    {{-- Show warning button if active session --}}
                                    <x-primary-button
                                        @click="showSessionWarning = true"
                                        class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                    >
                                        {{ __('Resume Draft') }}
                                    </x-primary-button>

                                    <x-confirmation-dialog :show="'showSessionWarning'">
                                        <x-slot:title>
                                            {{ __('draft.session_warning.title') }}
                                        </x-slot>

                                        <x-slot:content>
                                            <p class="text-sm text-gray-600">
                                                {{ __('draft.session_warning.message') }}
                                            </p>
                                        </x-slot:content>

                                        <x-slot:actions>
                                            <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                <form action="{{ localizedRoute('localized.draft-piggy-banks.resume', ['draft' => $draft->id]) }}"
                                                      method="POST">
                                                    @csrf
                                                    <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                        {{ __('draft.session_warning.resume_button') }}
                                                    </x-primary-button>
                                                </form>

                                                <x-secondary-button
                                                    @click="showSessionWarning = false"
                                                    class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                >
                                                    {{ __('Cancel') }}
                                                </x-secondary-button>
                                            </div>
                                        </x-slot:actions>
                                    </x-confirmation-dialog>
                                @else
                                    {{-- No active session, resume directly --}}
                                    <form action="{{ localizedRoute('localized.draft-piggy-banks.resume', ['draft' => $draft->id]) }}"
                                          method="POST">
                                        @csrf
                                        <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                            {{ __('Resume Draft') }}
                                        </x-primary-button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
