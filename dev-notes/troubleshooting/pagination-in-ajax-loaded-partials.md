# Pagination Links Breaking After AJAX Reload

## Problem

Pagination links changed URL structure after changing piggy bank status via dropdown, causing the partial to render without layout.

**Initial (correct):** `/{locale}/piggy-banks/7?page=2`
**After status change (broken):** `/{locale}/piggy-banks/7/schedule?page=2`

## Root Cause

When the schedule partial is reloaded via AJAX from `/{locale}/piggy-banks/{piggy_id}/schedule`, Laravel's paginator uses the **current request URL** as the base for generating pagination links. This results in links pointing to the AJAX endpoint instead of the parent page.

## Solution

Use Laravel's `setPath()` method on the paginator to explicitly set the base URL for pagination links.

## Files Changed

### 1. `/app/Http/Controllers/PiggyBankController.php` (lines 115-127)

**Before:**
```php
return view('piggy-banks.show', [
    'piggyBank' => $piggyBank,
]);
```

**After:**
```php
// Paginate the scheduled savings with correct path
$scheduledSavings = $piggyBank->scheduledSavings()
    ->paginate(50)
    ->setPath(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]));

return view('piggy-banks.show', [
    'piggyBank' => $piggyBank,
    'scheduledSavings' => $scheduledSavings,
]);
```

### 2. `/app/Http/Controllers/ScheduledSavingController.php` (lines 280-300)

**Before:**
```php
public function getSchedulePartial(Request $request, $piggy_id)
{
    $piggyBank = PiggyBank::findOrFail($piggy_id);

    return view('partials.schedule', compact('piggyBank'));
}
```

**After:**
```php
public function getSchedulePartial(Request $request, $piggy_id)
{
    $piggyBank = PiggyBank::findOrFail($piggy_id);

    // Paginate the scheduled savings with correct path
    $scheduledSavings = $piggyBank->scheduledSavings()
        ->paginate(50)
        ->setPath(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]));

    return view('partials.schedule', compact('piggyBank', 'scheduledSavings'));
}
```

### 3. `/resources/views/partials/schedule.blade.php` (lines 44, 75)

**Before:**
```blade
@foreach($piggyBank->scheduledSavings()->paginate(50) as $saving)
    ...
@endforeach

<div class="mt-4">
    {{ $piggyBank->scheduledSavings()->paginate(50)->links() }}
</div>
```

**After:**
```blade
@foreach(($scheduledSavings ?? $piggyBank->scheduledSavings()->paginate(50)->setPath(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]))) as $saving)
    ...
@endforeach

<div class="mt-4">
    {{ ($scheduledSavings ?? $piggyBank->scheduledSavings()->paginate(50)->setPath(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id])))->links() }}
</div>
```

## Key Takeaway

When paginating data in partials loaded via AJAX, always pass paginated data from the controller with `setPath()` set to the parent page route, not the AJAX endpoint route.
