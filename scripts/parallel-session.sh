#!/bin/bash
set -euo pipefail

# =============================================================================
# Parallel Session Manager for Akluma
#
# Creates git worktrees with isolated Sail stacks on unique ports,
# so you can run multiple Claude Code sessions simultaneously.
#
# Usage:
#   ./scripts/parallel-session.sh create <branch-name> [issue-number]
#   ./scripts/parallel-session.sh destroy <branch-name>
#   ./scripts/parallel-session.sh list
#
# Main repo stays on defaults (:80, :3306, :5173).
# Parallel sessions use offset ports (:8081/:3307/:5174, :8082/:3308/:5175, ...).
#
# Prerequisites: tmux (brew install tmux)
# macOS only (uses sed -i '' syntax).
# =============================================================================

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
PARENT_DIR="$(dirname "$PROJECT_DIR")"

# Worktree offset base ports
BASE_APP_PORT=8080
BASE_DB_PORT=3306
BASE_VITE_PORT=5173

# --- Helpers -----------------------------------------------------------------

usage() {
    cat <<EOF
Parallel Session Manager for Akluma

Usage: $0 {create|destroy|list} [branch-name] [issue-number]

Commands:
  create <branch> [issue]  Create a worktree + Sail stack for a parallel session
  destroy <branch>         Stop containers, remove volumes, and remove the worktree
  list                     Show active parallel sessions with ports and status

Examples:
  $0 create feature/add-export-376 376
  $0 destroy feature/add-export-376
  $0 list

Prerequisites:
  tmux (brew install tmux)
EOF
    exit 1
}

worktree_dir() {
    local branch="$1"
    local slug="${branch#feature/}"
    slug="${slug#fix/}"
    slug="${slug#chore/}"
    slug="${slug#hotfix/}"
    echo "$PARENT_DIR/akluma-wt-$slug"
}

worktree_slug() {
    local branch="$1"
    local slug="${branch#feature/}"
    slug="${slug#fix/}"
    slug="${slug#chore/}"
    slug="${slug#hotfix/}"
    echo "$slug"
}

set_env_var() {
    local key="$1" value="$2" file="$3"
    if grep -q "^${key}=" "$file" 2>/dev/null; then
        sed -i '' "s|^${key}=.*|${key}=${value}|" "$file"
    else
        echo "${key}=${value}" >> "$file"
    fi
}

next_offset() {
    local used_offsets=()

    for dir in "$PARENT_DIR"/akluma-wt-*; do
        [ -d "$dir" ] || continue
        if [ -f "$dir/.env" ]; then
            local port
            port=$(grep "^APP_PORT=" "$dir/.env" 2>/dev/null | cut -d= -f2) || true
            if [ -n "$port" ]; then
                used_offsets+=("$((port - BASE_APP_PORT))")
            fi
        fi
    done

    local offset=1
    while true; do
        local in_use=false
        for used in "${used_offsets[@]+"${used_offsets[@]}"}"; do
            if [ "$used" = "$offset" ]; then
                in_use=true
                break
            fi
        done
        if [ "$in_use" = false ]; then
            echo "$offset"
            return
        fi
        offset=$((offset + 1))
    done
}

# --- Commands ----------------------------------------------------------------

cmd_create() {
    local branch="${1:?Error: branch name required. Example: $0 create feature/my-branch}"
    local issue_number="${2:-}"
    local wt_dir
    wt_dir=$(worktree_dir "$branch")
    local slug
    slug=$(worktree_slug "$branch")
    local tmux_session="vite-wt-$slug"

    if [ -d "$wt_dir" ]; then
        echo "Error: $wt_dir already exists. Run '$0 destroy $branch' first."
        exit 1
    fi

    if ! command -v tmux &>/dev/null; then
        echo "Error: tmux is required but not installed."
        echo "Install it with: brew install tmux"
        exit 1
    fi

    local offset
    offset=$(next_offset)
    local app_port=$((BASE_APP_PORT + offset))
    local db_port=$((BASE_DB_PORT + offset))
    local vite_port=$((BASE_VITE_PORT + offset))

    echo "Creating worktree at $wt_dir ..."
    if git show-ref --verify --quiet "refs/heads/$branch" 2>/dev/null; then
        git worktree add "$wt_dir" "$branch"
    else
        echo "Branch '$branch' doesn't exist yet — creating from dev."
        git worktree add -b "$branch" "$wt_dir" dev
    fi

    if [ ! -f "$PROJECT_DIR/.env" ]; then
        echo "Error: .env not found in main repo ($PROJECT_DIR). Create it first."
        exit 1
    fi
    cp "$PROJECT_DIR/.env" "$wt_dir/.env"
    set_env_var "APP_PORT" "$app_port" "$wt_dir/.env"
    set_env_var "APP_URL" "http://localhost:${app_port}" "$wt_dir/.env"
    set_env_var "FORWARD_DB_PORT" "$db_port" "$wt_dir/.env"
    set_env_var "VITE_PORT" "$vite_port" "$wt_dir/.env"
    set_env_var "SESSION_COOKIE" "akluma_session_${app_port}" "$wt_dir/.env"

    if [ -d "$PROJECT_DIR/dev-notes" ]; then
        ln -s "$PROJECT_DIR/dev-notes" "$wt_dir/dev-notes"
        echo "Symlinked dev-notes/ from main repo."
    fi

    {
        echo "BRANCH=$branch"
        echo "TMUX_SESSION=$tmux_session"
        [ -n "$issue_number" ] && echo "ISSUE_NUMBER=$issue_number"
    } > "$wt_dir/.parallel-meta"

    echo "Installing Composer dependencies..."
    (cd "$wt_dir" && composer install --no-interaction --quiet) || {
        echo "Error: composer install failed."
        exit 1
    }

    echo "Starting Sail containers..."
    (cd "$wt_dir" && ./vendor/bin/sail up -d)

    echo "Waiting for MySQL to be ready..."
    local db_password
    db_password=$(grep "^DB_PASSWORD=" "$wt_dir/.env" | cut -d= -f2)
    local retries=0
    while true; do
        retries=$((retries + 1))
        if [ "$retries" -ge 90 ]; then
            echo "Warning: Timed out after 90 seconds. You may need to seed manually."
            break
        fi
        if (cd "$wt_dir" && docker compose exec -T mysql mysqladmin ping -p"$db_password" --silent &>/dev/null); then
            break
        fi
        sleep 1
    done

    if [ "$retries" -lt 90 ]; then
        echo "Seeding database..."
        (cd "$wt_dir" && ./vendor/bin/sail artisan migrate:fresh --seed --no-interaction) || {
            echo "Warning: Database seeding failed. You can retry manually:"
            echo "  cd $wt_dir && ./vendor/bin/sail artisan migrate:fresh --seed"
        }
    fi

    echo "Installing npm dependencies..."
    (cd "$wt_dir" && npm install --no-audit --no-fund 2>&1 | tail -1) || true

    echo "Starting Vite dev server in tmux session '$tmux_session'..."
    tmux new-session -d -s "$tmux_session" "cd '$wt_dir' && npm run dev"

    echo ""
    echo "========================================="
    echo "  Session ready!"
    echo "========================================="
    echo "  Directory:  $wt_dir"
    echo "  App:        http://localhost:${app_port}"
    echo "  MySQL:      localhost:${db_port}"
    echo "  Vite:       http://localhost:${vite_port}"
    echo "  Tmux:       tmux attach -t $tmux_session"
    echo "========================================="
    echo ""
    echo "Open $wt_dir in VS Code and start a Claude Code session."
}

cmd_destroy() {
    local branch="${1:?Error: branch name required. Example: $0 destroy feature/my-branch}"
    local wt_dir
    wt_dir=$(worktree_dir "$branch")

    if [ ! -d "$wt_dir" ]; then
        echo "Error: $wt_dir does not exist."
        echo "Run '$0 list' to see active sessions."
        exit 1
    fi

    # Kill tmux session if running
    local tmux_session=""
    if [ -f "$wt_dir/.parallel-meta" ]; then
        tmux_session=$(grep "^TMUX_SESSION=" "$wt_dir/.parallel-meta" 2>/dev/null | cut -d= -f2) || true
    fi
    if [ -n "$tmux_session" ]; then
        tmux kill-session -t "$tmux_session" 2>/dev/null || true
    fi

    echo "Stopping Sail containers and removing volumes..."
    (cd "$wt_dir" && ./vendor/bin/sail down -v) 2>/dev/null || true

    # Remove symlinks and metadata before worktree removal
    [ -L "$wt_dir/dev-notes" ] && rm "$wt_dir/dev-notes"
    [ -f "$wt_dir/.parallel-meta" ] && rm "$wt_dir/.parallel-meta"

    echo "Removing worktree..."
    git worktree remove "$wt_dir" --force

    echo ""
    echo "Session destroyed. Branch '$branch' still exists — delete it via git if you no longer need it."
}

cmd_list() {
    echo ""
    echo "Parallel sessions:"
    echo ""

    local found=false
    for dir in "$PARENT_DIR"/akluma-wt-*; do
        [ -d "$dir" ] || continue
        found=true

        local app_port="?" db_port="?" vite_port="?"
        if [ -f "$dir/.env" ]; then
            app_port=$(grep "^APP_PORT=" "$dir/.env" 2>/dev/null | cut -d= -f2) || app_port="?"
            db_port=$(grep "^FORWARD_DB_PORT=" "$dir/.env" 2>/dev/null | cut -d= -f2) || db_port="?"
            vite_port=$(grep "^VITE_PORT=" "$dir/.env" 2>/dev/null | cut -d= -f2) || vite_port="?"
        fi

        local branch="unknown" issue="" tmux_session=""
        if [ -f "$dir/.parallel-meta" ]; then
            branch=$(grep "^BRANCH=" "$dir/.parallel-meta" 2>/dev/null | cut -d= -f2) || branch="unknown"
            issue=$(grep "^ISSUE_NUMBER=" "$dir/.parallel-meta" 2>/dev/null | cut -d= -f2) || issue=""
            tmux_session=$(grep "^TMUX_SESSION=" "$dir/.parallel-meta" 2>/dev/null | cut -d= -f2) || tmux_session=""
        fi

        # Check container status
        local sail_status="stopped"
        if (cd "$dir" && docker compose ps --status running --quiet 2>/dev/null | head -1 | grep -q .); then
            sail_status="running"
        fi

        # Check tmux status
        local vite_status="stopped"
        if [ -n "$tmux_session" ] && tmux has-session -t "$tmux_session" 2>/dev/null; then
            vite_status="running"
        fi

        local issue_label=""
        [ -n "$issue" ] && issue_label=" #$issue"

        echo "  $(basename "$dir")"
        echo "    Branch:  $branch${issue_label}"
        echo "    App:     http://localhost:${app_port}  [${sail_status}]"
        echo "    MySQL:   localhost:${db_port}"
        echo "    Vite:    http://localhost:${vite_port}  [${vite_status}]"
        echo "    Tmux:    tmux attach -t ${tmux_session:-n/a}"
        echo "    Path:    $dir"
        echo ""
    done

    if [ "$found" = false ]; then
        echo "  (none)"
        echo ""
    fi

    echo "Main repo: http://localhost:80 ($(basename "$PROJECT_DIR"))"
    echo ""
}

# --- Main --------------------------------------------------------------------

case "${1:-}" in
    create)  cmd_create "${2:-}" "${3:-}" ;;
    destroy) cmd_destroy "${2:-}" ;;
    list)    cmd_list ;;
    *)       usage ;;
esac
