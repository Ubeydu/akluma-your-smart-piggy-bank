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
