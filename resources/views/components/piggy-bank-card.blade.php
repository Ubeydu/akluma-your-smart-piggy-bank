@props(['piggyBank', 'newPiggyBankId' => null])

<a href="{{ route('piggy-banks.show', $piggyBank) }}" class="block text-current hover:no-underline">
<div class="p-4 border rounded-lg shadow bg-rose-50 hover:bg-rose-100 transition-colors duration-300
    {{ $newPiggyBankId == $piggyBank->id ? 'highlight-new' : '' }}">
    <h3 class="text-lg font-bold">{{ $piggyBank->name }}</h3>
    <h3 class="text-lg font-bold">
        {{ __('piggy_bank_ID') }} {{ $piggyBank->id }}
    </h3>
    <div class="flex flex-col gap-y-4 mt-4">
        <div class="flex flex-col lg:flex-row">
            <div class="relative w-full lg:w-64">
                <span class="text-sm text-gray-600 font-medium">{{ __('Final Total') }}</span>
                <span class="text-sm text-gray-600 font-medium absolute right-0">:</span>
            </div>
            <span class="text-sm text-gray-600 font-medium mt-1 lg:mt-0 lg:ml-4">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->final_total, $piggyBank->currency) }}</span>
        </div>

        <div class="flex flex-col lg:flex-row">
            <div class="relative w-full lg:w-64">
                <span class="text-sm text-gray-600 font-medium">{{ __('Current Balance') }}</span>
                <span class="text-sm text-gray-600 font-medium absolute right-0">:</span>
            </div>
            <span class="text-sm text-gray-600 font-medium mt-1 lg:mt-0 lg:ml-4">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->current_balance ?? 0, $piggyBank->currency) }}</span>
        </div>

        <div class="flex flex-col lg:flex-row">
            <div class="relative w-full lg:w-64">
                <span class="text-sm text-gray-600 font-medium">{{ __('remaining_amount') }}</span>
                <span class="text-sm text-gray-600 font-medium absolute right-0">:</span>
            </div>
            <span class="text-sm text-gray-600 font-medium mt-1 lg:mt-0 lg:ml-4">{{ \App\Helpers\MoneyFormatHelper::format($piggyBank->remaining_amount, $piggyBank->currency) }}</span>
        </div>

        <div class="flex flex-col lg:flex-row">
            <div class="relative w-full lg:w-64">
                <span class="text-sm text-gray-600 font-medium">{{ __('saving_goal_reach_date') }}</span>
                <span class="text-sm text-gray-600 font-medium absolute right-0">:</span>
            </div>
            <span class="text-sm text-gray-600 font-medium mt-1 lg:mt-0 lg:ml-4">{{ $piggyBank->scheduledSavings()->orderByDesc('saving_number')->first()->saving_date->translatedFormat('d F Y') }}</span>
        </div>
    </div>
    <h3 class="text-lg font-bold mt-4">{{ __(strtolower($piggyBank->status)) }}</h3>
</div>
</a>
