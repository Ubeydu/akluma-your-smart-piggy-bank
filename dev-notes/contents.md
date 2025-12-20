# Akluma Documentation - Contents

This directory contains technical documentation, implementation guides, deployment procedures, and troubleshooting resources for the Akluma piggy bank savings application.

**Last updated:** 2025-12-20

## Deployment & Operations

### Fly.io Deployment Guides

- [deployment-guide-flyio.md](deployment-guide-flyio.md) - Comprehensive Fly.io deployment guide covering CLI setup, app launch, MySQL configuration, scheduler/queues, logging, and GitHub Actions CI/CD
- [updated-deployment-guide-flyio.md](updated-deployment-guide-flyio.md) - Updated deployment guide with multi-environment setup using separate config files (`fly.production.toml`, `fly.staging.toml`) and branch strategy
- [actual-deployment-steps-followed-flyio.md](actual-deployment-steps-followed-flyio.md) - Step-by-step log of actual deployment process including MySQL setup, TrustProxies configuration, environment variables, custom domain (akluma.com) configuration, and Mailgun integration
- [plan-for-preserving-testing-fly.io-setup.md](plan-for-preserving-testing-fly.io-setup.md) - Recovery scripts and documentation plan for Fly.io machine configuration, cron jobs, worker processes, and safe experimentation procedures

### Continuous Deployment (GitHub Actions)

- [continuous-deployment-with-github-actions.md](continuous-deployment-with-github-actions.md) - One-time setup guide for configuring CD with GitHub Actions workflows for staging (`main` branch) and production (`prod` branch)
- [cd-day-to-day-guide.md](cd-day-to-day-guide.md) - Daily deployment workflow: `feature → dev → main → prod` flow with merge procedures and sync commands
- [direct-main-branch-fix-staging-deployment.md](direct-main-branch-fix-staging-deployment.md) - Quick workflow for pushing urgent fixes directly to main and syncing dev branch afterward

### Command Reference

- [fly-notes.txt](fly-notes.txt) - Fly.io command reference: SSH access to app/MySQL, proxy tunnels, secrets management, machine scaling, environment variable inspection, and VM memory configuration
- [prod-db-access.md](prod-db-access.md) - Production database access via SSH tunnel (`flyctl proxy 13306:3306`) with PhpStorm/DBeaver configuration and password retrieval instructions
- [prod-deploy-log.txt](prod-deploy-log.txt) - Sample production deployment log output from `fly deploy`

## Local Development

- [saving-reminders-local-dev-guide.md](saving-reminders-local-dev-guide.md) - Testing saving reminders locally with Docker/Sail: triggering `app:send-saving-reminders --force` and running queue workers
- [test_emails_dev_guide.md](test_emails_dev_guide.md) - Using Laravel's log mail driver (`MAIL_MAILER=log`) to test emails without sending real messages via Postmark
- [expose-dev-app-local-network.txt](expose-dev-app-local-network.txt) - Exposing Laravel Sail application to local network for mobile device testing with security considerations

## Architecture & Feature Documentation

- [piggy-bank-status-update-flow.md](piggy-bank-status-update-flow.md) - Comprehensive documentation of piggy bank status transitions (active/paused/done/cancelled) with Mermaid flow diagram, frontend event handling, backend controllers, localization, and vault integration
- [ui-state-persistence.md](ui-state-persistence.md) - Handling browser navigation flash data and UI state persistence for success messages and highlighted items using localStorage and Performance API
- [www-to-non-www-redirect-implementation-guide.md](www-to-non-www-redirect-implementation-guide.md) - SEO fix: implementing www to non-www redirect via Laravel `RedirectWww` middleware with `prepend()` registration

### Future Features

- [web-push-notifications-with-fcm-for-pwa.md](web-push-notifications-with-fcm-for-pwa.md) - **PLANNED:** Push notification implementation guide using Firebase Cloud Messaging for PWA support with service worker, subscription storage, and Laravel API

## Implementation Plans

Detailed planning documents for feature implementations linked to GitHub issues:

- [issue-274-save-as-draft.md](implementation-plans/issue-274-save-as-draft.md) - **Comprehensive:** Save as draft feature for piggy bank creation - database schema (`piggy_bank_drafts` table), model with Money serialization, controller CRUD, Blade views, policy authorization, route configuration, and translations (en/tr/fr)
- [issue-279-database-schema-recalculation.md](implementation-plans/issue-279-database-schema-recalculation.md) - Database schema updates for schedule recalculation: adding `archived` boolean and `recalculation_version` columns to `scheduled_savings` table with migration and model updates
- [issue-280-service-layer-recalculation.md](implementation-plans/issue-280-service-layer-recalculation.md) - `ScheduleRecalculationService` implementation for safe schedule recalculation with validation, archiving old pending items, transaction handling, and version tracking
- [phase1-remaining-amount-deployment.md](implementation-plans/phase1-remaining-amount-deployment.md) - Deployment guide for `remaining_amount` database column feature: migration, model accessor, controller updates, and comprehensive testing checklist

## Troubleshooting

Common issues and their solutions:

- [authorization-pattern.md](troubleshooting/authorization-pattern.md) - Using `Gate::allows()` with `abort(403)` instead of `$this->authorize()` for controller authorization (base Controller lacks `AuthorizesRequests` trait)
- [confirmation-dialog-alpine-variable.md](troubleshooting/confirmation-dialog-alpine-variable.md) - Fixing Alpine.js `showConfirmCancel is not defined` error when using `<x-confirmation-dialog>` component with custom variable names - always pass `:show` prop
- [localized-route-redirect-pattern.md](troubleshooting/localized-route-redirect-pattern.md) - Using `redirect(localizedRoute(...))` instead of `redirect()->route()` for localized routes to avoid IDE warnings and route resolution issues
- [pagination-in-ajax-loaded-partials.md](troubleshooting/pagination-in-ajax-loaded-partials.md) - Fixing pagination URLs in AJAX-loaded partials using `setPath()` to point to parent page instead of AJAX endpoint

## Miscellaneous

- [notes.txt](notes.txt) - Quick reference for middleware registration (`bootstrap/app.php`) and database cleanup SQL commands
- [test-docs-skip.md](test-docs-skip.md) - Test file for validating GitHub Actions skip-deploy logic on documentation-only changes

---

**Note:** All file paths are relative to the `dev-notes/` directory.

**Directory Structure:**
```
dev-notes/
├── contents.md                              # This file
├── [deployment & development guides]        # Root level markdown files
├── implementation-plans/                    # Feature implementation plans
│   ├── issue-274-save-as-draft.md
│   ├── issue-279-database-schema-recalculation.md
│   ├── issue-280-service-layer-recalculation.md
│   └── phase1-remaining-amount-deployment.md
└── troubleshooting/                         # Common issue solutions
    ├── authorization-pattern.md
    ├── confirmation-dialog-alpine-variable.md
    ├── localized-route-redirect-pattern.md
    └── pagination-in-ajax-loaded-partials.md
```

**Maintenance:** Keep this contents.md updated when adding new documentation files.
