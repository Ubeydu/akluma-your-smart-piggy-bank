# Implementation Plan for Issue #280: Service Layer for Schedule Recalculation

**Issue:** https://github.com/Ubeydu/akluma-your-smart-piggy-bank/issues/280

## Context Summary

**Current State:**
- Database schema ready with `archived` and `recalculation_version` columns (Issue #279 complete)
- `SavingScheduleService` exists and generates schedules during piggy bank creation
- Schedules are created in `PiggyBankCreateController::storePiggyBank()` around line 756
- `PiggyBank` model has `getRemainingAmountAttribute()` accessor that calculates remaining amount
- `SendSavingReminders` command already excludes archived items

**Goal:**
Implement service layer to safely recalculate piggy bank saving schedules when users want to adjust their periodic saving amounts.

---

## Architecture Overview

### Service Structure

Create `app/Services/ScheduleRecalculationService.php` with these key methods:

1. **`recalculateSchedule(PiggyBank $piggyBank, float $newPeriodicAmount)`** - Main entry point
2. **`validateRecalculation(PiggyBank $piggyBank, float $newPeriodicAmount)`** - Validation logic
3. **`archiveOldPendingSchedule(PiggyBank $piggyBank)`** - Archive existing pending items
4. **`generateNewSchedule(PiggyBank $piggyBank, float $newPeriodicAmount)`** - Create new schedule items

### Data Flow

```
User Input (new periodic amount)
    ↓
ValidationRecalculation
    ↓
[DB Transaction Start]
    ↓
Archive old pending items (set archived=true)
    ↓
Increment recalculation_version
    ↓
Generate new schedule using SavingScheduleService
    ↓
Save new schedule items with new version
    ↓
[DB Transaction Commit]
```

---

## Detailed Implementation Steps

### 1. Create ScheduleRecalculationService

**File:** `app/Services/ScheduleRecalculationService.php`

**Constructor Dependencies:**
```php
public function __construct(
    private SavingScheduleService $scheduleService
) {}
```

### 2. Main Recalculation Method

**Method:** `recalculateSchedule(PiggyBank $piggyBank, float $newPeriodicAmount): bool`

**Logic:**
```php
// 1. Validate the recalculation request
$this->validateRecalculation($piggyBank, $newPeriodicAmount);

// 2. Start database transaction
DB::beginTransaction();

try {
    // 3. Get current version number
    $currentVersion = $piggyBank->scheduledSavings()
        ->where('archived', false)
        ->max('recalculation_version') ?? 1;

    $newVersion = $currentVersion + 1;

    // 4. Archive old pending schedule
    $this->archiveOldPendingSchedule($piggyBank);

    // 5. Generate and save new schedule
    $this->generateNewSchedule($piggyBank, $newPeriodicAmount, $newVersion);

    DB::commit();
    return true;

} catch (Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### 3. Validation Method

**Method:** `validateRecalculation(PiggyBank $piggyBank, float $newPeriodicAmount): void`

**Validations:**
- Piggy bank must be active (not paused, done, or cancelled)
- New periodic amount must be > 0
- New periodic amount must be different from current amount
- Must have at least one pending scheduled saving remaining
- New periodic amount should not be >= remaining amount (would complete in 1 payment)

**Example:**
```php
if ($piggyBank->status !== 'active') {
    throw new InvalidArgumentException('Cannot recalculate schedule for inactive piggy bank');
}

if ($newPeriodicAmount <= 0) {
    throw new InvalidArgumentException('New periodic amount must be greater than 0');
}

$remainingAmount = $piggyBank->remaining_amount;
if ($newPeriodicAmount >= $remainingAmount) {
    throw new InvalidArgumentException('New periodic amount must be less than remaining amount');
}

$pendingCount = $piggyBank->scheduledSavings()
    ->where('status', ScheduledSaving::STATUS_PENDING)
    ->where(function($q) {
        $q->whereNull('archived')->orWhere('archived', false);
    })
    ->count();

if ($pendingCount === 0) {
    throw new InvalidArgumentException('No pending scheduled savings to recalculate');
}
```

### 4. Archive Method

**Method:** `archiveOldPendingSchedule(PiggyBank $piggyBank): int`

**Logic:**
```php
// Only archive PENDING items, preserve SAVED items
$archivedCount = $piggyBank->scheduledSavings()
    ->where('status', ScheduledSaving::STATUS_PENDING)
    ->where(function($query) {
        $query->whereNull('archived')
              ->orWhere('archived', false);
    })
    ->update([
        'archived' => true,
        'updated_at' => now()
    ]);

return $archivedCount;
```

**Important:**
- DO NOT touch `status='saved'` items
- Only set `archived=true` for pending items
- This preserves history but excludes them from queries

### 5. Generate New Schedule Method

**Method:** `generateNewSchedule(PiggyBank $piggyBank, float $newPeriodicAmount, int $version): Collection`

**Logic:**
```php
// 1. Calculate how many payments needed
$remainingAmount = $piggyBank->remaining_amount;
$frequency = ceil($remainingAmount / $newPeriodicAmount);

// 2. Get the next saving_number
$lastSavingNumber = $piggyBank->scheduledSavings()
    ->where('status', ScheduledSaving::STATUS_SAVED)
    ->max('saving_number') ?? 0;

// 3. Determine start date (tomorrow or next scheduled date)
$startDate = Carbon::tomorrow()->toDateString();

// 4. Get frequency type from piggy bank
$periodType = $piggyBank->selected_frequency; // 'days', 'weeks', 'months', 'years'

// 5. Prepare amount details for SavingScheduleService
$amountDetails = [
    'amount' => Money::of($newPeriodicAmount, $piggyBank->currency)
];

// 6. Calculate target date based on frequency
$targetDate = Carbon::tomorrow();
switch ($periodType) {
    case 'days':
        $targetDate->addDays($frequency - 1);
        break;
    case 'weeks':
        $targetDate->addWeeks($frequency - 1);
        break;
    case 'months':
        $targetDate->addMonths($frequency - 1);
        break;
    case 'years':
        $targetDate->addYears($frequency - 1);
        break;
}

// 7. Use existing SavingScheduleService to generate schedule
$schedule = $this->scheduleService->generateSchedule(
    $targetDate->toDateString(),
    $frequency,
    $periodType,
    $amountDetails
);

// 8. Save new schedule items with version
$newSavings = collect();
foreach ($schedule as $payment) {
    $scheduledSaving = $piggyBank->scheduledSavings()->create([
        'saving_number' => $lastSavingNumber + $payment['payment_number'],
        'amount' => $payment['amount']->getAmount()->toFloat(),
        'saving_date' => $payment['date'],
        'status' => ScheduledSaving::STATUS_PENDING,
        'archived' => false,
        'recalculation_version' => $version,
    ]);

    $newSavings->push($scheduledSaving);
}

return $newSavings;
```

**Key Points:**
- Maintain sequence integrity with `saving_number`
- Continue numbering from last saved item
- Use existing `SavingScheduleService` for consistency
- Set proper `recalculation_version`

---

## Error Handling

### Exception Types
```php
// Validation errors
InvalidArgumentException - for validation failures

// Database errors
Exception - for transaction failures

// Service errors
RuntimeException - for calculation errors
```

### Error Messages
- "Cannot recalculate schedule for inactive piggy bank"
- "New periodic amount must be greater than 0"
- "New periodic amount must be less than remaining amount"
- "No pending scheduled savings to recalculate"
- "Failed to archive old schedule"
- "Failed to generate new schedule"

---

## Transaction Safety

**Critical:** All operations must be wrapped in a database transaction:

```php
DB::beginTransaction();
try {
    // 1. Archive old items
    // 2. Create new items
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    Log::error('Schedule recalculation failed', [
        'piggy_bank_id' => $piggyBank->id,
        'error' => $e->getMessage()
    ]);
    throw $e;
}
```

This ensures:
- Atomicity: All or nothing
- No partial schedule states
- Rollback on any failure

---

## Testing Checklist

### Unit Tests (if created later)
- [ ] Validates piggy bank status
- [ ] Validates new periodic amount
- [ ] Calculates correct number of payments
- [ ] Archives only pending items
- [ ] Preserves saved items
- [ ] Maintains saving_number sequence
- [ ] Creates correct number of new items
- [ ] Sets correct recalculation_version
- [ ] Handles transaction rollback
- [ ] Throws proper exceptions

### Integration Points
- Works with existing `SavingScheduleService`
- Compatible with `SendSavingReminders` command
- Respects `PiggyBank::remaining_amount` accessor
- Works with multi-currency support

---

## Dependencies

**Requires:**
- Issue #279 (Database schema) - ✅ Complete
- `app/Services/SavingScheduleService.php` - ✅ Exists
- `app/Models/PiggyBank.php` - ✅ Exists
- `app/Models/ScheduledSaving.php` - ✅ Updated

**Enables:**
- Issue #281 (UI implementation)
- User-initiated schedule recalculation
- Audit trail of schedule changes

---

## Usage Example

```php
$service = app(ScheduleRecalculationService::class);

try {
    $service->recalculateSchedule($piggyBank, 150.00);

    // Success: Schedule recalculated
    // Old pending items archived
    // New schedule created with new amounts

} catch (InvalidArgumentException $e) {
    // Validation failed: show error to user
} catch (Exception $e) {
    // System error: log and show generic error
}
```

---

## Implementation Order

1. Create `ScheduleRecalculationService` class with constructor
2. Implement `validateRecalculation()` method
3. Implement `archiveOldPendingSchedule()` method
4. Implement `generateNewSchedule()` method
5. Implement main `recalculateSchedule()` method with transaction
6. Test manually with Tinker
7. Run Pint for code style

---

This plan provides a robust, transactional approach to schedule recalculation that maintains data integrity and audit trails while reusing existing services for consistency.
