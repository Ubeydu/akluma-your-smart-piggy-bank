# Dev Notes - Contents

This directory contains technical documentation, implementation guides, and troubleshooting resources for the Akluma project.

## Deployment

- [deployment-guide-flyio.md](dev-notes/deployment-guide-flyio.md) - Comprehensive guide for deploying Laravel application on Fly.io with MySQL, scheduler, and queues
- [updated-deployment-guide-flyio.md](dev-notes/updated-deployment-guide-flyio.md) - Updated deployment guide with separate configuration files for staging and production
- [actual-deployment-steps-followed-flyio.md](dev-notes/actual-deployment-steps-followed-flyio.md) - Step-by-step record of actual deployment steps performed for Fly.io setup
- [continuous-deployment-with-github-actions.md](dev-notes/continuous-deployment-with-github-actions.md) - One-time setup guide for configuring continuous deployment with GitHub Actions and Fly.io
- [cd-day-to-day-guide.md](dev-notes/cd-day-to-day-guide.md) - Daily workflow guide for deploying code from feature branches through staging to production
- [direct-main-branch-fix-staging-deployment.md](dev-notes/direct-main-branch-fix-staging-deployment.md) - Emergency hotfix deployment procedure for staging environment
- [plan-for-preserving-testing-fly.io-setup.md](dev-notes/plan-for-preserving-testing-fly.io-setup.md) - Strategy for maintaining and testing the Fly.io deployment configuration
- [prod-db-access.md](dev-notes/prod-db-access.md) - Instructions for accessing production database via SSH tunnel and PhpStorm
- [www-to-non-www-redirect-implementation-guide.md](dev-notes/www-to-non-www-redirect-implementation-guide.md) - Laravel middleware solution for redirecting www subdomain to apex domain for SEO

## Development Guides

- [test_emails_dev_guide.md](dev-notes/test_emails_dev_guide.md) - Guide for testing email functionality locally using Laravel's log mail driver
- [saving-reminders-local-dev-guide.md](dev-notes/saving-reminders-local-dev-guide.md) - Instructions for manually triggering saving reminder jobs in local development environment
- [expose-dev-app-local-network.txt](dev-notes/expose-dev-app-local-network.txt) - Steps to expose local development server to devices on the same network
- [test-docs-skip.md](dev-notes/test-docs-skip.md) - Documentation about test execution and skip behaviors

## Implementation Plans

- [issue-280-service-layer-recalculation.md](dev-notes/implementation-plans/issue-280-service-layer-recalculation.md) - Detailed implementation plan for service layer to handle piggy bank schedule recalculation
- [issue-279-database-schema-recalculation.md](dev-notes/implementation-plans/issue-279-database-schema-recalculation.md) - Database schema changes for supporting schedule recalculation with archiving
- [issue-274-save-as-draft.md](dev-notes/implementation-plans/issue-274-save-as-draft.md) - Implementation plan for save as draft feature for piggy bank creation (Issue #274)
- [phase1-remaining-amount-deployment.md](dev-notes/implementation-plans/phase1-remaining-amount-deployment.md) - Deployment plan for remaining amount calculation feature

## Troubleshooting

- [pagination-in-ajax-loaded-partials.md](dev-notes/troubleshooting/pagination-in-ajax-loaded-partials.md) - Solution for fixing pagination links breaking after AJAX reloads using setPath() method
- [localized-route-redirect-pattern.md](dev-notes/troubleshooting/localized-route-redirect-pattern.md) - Correct pattern for using localizedRoute() helper with redirects to avoid IDE warnings
- [authorization-pattern.md](dev-notes/troubleshooting/authorization-pattern.md) - Correct authorization pattern using Gate::allows() with abort(403) instead of $this->authorize()
- [confirmation-dialog-alpine-variable.md](dev-notes/troubleshooting/confirmation-dialog-alpine-variable.md) - Fix for Alpine.js "showConfirmCancel is not defined" error when using confirmation dialog component with custom variable names

## Flow Documentation

- [piggy-bank-status-update-flow.md](dev-notes/piggy-bank-status-update-flow.md) - Complete documentation of piggy bank status transition system with vault detachment
- [ui-state-persistence.md](dev-notes/ui-state-persistence.md) - Implementation guide for handling browser navigation and persisting UI state with localStorage

## Future Features

- [web-push-notifications-with-fcm-for-pwa.md](dev-notes/web-push-notifications-with-fcm-for-pwa.md) - Implementation plan for push notification system using Firebase Cloud Messaging for PWA

## Miscellaneous Notes

- [notes.txt](dev-notes/notes.txt) - General development notes and quick references
- [fly-notes.txt](dev-notes/fly-notes.txt) - Fly.io specific configuration notes and commands
- [prod-deploy-log.txt](dev-notes/prod-deploy-log.txt) - Log of production deployment activities and issues

---

**Note:** All file paths are relative to the project root directory for easy navigation in PhpStorm and other IDEs.
