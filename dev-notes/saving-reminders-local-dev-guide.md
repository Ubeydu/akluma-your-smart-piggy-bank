# Testing Saving Reminders in Dev Environment

This guide shows how to manually trigger saving reminders in the development environment.

## Commands to Run

1. Make sure your Docker containers are running:
   ```bash
   sail up -d
   ```

2. Trigger the saving reminders command with the force flag:
   ```bash
   sail artisan app:send-saving-reminders --force
   ```

Or, if you don't want to force, you just start the scheduler and wait the time to come;
   ```bash
   sail artisan schedule:work
   ```

3. Start the queue worker to process the jobs:
   ```bash
   sail artisan queue:work
   ```

4. You should see output similar to:
   ```
   INFO  Processing jobs from the [default] queue.
   
   2025-04-23 13:44:14 App\Jobs\SendSavingReminderJob ..... RUNNING
   2025-04-23 13:44:14 App\Jobs\SendSavingReminderJob ... 92.86ms DONE
   2025-04-23 13:44:14 App\Mail\SavingReminderMail ....... RUNNING
   2025-04-23 13:44:15 App\Mail\SavingReminderMail ....... 1s DONE
   ```

5. Email will be sent to your inbox once the job is processed.

## Common Issues

If you don't receive emails, check:
- The queue worker is running (`sail artisan queue:work`)
- Your email configuration in `.env` is correct
- Look at the logs for any errors
