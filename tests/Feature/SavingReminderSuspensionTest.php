<?php

use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

function createUserWithPendingSaving(array $userAttributes = []): ScheduledSaving
{
    $user = User::factory()->create(array_merge([
        'notification_preferences' => json_encode([
            'email' => ['enabled' => true],
            'sms' => ['enabled' => false],
            'push' => ['enabled' => false],
        ]),
    ], $userAttributes));

    $piggyBank = PiggyBank::factory()->active()->create(['user_id' => $user->id]);

    return ScheduledSaving::create([
        'piggy_bank_id' => $piggyBank->id,
        'saving_number' => 1,
        'amount' => 100,
        'status' => 'pending',
        'saving_date' => now()->toDateString(),
        'archived' => false,
    ]);
}

test('saving reminder is not dispatched for suspended users', function () {
    Queue::fake();

    createUserWithPendingSaving(['suspended_at' => now()]);

    $this->artisan('app:send-saving-reminders --force');

    Queue::assertNothingPushed();
});

test('saving reminder is dispatched for active (non-suspended) users', function () {
    Queue::fake();

    createUserWithPendingSaving();

    $this->artisan('app:send-saving-reminders --force');

    Queue::assertPushed(\App\Jobs\SendSavingReminderJob::class);
});

test('saving reminder job skips sending email when user is suspended', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $saving = createUserWithPendingSaving(['suspended_at' => now()]);

    (new \App\Jobs\SendSavingReminderJob($saving))->handle();

    \Illuminate\Support\Facades\Mail::assertNothingSent();
});
