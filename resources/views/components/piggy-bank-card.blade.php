@props(['piggyBank', 'newPiggyBankId' => null, 'newPiggyBankCreatedTime' => null])

<a href="{{ localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]) }}" class="block text-current hover:no-underline">
    <div class="p-5 border rounded-lg shadow-md bg-white hover:bg-gray-50 transition-all duration-300 piggy-bank-card"
         data-piggy-bank-id="{{ $piggyBank->id }}"
         data-new-piggy-bank-id="{{ $newPiggyBankId }}"
         data-new-piggy-bank-time="{{ $newPiggyBankCreatedTime }}">

        <!-- Change the parent div from "flex items-start mb-4" to include flex-wrap -->
        <div class="flex items-start flex-wrap mb-4">

            <!-- Piggy Bank Image - keep as is -->
            <div class="mr-4 w-16 h-16 shrink-0">
                <img src="{{ asset($piggyBank->preview_image) }}" alt="{{ $piggyBank->name }}" class="w-full h-full object-cover rounded-lg shadow-xs">
            </div>

            <!-- Title Section - add w-[calc(100%-5rem)] to limit width on small screens -->
            <div class="flex-1 w-[calc(100%-5rem)]">
                <!-- Add truncate to the h3 to prevent overflow -->
                <h3 class="text-lg font-bold text-gray-900 mb-0.5 truncate">{{ $piggyBank->name }}</h3>
                <div class="flex items-center gap-1">
                    <span class="text-sm text-gray-500">{{ __('piggy_bank_ID') }} {{ $piggyBank->id }}</span>
                    @if($piggyBank->vault_id)
                        <span class="text-sm text-gray-500">•</span>
                        <span class="text-sm text-gray-500">{{ __('Vault') }}: {{ Str::limit($piggyBank->vault->name, 15) }}</span>
                    @endif
                </div>

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
                $finalTotal = $piggyBank->final_total ?? 0;
                $remaining = $piggyBank->remaining_amount ?? 0;
                if ($finalTotal > 0) {
                    if ($remaining > 0) {
                        $percent = round((1 - ($remaining / $finalTotal)) * 100);
                    } else {
                        $percent = 100;
                    }
                    $percent = max(0, min(100, $percent));
                } else {
                    $percent = 0;
                }

                // Consistent color mapping with the badge
                $progressColors = [
                    'active' => 'bg-green-500',
                    'paused' => 'bg-yellow-500',
                    'done' => 'bg-blue-500',
                    'cancelled' => 'bg-red-500'
                ];
                $progressColor = $progressColors[$piggyBank->status] ?? 'bg-indigo-500';
            @endphp
            <div class="{{ $progressColor }} h-full rounded-full" style="width: {{ $percent }}%"></div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Left Column -->
            <div>
                <!-- Original Goal -->
                <div class="mb-1">
                    <span class="text-xs text-gray-500 block">{{ __('Original Goal') }}</span>
                    <span class="text-sm font-semibold text-gray-900">
                        {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->final_total, $piggyBank->currency) }}
                    </span>
                </div>

                <!-- Current Projected Total (if different from original and not done/cancelled) -->
                @if($piggyBank->uptodate_final_total && $piggyBank->uptodate_final_total != $piggyBank->final_total && !in_array($piggyBank->status, ['done', 'cancelled']))
                <div class="mb-1">
                    <span class="text-xs text-gray-500 block">{{ __('Current Projected') }}</span>
                    <span class="text-sm font-semibold text-indigo-700">
                        {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->uptodate_final_total, $piggyBank->currency) }}
                    </span>
                </div>
                @endif

                <!-- Actual (live) -->
                <div class="mb-3">
                    <span class="text-xs text-gray-500 block">{{ __('Total Money You Saved') }}</span>
                    <span class="text-sm font-semibold text-blue-900">
                        {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->actual_final_total_saved, $piggyBank->currency) }}
                    </span>
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
                    <div class="mb-1 text-xs text-indigo-700 font-semibold">
                        {{ $statusMessage }}
                    </div>
                @endif

            </div>

            <!-- Right Column -->
            <div>
                <div class="mb-3">
                    <span class="text-xs text-gray-500 block">{{ __('remaining_amount') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}</span>
                </div>

                @php
                    $showActualCompletionDate = $piggyBank->status === 'done'
                        && $piggyBank->actual_completed_at
                        && $piggyBank->actual_completed_at->format('Y-m-d') !== optional($piggyBank->scheduledSavings()->orderByDesc('saving_number')->first()->saving_date)->format('Y-m-d');
                @endphp

                @if(!$showActualCompletionDate)
                <div class="mb-3">
                    <span class="text-xs text-gray-500 block">{{ __('saving_goal_reach_date') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $piggyBank->scheduledSavings()->orderByDesc('saving_number')->first()->saving_date->translatedFormat('d F Y') }}</span>
                </div>
                @endif

                @if($showActualCompletionDate)
                    <div class="mb-3">
                        <span class="text-xs text-green-700 block">{{ __('actual_completed_at_label') }}</span>
                        <span class="text-sm font-semibold text-green-700">{{ $piggyBank->actual_completed_at->translatedFormat('d F Y') }}</span>
                    </div>
                @endif

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
