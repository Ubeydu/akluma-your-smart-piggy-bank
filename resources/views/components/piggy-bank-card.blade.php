@props(['piggyBank', 'newPiggyBankId' => null])

<div class="p-4 border rounded-lg shadow bg-rose-50 hover:bg-rose-100 transition-colors duration-300
    {{ $newPiggyBankId == $piggyBank->id ? 'highlight-new' : '' }}">
    <h3 class="text-lg font-bold">{{ $piggyBank->name }}</h3>
    <div class="flex flex-col gap-y-1">
        <div class="flex">
            <span class="text-sm text-gray-600 font-medium w-64">{{ __('Final Total') }}</span>
            <span class="text-sm text-gray-600 font-medium">: {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->final_total, $piggyBank->currency) }}</span>
        </div>

        <div class="flex">
            <span class="text-sm text-gray-600 font-medium w-64">{{ __('Current Balance') }}</span>
            <span class="text-sm text-gray-600 font-medium">: {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->current_balance ?? 0, $piggyBank->currency) }}</span>
        </div>

        <div class="flex">
            <span class="text-sm text-gray-600 font-medium w-64">{{ __('remaining_amount') }}</span>
            <span class="text-sm text-gray-600 font-medium">: {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}</span>
        </div>

        <div class="flex">
            <span class="text-sm text-gray-600 font-medium w-64">{{ __('saving_goal_reach_date') }}</span>
            <span class="text-sm text-gray-600 font-medium">: {{ $piggyBank->scheduledSavings()->orderByDesc('saving_number')->first()->saving_date->translatedFormat('d F Y') }}</span>
        </div>
    </div>
    <h3 class="text-lg font-bold">{{ $piggyBank->status }}</h3>
</div>
