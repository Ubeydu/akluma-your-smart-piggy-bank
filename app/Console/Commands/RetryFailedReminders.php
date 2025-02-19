<?php

namespace App\Console\Commands;

use App\Mail\SavingReminderMail;
use App\Models\ScheduledSaving;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetryFailedReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
//    protected $signature = 'app:retry-failed-reminders {--days=1 : Number of days to look back}';

    protected $signature = 'app:retry-failed-reminders {--days=1 : Number of days to look back} {--date= : Override current date for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry sending failed saving reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to retry failed saving reminders...');

//        $lookBackDays = (int) $this->option('days');
//        $startDate = Carbon::now()->subDays($lookBackDays)->startOfDay();
//        $endDate = Carbon::now()->endOfDay();

        $baseDate = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();
        $lookBackDays = (int) $this->option('days');
        $startDate = $baseDate->copy()->subDays($lookBackDays)->startOfDay();
        $endDate = $baseDate->copy()->endOfDay();
        $this->info("Using base date: {$baseDate->toDateString()}");

        $this->info("Looking for failed reminders from: {$startDate->toDateString()} to {$endDate->toDateString()}");

        // Find scheduled savings from the past few days where:
        // 1. The saving is still pending
        // 2. The piggy bank is active
        // 3. The notification was attempted but not successfully sent
        $scheduledSavings = ScheduledSaving::whereBetween('saving_date', [$startDate, $endDate])
            ->where('status', 'pending')
            ->whereHas('piggyBank', function ($query) {
                $query->where('status', 'active');
            })
            ->with(['piggyBank', 'piggyBank.user'])
            ->get();

        $this->info("Found {$scheduledSavings->count()} scheduled savings to check for retry.");

        $retryCount = 0;

        foreach ($scheduledSavings as $saving) {
            $notificationStatuses = json_decode($saving->notification_statuses, true);
            $notificationAttempts = json_decode($saving->notification_attempts, true);

            // Only retry if:
            // 1. Email was attempted (attempts > 0)
            // 2. Email was not successfully sent (sent = false)
            // 3. Max retry limit not reached (attempts < 3)
            if ($notificationAttempts['email'] > 0 &&
                !$notificationStatuses['email']['sent'] &&
                $notificationAttempts['email'] < 3) {

                $this->retryEmailReminder($saving);
                $retryCount++;
            }

            // For future SMS implementation
            // FUTURE: Add similar logic for SMS retries here
            // FUTURE: Check if user has paid subscription before retrying SMS
        }

        $this->info("Completed retry process. Attempted to retry {$retryCount} reminders.");
    }

    /**
     * Retry sending email for a specific scheduled saving
     */
    protected function retryEmailReminder(ScheduledSaving $saving)
    {
        $piggyBank = $saving->piggyBank;
        $user = $piggyBank->user;

        // Skip if user has no email
        if (empty($user->email)) {
            $this->info("Skipping retry for saving #{$saving->id}: user has no email address");
            return;
        }

        // Check notification preferences
        $preferences = $this->getNotificationPreferences($piggyBank);
        if (!$preferences['email']['enabled']) {
            $this->info("Skipping retry for saving #{$saving->id}: email notifications disabled");
            return;
        }

        try {
            $this->info("Retrying email for saving #{$saving->id}");

            // Queue the email
            Mail::to($user)->queue(new SavingReminderMail(
                $user,
                $piggyBank,
                $saving
            ));

            // Update notification status on success
            $notificationStatuses = json_decode($saving->notification_statuses, true);
            $notificationStatuses['email']['sent'] = true;
            $notificationStatuses['email']['sent_at'] = Carbon::now()->toDateTimeString();

            $notificationAttempts = json_decode($saving->notification_attempts, true);
            $notificationAttempts['email'] += 1;

            $saving->notification_statuses = json_encode($notificationStatuses);
            $saving->notification_attempts = json_encode($notificationAttempts);
            $saving->save();

            $this->info("Successfully queued retry email for saving #{$saving->id}");

        } catch (\Exception $e) {
            $this->error("Failed to retry email for saving #{$saving->id}: {$e->getMessage()}");
            Log::error("Failed to retry saving reminder", [
                'saving_id' => $saving->id,
                'piggy_bank_id' => $piggyBank->id,
                'user_id' => $user->id,
                'exception' => $e->getMessage()
            ]);

            // Update attempt count
            $notificationAttempts = json_decode($saving->notification_attempts, true);
            $notificationAttempts['email'] += 1;
            $saving->notification_attempts = json_encode($notificationAttempts);
            $saving->save();
        }
    }

    /**
     * Get notification preferences for a piggy bank
     */
    protected function getNotificationPreferences($piggyBank)
    {
        // Get notification preferences from database
        $preferences = DB::table('notification_preferences')
            ->where('piggy_bank_id', $piggyBank->id)
            ->first();

        if ($preferences && $preferences->channel_preferences) {
            return json_decode($preferences->channel_preferences, true);
        }

        // Default preferences if none found
        return [
            'email' => ['enabled' => true],
            'sms' => ['enabled' => true],
            'push' => ['enabled' => true]
        ];
    }
}
