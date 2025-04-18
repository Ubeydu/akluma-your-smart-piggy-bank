#!/usr/bin/env bash

# Only run on cron process
if [[ "${FLY_PROCESS_GROUP}" == "cron" ]]; then
    echo "↪️ Setting up Laravel cron job inside cron process..."

    # Create helper to load env and run artisan command
    cat > /var/www/html/load-env.sh << 'EOF'
#!/bin/bash
set -e
export $(printenv | grep -v "^HOME=" | grep -v "^PWD=" | grep -v "^TERM=" | grep -v "^SHLVL=" | grep -v "^PATH=" | grep "^[A-Z]" | xargs -0)
exec "$@"
EOF

    chmod +x /var/www/html/load-env.sh

    # Register cron job
    echo "* * * * * cd /var/www/html && /var/www/html/load-env.sh php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1" | crontab -

    echo "✅ Cron job registered successfully."
fi
