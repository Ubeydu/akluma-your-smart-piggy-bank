<x-mail::message>
    # {{ __('saving_reminder_title') }}

    {{ __('saving_reminder_greeting', ['name' => $user->name]) }}

    {{ __('saving_reminder_message', [
        'saving_number' => $scheduledSaving->saving_number,
        'amount' => $scheduledSaving->amount,
        'currency' => $piggyBank->currency,
        'piggy_bank_name' => $piggyBank->name,
        'date' => $scheduledSaving->saving_date,
    ]) }}

    <x-mail::button :url="$piggyBankUrl">
        {{ __('view_piggy_bank') }}
    </x-mail::button>

    {{ __('saving_reminder_closing') }}<br>
    {{ config('app.name') }}

    <x-slot:subcopy>
        {{ __('saving_reminder_unsubscribe_notice') }}
    </x-slot:subcopy>
</x-mail::message>
