<?php

namespace App\Console\Commands;

use App\Jobs\SendSavingReminderJob;
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
            $notificationStatuses = json_decode($saving->notification_statuses, true) ?: [];
            $notificationAttempts = json_decode($saving->notification_attempts, true) ?: [
                'email' => 0,
                'sms' => 0,
                'push' => 0
            ];

            // Only retry if:
            // 1. Email was attempted (attempts > 0)
            // 2. Email was not successfully sent (sent = false or status doesn't exist)
            // 3. Max retry limit not reached (attempts < 3)
            $emailSent = isset($notificationStatuses['email']) && isset($notificationStatuses['email']['sent']) ?
                $notificationStatuses['email']['sent'] : false;
            $emailAttempts = isset($notificationAttempts['email']) ? $notificationAttempts['email'] : 0;

//            if ($emailAttempts > 0 && !$emailSent && $emailAttempts < 3) {
//                $this->info("Dispatching retry job for saving #{$saving->id} (Attempt #{$emailAttempts})");
//
//                // Use the new job to handle the email sending
//                try {
//                    // Dispatch the job instead of directly queueing
//                    SendSavingReminderJob::dispatch($saving);
//
//                    Log::info("🔁 Retrying reminder for saving ID {$saving->id} from RetryFailedReminders");
//
//                    $retryCount++;
//
//                    $this->info("Successfully dispatched retry job for saving #{$saving->id}");
//                } catch (\Exception $e) {
//                    $this->error("Failed to dispatch retry job for saving #{$saving->id}: {$e->getMessage()}");
//                    Log::error("Failed to dispatch retry job", [
//                        'saving_id' => $saving->id,
//                        'exception' => $e->getMessage()
//                    ]);
//                }
//            } else {
//                $this->info("Skipping saving #{$saving->id}: " .
//                    ($emailSent ? "already sent" :
//                        ($emailAttempts >= 3 ? "too many attempts ({$emailAttempts})" :
//                            "no previous attempts")));
//            }

            if ($emailAttempts > 0 && !$emailSent) {
                if ($emailAttempts >= 100) {
                    Log::alert("🚨 Saving #{$saving->id} has {$emailAttempts} failed email attempts and still not sent.");
                    Mail::to('your@email.com')->send(
                        new \App\Mail\AdminRetryAlert($saving, $emailAttempts)
                    );
                }

                $this->info("Dispatching retry job for saving #{$saving->id} (Attempt #{$emailAttempts})");

                try {
                    SendSavingReminderJob::dispatch($saving);

                    Log::info("🔁 Retrying reminder for saving ID {$saving->id} from RetryFailedReminders");

                    $retryCount++;

                    $this->info("Successfully dispatched retry job for saving #{$saving->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to dispatch retry job for saving #{$saving->id}: {$e->getMessage()}");
                    Log::error("Failed to dispatch retry job", [
                        'saving_id' => $saving->id,
                        'exception' => $e->getMessage()
                    ]);
                }
            } else {
                $reason = $emailSent
                    ? "already sent"
                    : ($emailAttempts === 0 ? "no previous attempts" : "unknown condition");

                $this->info("Skipping saving #{$saving->id}: {$reason}");
            }


            // For future SMS implementation
            // FUTURE: Add similar logic for SMS retries here
        }

        $this->info("Completed retry process. Attempted to retry {$retryCount} reminders.");
    }
}
