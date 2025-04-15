<?php

namespace App\Console\Commands;

use App\Mail\SavingReminderMail;
use App\Models\ScheduledSaving;
use App\Models\PiggyBank;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSavingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
//    protected $signature = 'app:send-saving-reminders {--force : Send reminders regardless of time}';

    protected $signature = 'app:send-saving-reminders {--force : Send reminders regardless of time} {--date= : Override date for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for scheduled savings due today';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting to process saving reminders...');

//        // Get today's date in UTC
//        $today = Carbon::now()->toDateString();

        // Get target date (today or override for testing)
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();
        $today = $date->toDateString();
        $this->info("Processing reminders for date: {$today}");

        // Find pending scheduled savings for today
        $scheduledSavings = ScheduledSaving::whereDate('saving_date', $today)
            ->where('status', 'pending')
            ->whereHas('piggyBank', function ($query) {
                $query->where('status', 'active');
            })
            ->with(['piggyBank', 'piggyBank.user'])
            ->get();

        $this->info("Found {$scheduledSavings->count()} scheduled savings for today.");

        if ($scheduledSavings->isEmpty()) {
            return;
        }

        // Group by user timezone
        $savingsByTimezone = $scheduledSavings->groupBy(function ($saving) {
            // Default to UTC if timezone is not set
            return $saving->piggyBank->user->timezone ?? 'UTC';
        });

        foreach ($savingsByTimezone as $timezone => $timezoneGroup) {
            $this->processTimezoneGroup($timezone, $timezoneGroup);
        }

        $this->info('Reminder processing completed.');
    }

    /**
     * Process a group of savings for users in the same timezone
     */
    protected function processTimezoneGroup(string $timezone, $savings): void
    {
        $this->info("Processing {$savings->count()} savings for timezone: {$timezone}");

        try {
            // Get current time in user's timezone
            $now = Carbon::now()->timezone($timezone);

            $isTestMode = config('app.reminders_test_mode');

            if (!$this->option('force') && !$isTestMode && $now->hour != 9) {
                $this->info("Skipping timezone {$timezone}: current hour is {$now->hour}, not 9AM");
                return;
            }


            foreach ($savings as $saving) {
                $this->processSaving($saving);
            }

        } catch (\Exception $e) {
            // If timezone is invalid, default to UTC
            $this->error("Invalid timezone '{$timezone}'. Defaulting to UTC");
            Log::error("Invalid timezone: {$timezone}", [
                'exception' => $e->getMessage(),
                'savings_count' => $savings->count()
            ]);

            // Get current time in UTC
            $now = Carbon::now();


            // In the processTimezoneGroup method, replace the current dev/prod code blocks with this:
            // Check current hour based on environment
            if (app()->environment('production')) {
                // Production behavior - only send at 9AM in user's timezone
                if (!$this->option('force') && $now->hour != 9) {
                    $this->info("Skipping timezone {$timezone}: current hour is {$now->hour}, not 9AM");
                    return;
                }
            } else {
                // Development/Staging behavior - more flexible for testing
                if (!$this->option('force') && $now->hour != 9) {
                    $this->info("Skipping: current UTC hour is {$now->hour}, not 9AM");
                    return;
                }
            }

            foreach ($savings as $saving) {
                $this->processSaving($saving);
            }
        }
    }

    /**
     * Process an individual scheduled saving
     */
    protected function processSaving(ScheduledSaving $saving): void
    {
        $piggyBank = $saving->piggyBank;
        $user = $piggyBank->user;

        // Skip if user has no email
        if (empty($user->email)) {
            $this->info("Skipping saving #{$saving->id}: user has no email address");
            return;
        }

        // Skip if already sent
        $notificationStatuses = json_decode($saving->notification_statuses, true);
        if (!is_array($notificationStatuses)) {
            // Initialize with default structure if null or invalid
            $notificationStatuses = [
                'email' => ['sent' => false, 'sent_at' => null],
                'sms' => ['sent' => false, 'sent_at' => null],
                'push' => ['sent' => false, 'sent_at' => null]
            ];
            $saving->notification_statuses = json_encode($notificationStatuses);
            $saving->save();
            $this->info("Fixed missing notification_statuses for saving #{$saving->id}");
        } else if ($notificationStatuses['email']['sent']) {
            $this->info("Skipping saving #{$saving->id}: email already sent");
            return;
        }

        // Check notification preferences
        $preferences = $this->getNotificationPreferences($piggyBank);
        if (!isset($preferences['email']) || !$preferences['email']['enabled']) {
            $this->info("Skipping saving #{$saving->id}: email notifications disabled");
            return;
        }

        try {
            // Dispatch the job instead of directly queueing the email
            \App\Jobs\SendSavingReminderJob::dispatch($saving);

            $this->info("Successfully dispatched email job for saving #{$saving->id}");

            // For future SMS implementation
            // FUTURE: If user has SMS enabled and is on paid plan, send SMS here

        } catch (\Exception $e) {
            $this->error("Failed to dispatch email job for saving #{$saving->id}: {$e->getMessage()}");
            Log::error("Failed to dispatch saving reminder job", [
                'saving_id' => $saving->id,
                'piggy_bank_id' => $piggyBank->id,
                'user_id' => $user->id,
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get notification preferences for a piggy bank
     */
    public function getNotificationPreferences(PiggyBank $piggyBank): array
    {
        $user = $piggyBank->user; // Get the user who owns the piggy bank

        if (!$user) {
            return []; // If no user is found, return an empty array
        }

        $preferences = $user->notification_preferences; // Fetch the column

        // Ensure it's an array
        if (is_string($preferences)) {
            $preferences = json_decode($preferences, true);
        }

        // Return an empty array if it's still not valid
        return is_array($preferences) ? $preferences : [];

    }

}
