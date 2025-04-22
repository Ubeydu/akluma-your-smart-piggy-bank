# Complete Plan for Preserving and Testing Your Fly.io Setup

## PART 1: Documenting Current State

1. **Create a documentation file in your local repository**:

   *Run this in your local project root:*
   ```bash
   touch FLY_MACHINE_SETUP.md
   ```

2. **Document your current configuration**:

   *Run these commands in your local project root:*
   ```bash
   # Create the documentation file with a header
   echo "# Fly.io Machine Configuration - Last working setup on $(date)" > FLY_MACHINE_SETUP.md

   # Add active machines section
   echo "## Active Machines" >> FLY_MACHINE_SETUP.md
   fly m list --app akluma-prod >> FLY_MACHINE_SETUP.md

   # Add app configuration section
   echo "## App Configuration" >> FLY_MACHINE_SETUP.md
   fly config show --app akluma-prod >> FLY_MACHINE_SETUP.md

   # Add environment variables section (this will only show variable names, not values)
   echo "## Environment Variables" >> FLY_MACHINE_SETUP.md
   fly secrets list --app akluma-prod >> FLY_MACHINE_SETUP.md
   ```

3. **Document cron machine configuration**:

   *Run this in your local project root:*
   ```bash
   echo "## Cron Machine Setup" >> FLY_MACHINE_SETUP.md
   echo "To check crontab: \`crontab -l\`" >> FLY_MACHINE_SETUP.md
   echo "To check cron script: \`cat /var/www/html/.fly/scripts/setup-cron.sh\`" >> FLY_MACHINE_SETUP.md
   ```

   *Now connect to your cron machine:*
   ```bash
   fly ssh console --app akluma-prod --select
   # Select your working cron machine (summer-mountain-6143)
   ```

   *In the cron machine, run:*
   ```bash
   crontab -l
   cat /var/www/html/.fly/scripts/setup-cron.sh
   exit
   ```

   *Copy the output of each command manually and add it to your FLY_MACHINE_SETUP.md file*

4. **Document worker machine configuration**:

   *Run this in your local project root:*
   ```bash
   echo "## Worker Machine Setup" >> FLY_MACHINE_SETUP.md
   echo "To check worker process: \`ps aux | grep artisan\`" >> FLY_MACHINE_SETUP.md
   echo "To check worker script: \`cat /var/www/html/.fly/scripts/start-worker.sh\`" >> FLY_MACHINE_SETUP.md
   ```

   *Now connect to your worker machine:*
   ```bash
   fly ssh console --app akluma-prod --select
   # Select your working worker machine (bitter-resonance-2583)
   ```

   *In the worker machine, run:*
   ```bash
   ps aux | grep artisan
   cat /var/www/html/.fly/scripts/start-worker.sh
   exit
   ```

   *Copy the output of each command manually and add it to your FLY_MACHINE_SETUP.md file*

5. **Save the working codebase state**:

   *Run this in your local project root:*
   ```bash
   # Tag the current state in Git
   git tag production-stable-$(date +%Y%m%d)
   git push --tags
   ```

## PART 2: Creating Recovery Scripts

1. **Create a script to restore cron configuration**:

   *Run this in your local project root:*
   ```bash
   mkdir -p recovery-scripts
   touch recovery-scripts/restore-cron.sh
   ```

   *Open the file in your code editor and add:*
   ```bash
   #!/bin/bash
   # Run this script from your local machine
   # It will guide you through restoring the cron configuration

   echo "Connecting to your Fly.io app..."
   echo "When prompted, select the cron machine that needs fixing."
   echo "After connecting, the required commands will be shown."
   echo "Copy and paste each command into the SSH session."
   echo ""
   echo "Commands to run after connecting:"
   echo "-------------------------------"
   echo "echo -e \"MAILTO=\\\"\\\"\\n* * * * * cd /var/www/html && /var/www/html/load-env.sh php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1\" > /etc/cron.d/laravel"
   echo "chmod 644 /etc/cron.d/laravel"
   echo "service cron start"
   echo "crontab -l"
   echo "exit"
   echo "-------------------------------"
   echo ""
   echo "Press Enter to connect to your Fly.io app..."
   read

   fly ssh console --app akluma-prod --select
   ```

   *Make the script executable:*
   ```bash
   chmod +x recovery-scripts/restore-cron.sh
   ```

2. **Create a script to restore worker configuration**:

   *Run this in your local project root:*
   ```bash
   touch recovery-scripts/restore-worker.sh
   ```

   *Open the file in your code editor and add:*
   ```bash
   #!/bin/bash
   # Run this script from your local machine
   # It will guide you through restoring the worker configuration

   echo "Connecting to your Fly.io app..."
   echo "When prompted, select the worker machine that needs fixing."
   echo "After connecting, the required commands will be shown."
   echo "Copy and paste each command into the SSH session."
   echo ""
   echo "Commands to run after connecting:"
   echo "-------------------------------"
   echo "cd /var/www/html"
   echo "nohup php artisan queue:work --tries=3 &"
   echo "ps aux | grep artisan"
   echo "exit"
   echo "-------------------------------"
   echo ""
   echo "Press Enter to connect to your Fly.io app..."
   read

   fly ssh console --app akluma-prod --select
   ```

   *Make the script executable:*
   ```bash
   chmod +x recovery-scripts/restore-worker.sh
   ```

3. **Add these scripts to Git**:

   *Run this in your local project root:*
   ```bash
   git add FLY_MACHINE_SETUP.md recovery-scripts/
   git commit -m "Add documentation and recovery scripts for Fly.io setup"
   git push
   ```

## PART 3: Safe Experimentation and Testing in Production

1. **Create a snapshot before experimenting**:

   *Run this in your local project root:*
   ```bash
   # Take a snapshot of your current working state with a descriptive tag
   git tag prod-working-$(date +%Y%m%d)
   git push --tags
   ```

2. **Small, incremental tests in production**:

   When experimenting with production, you should:
   - Make one small change at a time
   - Verify it works correctly before making another change
   - Document each change and its effect
   - Have your recovery scripts ready

3. **Safely test environment variables**:

   *To set test variables in production:*
   ```bash
   # Set a temporary test variable
   fly secrets set TEST_TEMP_VAR=testing123 --app akluma-prod

   # Connect to your app machine (not worker or cron)
   fly ssh console --app akluma-prod --select
   # Select your app machine
   ```

   *In the app machine SSH session:*
   ```bash
   # Create a temporary test script
   echo "<?php echo 'TEST_TEMP_VAR: ' . (isset(\$_ENV['TEST_TEMP_VAR']) ? \$_ENV['TEST_TEMP_VAR'] : 'not set') . \"\n\";" > /var/www/html/test-temp-env.php

   # Run the test script
   php /var/www/html/test-temp-env.php

   # Clean up when done
   rm /var/www/html/test-temp-env.php
   exit
   ```

   *Remove the test variable when done:*
   ```bash
   fly secrets unset TEST_TEMP_VAR --app akluma-prod
   ```

4. **Test changes to cron job frequency**:

   *Connect to your cron machine:*
   ```bash
   fly ssh console --app akluma-prod --select
   # Select your cron machine
   ```

   *In the cron machine, modify the cron frequency temporarily:*
   ```bash
   # Back up the current crontab
   crontab -l > /tmp/current-crontab

   # Edit the crontab (for example, to run every 2 minutes instead of every minute)
   echo "*/2 * * * * cd /var/www/html && /var/www/html/load-env.sh php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1" | crontab -

   # Verify the change
   crontab -l

   # Monitor the scheduler log to see if it runs at the expected time
   tail -f /var/www/html/storage/logs/scheduler.log

   # Restore the original crontab when done
   cat /tmp/current-crontab | crontab -
   rm /tmp/current-crontab

   # Verify restoration
   crontab -l

   exit
   ```

5. **Test worker queue changes**:

   *Connect to your worker machine:*
   ```bash
   fly ssh console --app akluma-prod --select
   # Select your worker machine
   ```

   *In the worker machine, try modifying worker parameters:*
   ```bash
   # Find the current worker process
   ps aux | grep artisan

   # Stop the current worker (replace PID with the actual process ID from above)
   kill PID

   # Start a new worker with different parameters for testing
   cd /var/www/html
   php artisan queue:work --tries=3 --sleep=5 &

   # Verify the new worker is running
   ps aux | grep artisan

   # After testing, restore original worker
   kill PID_OF_NEW_WORKER
   php artisan queue:work --tries=3 &

   # Verify restoration
   ps aux | grep artisan

   exit
   ```

6. **Carefully test Dockerfile changes**:

   *When modifying your Dockerfile, follow these steps:*

   *In your local project root:*
   ```bash
   # Before changing the Dockerfile, make a backup
   cp Dockerfile Dockerfile.bak

   # Make your changes to the Dockerfile
   # IMPORTANT: Only change one thing at a time

   # Commit the change with a descriptive message
   git add Dockerfile
   git commit -m "Test: Specific change to Dockerfile (KEEP THIS COMMIT ID FOR REFERENCE)"

   # Deploy the changes
   fly deploy --app akluma-prod
   ```

   *After deployment, verify everything is working:*
   ```bash
   # Check machines are running
   fly m list --app akluma-prod

   # Connect to cron machine and verify setup
   fly ssh console --app akluma-prod --select
   # Select cron machine
   # Run: crontab -l && service cron status

   # Connect to worker machine and verify setup
   fly ssh console --app akluma-prod --select
   # Select worker machine
   # Run: ps aux | grep artisan
   ```

   *If something breaks, revert immediately:*
   ```bash
   # Restore from backup
   git checkout HEAD~1 Dockerfile
   git commit -m "Revert: Restore working Dockerfile"
   fly deploy --app akluma-prod
   ```

7. **Create a recovery checklist for when things go wrong**:

   *Run this in your local project root:*
   ```bash
   touch RECOVERY_CHECKLIST.md
   ```

   *Open the file in your code editor and add:*
   ```markdown
   # Recovery Checklist

   ## If cron jobs stop working:
   1. Connect to cron machine: `fly ssh console --app akluma-prod --select`
   2. Check cron service: `service cron status`
   3. If not running: `service cron start`
   4. Check crontab: `crontab -l`
   5. If missing or incorrect, run:
      ```
      echo "* * * * * cd /var/www/html && /var/www/html/load-env.sh php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1" | crontab -
      ```
   6. Verify logs: `tail -f /var/www/html/storage/logs/scheduler.log`

   ## If queue worker stops:
   1. Connect to worker machine: `fly ssh console --app akluma-prod --select`
   2. Check if running: `ps aux | grep artisan`
   3. If not running, start it:
      ```
      cd /var/www/html
      php artisan queue:work --tries=3 &
      ```
   4. Verify it's running: `ps aux | grep artisan`

   ## If deployment breaks things:
   1. Roll back to last working commit:
      ```
      git checkout [LAST_WORKING_COMMIT_TAG]
      fly deploy --app akluma-prod
      ```
   2. Apply recovery steps for cron and worker as needed

   ## Always check logs for errors:
   ```
   fly logs --app akluma-prod
   ```
   ```

8. **Add recovery checklist to Git**:

   *Run this in your local project root:*
   ```bash
   git add RECOVERY_CHECKLIST.md
   git commit -m "Add recovery checklist for production issues"
   git push
   ```

This revised approach allows you to experiment directly in your production environment while minimizing risk through:
1. Making small, incremental changes
2. Testing one component at a time
3. Having immediate recovery steps if something breaks
4. Documenting each change and its effect

You'll gain confidence by working with your actual production environment while having clear path to recovery when needed.
