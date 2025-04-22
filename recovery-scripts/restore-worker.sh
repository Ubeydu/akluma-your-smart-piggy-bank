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
