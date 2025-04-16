<?php

namespace App\Jobs;

use App\Mail\SavingReminderMail;
use App\Models\ScheduledSaving;
use App\Models\PiggyBank;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSavingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The scheduled saving instance.
     *
     * @var \App\Models\ScheduledSaving
     */
    protected $saving;

    /**
     * The user ID (to prevent serialization issues).
     *
     * @var int
     */
    protected $userId;

    /**
     * The piggy bank ID (to prevent serialization issues).
     *
     * @var int
     */
    protected $piggyBankId;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\ScheduledSaving  $saving
     * @return void
     */
    public function __construct(ScheduledSaving $saving)
    {
        $this->saving = $saving;
        $this->userId = $saving->piggyBank->user_id;
        $this->piggyBankId = $saving->piggy_bank_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Re-fetch models to ensure we have fresh data
        $user = User::find($this->userId);
        $piggyBank = PiggyBank::find($this->piggyBankId);
        $saving = $this->saving->fresh();

        if (!$user || !$piggyBank) {
            Log::error("Could not find user or piggy bank for saving reminder", [
                'saving_id' => $saving->id,
                'user_id' => $this->userId,
                'piggy_bank_id' => $this->piggyBankId
            ]);
            return;
        }

        // Save the current app locale
        $previousLocale = App::getLocale();

        Log::info('Reminder Job User Locale Check', [
            'user_id' => $user->id,
            'user_language' => $user->language,
            'current_app_locale' => App::getLocale()
        ]);

        // Set user’s preferred locale
        App::setLocale($user->language ?? config('app.locale'));

        Mail::to($user)->send(new SavingReminderMail(
            $user,
            $piggyBank,
            $saving
        ));

        // Restore the previous locale
        App::setLocale($previousLocale);


        // Update notification status after successful send
        $notificationStatuses = json_decode($saving->notification_statuses, true);
        if (!is_array($notificationStatuses)) {
            $notificationStatuses = [
                'email' => ['sent' => false, 'sent_at' => null],
                'sms' => ['sent' => false, 'sent_at' => null],
                'push' => ['sent' => false, 'sent_at' => null]
            ];
        }

        $notificationStatuses['email']['sent'] = true;
        $notificationStatuses['email']['sent_at'] = Carbon::now()->toDateTimeString();

        // Update attempt count
        $notificationAttempts = json_decode($saving->notification_attempts, true);
        if (!is_array($notificationAttempts)) {
            $notificationAttempts = [
                'email' => 0,
                'sms' => 0,
                'push' => 0
            ];
        }
        $notificationAttempts['email'] += 1;

        $saving->notification_statuses = json_encode($notificationStatuses);
        $saving->notification_attempts = json_encode($notificationAttempts);
        $saving->save();

        Log::info("Successfully sent email reminder for saving", [
            'saving_id' => $saving->id,
            'user_id' => $user->id,
            'piggy_bank_id' => $piggyBank->id
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $saving = $this->saving->fresh();

        // Only update attempt count in case of failure, don't mark as sent
        $notificationAttempts = json_decode($saving->notification_attempts, true);
        if (!is_array($notificationAttempts)) {
            $notificationAttempts = [
                'email' => 0,
                'sms' => 0,
                'push' => 0
            ];
        }
        $notificationAttempts['email'] += 1;

        $saving->notification_attempts = json_encode($notificationAttempts);
        $saving->save();

        Log::error("Failed to send saving reminder email", [
            'saving_id' => $saving->id,
            'user_id' => $this->userId,
            'piggy_bank_id' => $this->piggyBankId,
            'exception' => $exception->getMessage(),
            'attempt' => $notificationAttempts['email']
        ]);
    }
}
