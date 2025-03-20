@props(['piggyBank', 'newPiggyBankId' => null, 'newPiggyBankCreatedTime' => null])

<a href="{{ route('piggy-banks.show', $piggyBank) }}" class="block text-current hover:no-underline">
    <div class="p-5 border rounded-lg shadow-md bg-white hover:bg-gray-50 transition-all duration-300 piggy-bank-card"
         data-piggy-bank-id="{{ $piggyBank->id }}"
         data-new-piggy-bank-id="{{ $newPiggyBankId }}"
         data-new-piggy-bank-time="{{ $newPiggyBankCreatedTime }}">

        <!-- Change the parent div from "flex items-start mb-4" to include flex-wrap -->
        <div class="flex items-start flex-wrap mb-4">

            <!-- Piggy Bank Image - keep as is -->
            <div class="mr-4 w-16 h-16 flex-shrink-0">
                <img src="{{ asset($piggyBank->preview_image) }}" alt="{{ $piggyBank->name }}" class="w-full h-full object-cover rounded-lg shadow-sm">
            </div>

            <!-- Title Section - add w-[calc(100%-5rem)] to limit width on small screens -->
            <div class="flex-1 w-[calc(100%-5rem)]">
                <!-- Add truncate to the h3 to prevent overflow -->
                <h3 class="text-lg font-bold text-gray-900 mb-0.5 truncate">{{ $piggyBank->name }}</h3>
                <div class="text-sm text-gray-500">{{ __('piggy_bank_ID') }} {{ $piggyBank->id }}</div>
            </div>

            <!-- Status Badge - move to below title on small screens -->
            <div class="mt-2 md:mt-0 md:ml-2">
                @php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-800',
                        'paused' => 'bg-yellow-100 text-yellow-800',
                        'done' => 'bg-blue-100 text-blue-800',
                        'cancelled' => 'bg-red-100 text-red-800'
                    ];
                    $statusColor = $statusColors[$piggyBank->status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
            {{ __(strtolower($piggyBank->status)) }}
        </span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full h-2.5 bg-gray-200 rounded-full mb-4 overflow-hidden">
            @php
                $percent = $piggyBank->target_amount > 0
                    ? min(100, round(($piggyBank->current_balance ?? 0) / $piggyBank->target_amount * 100))
                    : 0;

                $progressColors = [
                    'active' => 'bg-indigo-500',
                    'paused' => 'bg-yellow-500',
                    'done' => 'bg-blue-500',
                    'cancelled' => 'bg-gray-500'
                ];
                $progressColor = $progressColors[$piggyBank->status] ?? 'bg-indigo-500';
            @endphp
            <div class="{{ $progressColor }} h-full rounded-full" style="width: {{ $percent }}%"></div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Left Column -->
            <div>
                <div class="mb-3">
                    <span class="text-xs text-gray-500 block">{{ __('Final Total') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->final_total, $piggyBank->currency) }}</span>
                </div>

                <div class="mb-3">
                    <span class="text-xs text-gray-500 block">{{ __('Current Balance') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->current_balance ?? 0, $piggyBank->currency) }}</span>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <div class="mb-3">
                    <span class="text-xs text-gray-500 block">{{ __('remaining_amount') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}</span>
                </div>

                <div class="mb-3">
                    <span class="text-xs text-gray-500 block">{{ __('saving_goal_reach_date') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $piggyBank->scheduledSavings()->orderByDesc('saving_number')->first()->saving_date->translatedFormat('d F Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</a>

<style>
    @keyframes highlight {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }

    .highlight-new {
        animation: highlight 1s ease-in-out 3;
    }
</style>
