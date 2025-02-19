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
    protected $signature = 'app:send-saving-reminders {--force : Send reminders regardless of time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for scheduled savings due today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process saving reminders...');

        // Get today's date in UTC
        $today = Carbon::now()->toDateString();

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
    protected function processTimezoneGroup(string $timezone, $savings)
    {
        $this->info("Processing {$savings->count()} savings for timezone: {$timezone}");

        try {
            // Get current time in user's timezone
            $now = Carbon::now()->timezone($timezone);

            // Only send at 9AM unless --force is used
            if (!$this->option('force') && $now->hour != 9) {
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

            // Only send at 9AM UTC unless --force is used
            if (!$this->option('force') && $now->hour != 9) {
                $this->info("Skipping: current UTC hour is {$now->hour}, not 9AM");
                return;
            }

            foreach ($savings as $saving) {
                $this->processSaving($saving);
            }
        }
    }

    /**
     * Process an individual scheduled saving
     */
    protected function processSaving(ScheduledSaving $saving)
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
        if ($notificationStatuses['email']['sent']) {
            $this->info("Skipping saving #{$saving->id}: email already sent");
            return;
        }

        // Check notification preferences
        $preferences = $this->getNotificationPreferences($piggyBank);
        if (!$preferences['email']['enabled']) {
            $this->info("Skipping saving #{$saving->id}: email notifications disabled");
            return;
        }

        try {
            // Queue the email
            Mail::to($user)->queue(new SavingReminderMail(
                $user,
                $piggyBank,
                $saving
            ));

            // Update notification status
            $notificationStatuses = json_decode($saving->notification_statuses, true);
            $notificationStatuses['email']['sent'] = true;
            $notificationStatuses['email']['sent_at'] = Carbon::now()->toDateTimeString();

            $notificationAttempts = json_decode($saving->notification_attempts, true);
            $notificationAttempts['email'] += 1;

            $saving->notification_statuses = json_encode($notificationStatuses);
            $saving->notification_attempts = json_encode($notificationAttempts);
            $saving->save();

            $this->info("Successfully queued email for saving #{$saving->id}");

            // For future SMS implementation
            // FUTURE: If user has SMS enabled and is on paid plan, send SMS here

        } catch (\Exception $e) {
            $this->error("Failed to send email for saving #{$saving->id}: {$e->getMessage()}");
            Log::error("Failed to send saving reminder", [
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
    protected function getNotificationPreferences(PiggyBank $piggyBank)
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
