# How to Send Test Emails to Log in Development

This guide explains how to test email functionality in your Laravel development environment without actually sending emails or consuming your email service quota.

## Overview

When developing email features, you often want to see how emails look and work without actually sending them. Laravel's log mail driver allows you to "send" emails to your log files instead of through your configured email service (like Postmark).

## Step 1: Change Mail Driver to Log

Edit your `.env` file and change the mail driver from your production service to log.

**File:** `.env`

**Change this:**
```
MAIL_MAILER=postmark
```

**To this:**
```
MAIL_MAILER=log
```

Keep your other mail settings unchanged:
```
MAIL_FROM_ADDRESS=contact@akluma.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Step 2: Clear Application Cache (Optional but Recommended)

After changing the `.env` file, clear the config cache to ensure the changes take effect:

```bash
sail php artisan config:clear
```

## Step 3: Trigger the Email Command

Use the saving reminders command to manually trigger emails. The `--force` flag bypasses time restrictions.

```bash
sail php artisan app:send-saving-reminders --force
```

**Optional parameters:**
- `--date=YYYY-MM-DD` - Test with a specific date instead of today

Example with specific date:
```bash
sail php artisan app:send-saving-reminders --force --date=2024-01-15
```

You should see output like:
```
Starting to process saving reminders...
Processing reminders for date: 2025-07-16
Found 3 scheduled savings for today.
Processing 3 savings for timezone: Africa/Abidjan
Successfully dispatched email job for saving #142
Successfully dispatched email job for saving #198
Successfully dispatched email job for saving #217
Reminder processing completed.
```

## Step 4: Process the Queue

The emails are dispatched to jobs but need to be processed by the queue worker. In a **separate terminal**, run:

```bash
sail php artisan queue:work
```

This will start processing the queued email jobs. Keep this running until all jobs are processed.

You should see output like:
```
[2025-07-16 10:30:45][abc123] Processing: App\Jobs\SendSavingReminderJob
[2025-07-16 10:30:45][abc123] Processed:  App\Jobs\SendSavingReminderJob
```

## Step 5: Check the Log File

View the email content in the Laravel log file:

```bash
tail -f storage/logs/laravel.log
```

Or open the file directly:
**File:** `storage/logs/laravel.log`

You'll see the complete email content including headers, subject, and body.

## Step 6: Clean Up After Testing

### Stop the Queue Worker
Press `Ctrl+C` in the terminal running the queue worker.

### Restore Production Mail Settings
Change your `.env` back to production settings:

**Change this:**
```
MAIL_MAILER=log
```

**Back to this:**
```
MAIL_MAILER=postmark
```

### Clear Config Cache Again
```bash
sail php artisan config:clear
```

### Optional: Clear Log File
To clear the log file for future testing:

```bash
sail php artisan logs:clear
```

## Troubleshooting

### No Emails in Log
- Make sure you ran the queue worker (`sail php artisan queue:work`)
- Check that `MAIL_MAILER=log` is set in `.env`
- Verify the command found scheduled savings to process

### Queue Jobs Not Processing
- Ensure the queue worker is running in a separate terminal
- Check for any error messages in the queue worker output
- Verify your database connection is working

### Command Shows "Found 0 scheduled savings"
- Check your database has `ScheduledSaving` records for today's date
- Use the `--date` parameter to test with a specific date that has data
- Ensure the savings have `status = 'pending'` and belong to active piggy banks

## Related Files

- **Email Template:** `resources/views/emails/saving-reminder.blade.php`
- **Email Class:** `app/Mail/SavingReminderMail.php`
- **Command:** `app/Console/Commands/SendSavingReminders.php`
- **Job:** `app/Jobs/SendSavingReminderJob.php`
- **Console Routes:** `routes/console.php`

## Quick Reference Commands

```bash
# Change mail driver to log (in .env file)
MAIL_MAILER=log

# Clear config cache
sail php artisan config:clear

# Trigger emails
sail php artisan app:send-saving-reminders --force

# Process queue (in separate terminal)
sail php artisan queue:work

# View logs
tail -f storage/logs/laravel.log

# Clear logs
sail php artisan logs:clear

# Restore mail driver (in .env file)
MAIL_MAILER=postmark
```
