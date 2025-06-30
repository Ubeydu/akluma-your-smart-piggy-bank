<div
    id="financial-summary-container"
    data-financial-summary-url="{{ localizedRoute('localized.piggy-banks.financial-summary', ['piggy_id' => $piggyBank->id]) }}"
>
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
            <h3 class="text-sm font-medium text-gray-500">{{ __('Planned Final Total') }}</h3>
            <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->final_total, $piggyBank->currency) }}</p>
        </div>

        @php
            // Get the projected target completion date (always show)
            $projectedDate = optional($piggyBank->scheduledSavings()->orderByDesc('saving_number')->first())->saving_date;
        @endphp

        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('saving_goal_reach_date') }}</h3>
            <p class="mt-1 text-base text-gray-900">
                {{ $projectedDate ? $projectedDate->translatedFormat('d F Y') : '-' }}
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Total Money You Saved') }}</h3>
            <p class="mt-1 text-base text-blue-900 font-semibold">
                {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->actual_final_total_saved, $piggyBank->currency) }}
            </p>
        </div>

        @php
            $statusMessage = null;
            if ($piggyBank->status === 'done') {
                if ($piggyBank->actual_final_total_saved > $piggyBank->final_total) {
                    $statusMessage = __('You saved more than your goal!');
                } elseif ($piggyBank->actual_final_total_saved < $piggyBank->final_total) {
                    $statusMessage = __('You saved less than your goal.');
                } else {
                    $statusMessage = __('You reached your goal!');
                }
            }
        @endphp
        @if($statusMessage)
            <div class="mt-2 text-sm text-indigo-600 font-semibold">{{ $statusMessage }}</div>
        @endif

        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('remaining_amount') }}</h3>
            <p id="remaining-amount-{{ $piggyBank->id }}"
               class="mt-1 text-base text-gray-900"
               data-currency="{{ $piggyBank->currency }}"
               data-locale="{{ app()->getLocale() }}">
                {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}
                @if($piggyBank->remaining_amount < 0)
                    <span class="ml-2 inline-block text-xs text-green-700 bg-green-50 px-2 py-1 rounded">
                        {{ __('extra_savings_note') }}
                    </span>
                @endif
            </p>
        </div>


        {{-- Add this block HERE, right before the final two closing divs --}}
        @php
            // Get the scheduled target completion date (projected)
            $projectedDate = optional($piggyBank->scheduledSavings()->orderByDesc('saving_number')->first())->saving_date;
        @endphp

        @if($piggyBank->status === 'done' && $piggyBank->actual_completed_at
            && $projectedDate && $piggyBank->actual_completed_at->format('Y-m-d') !== $projectedDate->format('Y-m-d'))
            <div>
                <h3 class="text-sm font-medium text-gray-500">{{ __('actual_completed_at_label') }}</h3>
                <p class="mt-1 text-base text-green-700 font-semibold">
                    {{ $piggyBank->actual_completed_at->translatedFormat('d F Y') }}
                </p>
            </div>
        @endif


    </div>
</div>
