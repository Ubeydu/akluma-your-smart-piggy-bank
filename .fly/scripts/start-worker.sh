#!/usr/bin/env bash

# Only run on worker process
if [[ "${FLY_PROCESS_GROUP}" == "worker" ]]; then
    echo "Starting queue worker..."
    cd /var/www/html || exit
    exec php artisan queue:work --tries=3
fi
