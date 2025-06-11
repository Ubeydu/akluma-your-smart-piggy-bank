# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

**Development:**
- `composer dev` - Start all development services (server, queue, logs, vite)
- `./vendor/bin/sail up -d` - Start Docker environment
- `./vendor/bin/sail artisan serve` - Start Laravel server
- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

**Testing:**
- `./vendor/bin/sail pest` - Run all tests using Pest
- `./vendor/bin/sail pest tests/Feature` - Run feature tests only
- `./vendor/bin/sail pest tests/Unit` - Run unit tests only

**Code Quality:**
- `./vendor/bin/pint` - Fix PHP code style using Laravel Pint

**Database:**
- `./vendor/bin/sail artisan migrate` - Run migrations
- `./vendor/bin/sail artisan migrate:fresh --seed` - Fresh migration with seeders

## Architecture

### Localized Routing System

This application implements a custom localized routing system that automatically generates routes for multiple languages (en, tr, fr).

**Core Components:**
- `LocalizedRouteService` - Registers routes for all available locales with translated slugs
- `config/route-slugs.php` - Contains translated URL slugs for each route
- `RouteSlugHelper` - Helper for retrieving localized route slugs
- Custom route macros: `Route::localizedGet()`, `Route::localizedPost()`, etc.

**Route Structure:**
- All routes are prefixed with locale: `/{locale}/dashboard`, `/{locale}/panelim`, `/{locale}/tableau-de-bord`
- Each route generates unique names per locale: `localized.dashboard.en`, `localized.dashboard.tr`, `localized.dashboard.fr`
- Middleware automatically handles locale detection and URL generation

### Money & Currency System

**Currency Configuration:**
- Multi-currency support with different decimal place handling
- XOF/XAF currencies have 0 decimal places, others have 2
- Currency switching persists in session and user preferences
- Uses `brick/money` package for precise monetary calculations

**Helpers:**
- `CurrencyHelper::hasDecimalPlaces()` - Check if currency uses decimals
- `MoneyFormatHelper` - Format money amounts based on currency rules

### Savings & Piggy Bank System

**Core Models:**
- `PiggyBank` - Main savings goal entity
- `ScheduledSaving` - Individual saving transactions/reminders
- `User` - Extended with timezone, language, currency preferences

**Saving Strategies:**
- Pick Date: Set target date, calculate required saving frequency
- Enter Amount: Set saving amount, calculate completion timeline

### Queue & Notifications

**Jobs:**
- `SendSavingReminderJob` - Sends scheduled saving reminders via email
- Queue worker required for reminder functionality

**Email System:**
- Uses Postmark for transactional emails
- Localized email templates based on user language preference
- Reminder scheduling based on user timezone

### Multi-language Support

**Locales:** English (en), Turkish (tr), French (fr)
**Translation Files:**
- `lang/{locale}.json` - Main application translations
- `lang/{locale}/` - Laravel framework translations (auth, validation, etc.)

### Middleware

**Custom Middleware:**
- `SetLocaleFromUrl` - Extracts locale from URL and sets application locale
- `LocalizedAuthenticateMiddleware` - Redirects to localized login routes
- `ConditionalLayoutMiddleware` - Switches between authenticated/guest layouts
- `CurrencySwitcher` - Sets currency from user preferences or session