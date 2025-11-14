# Troubleshooting: Localized Route Redirect Pattern

## Problem

When using `redirect()->route()` with localized routes, PHPStorm/IDE shows "Unknown route name" warnings:

```php
// ❌ INCORRECT - Causes IDE warnings
return redirect()
    ->route('localized.create-piggy-bank.step-1', ['locale' => app()->getLocale()])
    ->with('error', __('Some error'));
```

## Solution

This application uses a **custom `localizedRoute()` helper function** that automatically handles locale resolution and route naming. You must use this pattern:

```php
// ✅ CORRECT - No warnings
return redirect(localizedRoute('localized.create-piggy-bank.step-1'))
    ->with('error', __('Some error'));
```

## Why This Happens

The `localizedRoute()` helper (defined in `app/helpers_global.php`) automatically:
1. Appends the current locale to the route name (e.g., `localized.dashboard` becomes `localized.dashboard.en`)
2. Adds `locale` to the parameters array
3. Generates the correct localized URL

**Key differences:**
- ✅ Use `redirect(localizedRoute(...))` - Pass the helper result directly
- ❌ Don't use `redirect()->route(...)` - This bypasses the custom routing system
- ✅ Don't manually pass `['locale' => ...]` - The helper does this automatically

## Examples from Codebase

### Correct Pattern (from PiggyBankCreateController)

```php
// Redirect to step 1
return redirect(localizedRoute('localized.create-piggy-bank.step-1'))
    ->with('error', __('Invalid strategy chosen.'));

// Redirect with parameters
return redirect(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]))
    ->with('success', __('Piggy bank created successfully!'));
```

### Incorrect Pattern (Don't Use)

```php
// ❌ This will cause IDE warnings
return redirect()
    ->route('localized.create-piggy-bank.step-1', ['locale' => app()->getLocale()]);

// ❌ This will cause route not found errors
return redirect()->route('create-piggy-bank.step-1');
```

## How the Helper Works

**File:** `app/helpers_global.php` (lines 7-36)

```php
function localizedRoute(string $routeName, array $parameters = [], ?string $locale = null): string
{
    $locale = $locale ?? app()->getLocale();

    // Create locale-specific route name: localized.dashboard.en
    $localeSpecificRouteName = $routeName . '.' . $locale;

    // Always ensure locale is in parameters
    $parameters['locale'] = $locale;

    return route($localeSpecificRouteName, $parameters);
}
```

**What it does:**
1. Takes route name like `localized.dashboard`
2. Gets current locale (e.g., `en`)
3. Creates `localized.dashboard.en`
4. Adds locale to parameters
5. Generates URL like `/en/dashboard`

## Route Registration Pattern

Routes are registered using custom macros that create locale-specific versions:

```php
// File: routes/web.php
Route::localizedGet('dashboard', [DashboardController::class, 'index'])
    ->name('localized.dashboard')
    ->middleware(['auth']);
```

This automatically creates:
- `localized.dashboard.en` → `/en/dashboard`
- `localized.dashboard.tr` → `/tr/panelim`
- `localized.dashboard.fr` → `/fr/tableau-de-bord`

## When to Use Each Pattern

### Use `localizedRoute()` for:
- ✅ Redirects in controllers
- ✅ Route generation in views
- ✅ Any time you need a localized URL

### Don't Use Regular `route()` for:
- ❌ Localized routes (routes with `localized.` prefix)
- ❌ Any route that supports multiple languages

## Related Files

- **Helper Function:** `app/helpers_global.php`
- **Route Registration:** `routes/web.php`
- **Route Service:** `app/Services/LocalizedRouteService.php`
- **Route Slugs:** `config/route-slugs.php`
- **Route Helper:** `app/Helpers/RouteHelper.php`

## Debugging Tips

If you're not sure if a route is localized:

1. **Check `routes/web.php`** - Look for `Route::localizedGet()` or `Route::localizedPost()`
2. **Check route name** - If it starts with `localized.`, use `localizedRoute()`
3. **Run `php artisan route:list | grep localized`** - See all localized routes

## Common Mistakes

### Mistake 1: Using route() instead of localizedRoute()
```php
// ❌ Wrong
return redirect()->route('localized.dashboard');

// ✅ Correct
return redirect(localizedRoute('localized.dashboard'));
```

### Mistake 2: Manually passing locale parameter
```php
// ❌ Wrong - Redundant and causes warnings
return redirect(localizedRoute('localized.dashboard', ['locale' => 'en']));

// ✅ Correct - Helper handles locale automatically
return redirect(localizedRoute('localized.dashboard'));
```

### Mistake 3: Missing localized prefix
```php
// ❌ Wrong - Route doesn't exist
return redirect(localizedRoute('dashboard'));

// ✅ Correct - Full localized route name
return redirect(localizedRoute('localized.dashboard'));
```

---

**TL;DR:** Always use `redirect(localizedRoute('localized.route.name'))` for localized routes. Never use `redirect()->route()` with localized routes.
