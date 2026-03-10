<div
    id="financial-summary-container"
    data-financial-summary-url="{{ localizedRoute('localized.piggy-banks.financial-summary', ['piggy_id' => $piggyBank->id]) }}"
    x-data="{ showMoreDetails: false }"
>
    <div class="space-y-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ __('Total Money Saved') }}</h3>
            <p class="mt-1 text-xl font-bold text-blue-900">
                {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->actual_final_total_saved, $piggyBank->currency) }}
            </p>
        </div>

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

        @if($piggyBank->status === 'done' && $piggyBank->actual_completed_at)
            <div>
                <h3 class="text-sm font-medium text-gray-500">{{ __('actual_completed_at_label') }}</h3>
                <p class="mt-1 text-base text-green-700 font-semibold">
                    {{ $piggyBank->actual_completed_at->translatedFormat('d F Y') }}
                </p>
            </div>
        @endif

        @if(isset($manualTransactions) && $manualTransactions->isNotEmpty())
        <div class="pt-2 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('classic_transaction_history') }}</h3>
            <ul class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($manualTransactions as $tx)
                <li class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 text-sm">
                    <span class="text-gray-500 shrink-0">{{ $tx->created_at->translatedFormat('d M Y, H:i') }}</span>
                    <span class="{{ $tx->amount > 0 ? 'text-green-700 font-medium' : 'text-orange-700 font-medium' }}">
                        {{ $tx->amount > 0 ? '+' : '' }}{{ \App\Helpers\MoneyFormatHelper::format($tx->amount, $piggyBank->currency) }}
                    </span>
                    @if($tx->note)
                        <span class="text-gray-600 italic">— {{ Str::limit($tx->note, 60) }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
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
                <h3 class="text-sm font-medium text-gray-500">{{ __('piggy_bank_ID') }}</h3>
                <p class="mt-1 text-base text-gray-900">{{ $piggyBank->id }}</p>
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
</div>
