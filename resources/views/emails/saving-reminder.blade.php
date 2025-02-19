<x-mail::message>
    # {{ __('emails.saving_reminder_title') }}

    {{ __('emails.saving_reminder_greeting', ['name' => $user->name]) }}

    {{ __('emails.saving_reminder_message', [
        'saving_number' => $scheduledSaving->saving_number,
        'amount' => $scheduledSaving->amount,
        'currency' => $piggyBank->currency,
        'piggy_bank_name' => $piggyBank->name,
        'date' => $scheduledSaving->saving_date,
    ]) }}

    <x-mail::button :url="route('piggy-banks.show', $piggyBank->id)">
        {{ __('emails.view_piggy_bank') }}
    </x-mail::button>

    {{ __('emails.saving_reminder_closing') }}<br>
    {{ config('app.name') }}

    <x-slot:subcopy>
        {{ __('emails.saving_reminder_unsubscribe_notice') }}
    </x-slot:subcopy>
</x-mail::message>
