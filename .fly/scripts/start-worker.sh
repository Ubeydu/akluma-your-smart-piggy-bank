#!/usr/bin/env bash
set -euo pipefail

# Only run on worker process
if [[ "${FLY_PROCESS_GROUP}" == "worker" ]]; then
    echo "Starting queue worker with auto-restart..."
    cd /var/www/html || exit 1
    command -v php >/dev/null 2>&1 || { echo "php not found"; exit 1; }
    [[ -f artisan ]] || { echo "artisan not found in $(pwd)"; exit 1; }

    # Signal handling for clean shutdowns (deploys, stops)
    CHILD_PID=""
    USE_PG_KILL=0
    terminate() {
        echo "[$(date)] Caught termination signal. Stopping worker..."
        if [[ -n "$CHILD_PID" ]]; then
            # Kill the whole process group if setsid was used, otherwise just the child
            if (( USE_PG_KILL == 1 )); then
                kill -TERM -- "-$CHILD_PID" 2>/dev/null || true
            else
                kill -TERM "$CHILD_PID" 2>/dev/null || true
            fi
            wait "$CHILD_PID" 2>/dev/null
        fi
        exit 0
    }
    trap terminate TERM INT

    BACKOFF=5

    while true; do
        START_TS=$(date +%s)
        echo "[$(date)] Starting queue:work..."

        # Start worker in a new process group for clean shutdown (fallback if setsid missing)
        if command -v setsid >/dev/null 2>&1; then
            USE_PG_KILL=1
            setsid php artisan queue:work --tries=3 --sleep=3 --max-jobs=1000 --max-time=3600 &
        else
            USE_PG_KILL=0
            php artisan queue:work --tries=3 --sleep=3 --max-jobs=1000 --max-time=3600 &
        fi
        CHILD_PID=$!

        # Temporarily disable set -e so we can capture the exit code
        set +e
        wait "$CHILD_PID"
        EXIT_CODE=$?
        set -e
        CHILD_PID=""

        END_TS=$(date +%s)
        RUNTIME=$((END_TS - START_TS))

        # Reset backoff on clean exit (code 0) or if it ran long enough
        if (( EXIT_CODE == 0 )) || (( RUNTIME >= 30 )); then BACKOFF=5; fi

        echo "[$(date)] Worker exited (code $EXIT_CODE) after ${RUNTIME}s. Restarting in ${BACKOFF}s..."
        sleep "$BACKOFF"
        BACKOFF=$(( BACKOFF < 60 ? BACKOFF * 2 : 60 ))
    done
fi
