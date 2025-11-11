<div
    id="financial-summary-container"
    data-financial-summary-url="{{ localizedRoute('localized.piggy-banks.financial-summary', ['piggy_id' => $piggyBank->id]) }}"
    x-data="{ showMoreDetails: false }"
>
    <div class="space-y-4">
        <!-- Always Visible: Original Goal -->
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Original Goal') }}</h3>
            <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->final_total, $piggyBank->currency) }}</p>
        </div>

        <!-- Always Visible: Current Projected Total (conditional) -->
        @if($piggyBank->uptodate_final_total && $piggyBank->uptodate_final_total != $piggyBank->final_total)
        <div>
            <h3 class="text-sm font-medium text-gray-500">
                @if(in_array($piggyBank->status, ['done', 'cancelled']))
                    {{ __('Updated Goal') }}
                @else
                    {{ __('Current Projected Total') }}
                @endif
            </h3>
            <p class="mt-1 text-base text-indigo-700 font-semibold">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->uptodate_final_total, $piggyBank->currency) }}</p>
            @php
                $difference = $piggyBank->uptodate_final_total - $piggyBank->final_total;
                $isIncrease = $difference > 0;
            @endphp
            <p class="mt-1 text-xs text-gray-600">
                @if($isIncrease)
                    <span class="text-green-600">▲ {{ \App\Helpers\MoneyFormatHelper::format(abs($difference), $piggyBank->currency) }} {{ __('more than original') }}</span>
                @else
                    <span class="text-orange-600">▼ {{ \App\Helpers\MoneyFormatHelper::format(abs($difference), $piggyBank->currency) }} {{ __('less than original') }}</span>
                @endif
            </p>
        </div>
        @endif

        <!-- Always Visible: Total Money You Saved -->
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

        <!-- Always Visible: Remaining Amount -->
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

        @php
            $pendingCount = $piggyBank->scheduledSavings()
                ->where('status', 'pending')
                ->where('archived', false)
                ->count();
        @endphp

        @if($pendingCount === 0 && $piggyBank->status === 'active' && $piggyBank->remaining_amount > 0)
        <!-- All Scheduled Savings Completed Info -->
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-base font-medium text-blue-800">
                        {{ __('All scheduled savings completed!') }}
                    </h3>
                    <p class="mt-2 text-sm text-blue-700">
                        {{ __('However, your piggy bank is still') }}
                        <strong>{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}</strong>
                        {{ __('short of your goal because you took out money earlier.') }}
                    </p>
                    <p class="mt-2 text-sm text-blue-700">
                        {{ __('You can add money manually using the section below to reach your goal, or leave it as is.') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Always Visible: Actual Completion Date (conditional for done) -->
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

        <!-- Show More/Hide Details Toggle Button -->
        <div class="pt-2">
            <button
                type="button"
                @click="showMoreDetails = !showMoreDetails"
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-800 border border-gray-200 shadow-xs transition-colors duration-200 cursor-pointer"
            >
                <span x-text="showMoreDetails ? '{{ __('Hide Details') }}' : '{{ __('Show More') }}'"></span>
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4 ml-2 transition-transform"
                     :class="{ 'rotate-180': showMoreDetails }"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <!-- Collapsible Details Section -->
        <div x-show="showMoreDetails"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="space-y-4 pt-2 border-t border-gray-200">
            <div>
                <h3 class="text-sm font-medium text-gray-500">{{ __('Item Price') }}</h3>
                <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->price, $piggyBank->currency) }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500">{{ __('Starting Amount') }}</h3>
                <p class="mt-1 text-base text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->starting_amount, $piggyBank->currency) }}</p>
            </div>

            @if($piggyBank->status !== 'done')
            <div>
                <h3 class="text-sm font-medium text-gray-500">{{ __('saving_goal_reach_date') }}</h3>
                <p class="mt-1 text-base text-gray-900">
                    {{ $projectedDate ? $projectedDate->translatedFormat('d F Y') : '-' }}
                </p>
            </div>
            @endif

            @if($piggyBank->manual_money_net != 0)
            <div>
                <h3 class="text-sm font-medium text-gray-500">{{ __('Money you added or took out') }}</h3>
                <p class="mt-1 text-base {{ $piggyBank->manual_money_net > 0 ? 'text-green-700' : 'text-orange-700' }} font-semibold">
                    @if($piggyBank->manual_money_net > 0)
                        <span>+ {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->manual_money_net, $piggyBank->currency) }}</span>
                        <span class="text-xs text-gray-600 ml-1">({{ __('added') }})</span>
                    @else
                        <span>{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->manual_money_net, $piggyBank->currency) }}</span>
                        <span class="text-xs text-gray-600 ml-1">({{ __('took out') }})</span>
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
