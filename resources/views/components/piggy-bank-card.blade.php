@props(['piggyBank', 'newPiggyBankId' => null])

<div class="p-4 border rounded-lg shadow bg-rose-50 hover:bg-rose-100 transition-colors duration-300
    {{ $newPiggyBankId == $piggyBank->id ? 'highlight-new' : '' }}">
    <h3 class="text-lg font-bold">{{ $piggyBank->name }}</h3>
    <p class="text-sm text-gray-600 font-medium">
        {{ __('Final Total') }}: {{$piggyBank->final_total, $piggyBank->currency }}
    </p>
    <p class="text-sm text-gray-600 font-medium">
        {{ __('Current Balance') }}: {{ \App\Helpers\MoneyFormatHelper::format($piggyBank->current_balance ?? 0, $piggyBank->currency) }}
    </p>
    <p class="text-sm text-gray-600 font-medium">{{ __('remaining_amount') }}: {{ $piggyBank->remaining_amount }}</p>
    <p class="text-sm text-gray-600 font-medium">{{ __('saving_goal_reach_date') }}: {{ $piggyBank->scheduledSavings()->orderByDesc('saving_number')->first()->saving_date->translatedFormat('d F Y') }}</p>
    <h3 class="text-lg font-bold">{{ $piggyBank->status }}</h3>
</div>
