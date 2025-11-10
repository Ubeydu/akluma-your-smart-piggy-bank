# Implementation Plan for Issue #279: Database Schema Updates for Schedule Recalculation

**Issue:** https://github.com/Ubeydu/akluma-your-smart-piggy-bank/issues/279

## Context Summary

**Current State:**
- `scheduled_savings` table tracks individual periodic savings for piggy banks
- Current columns: id, piggy_bank_id, saving_number, amount, status (saved/pending), saving_date, notification_statuses, notification_attempts, timestamps
- `SendSavingReminders` command queries `status = 'pending'` to send email reminders
- Once created, schedules are immutable - users cannot adjust periodic amounts

**Goal:**
Enable safe schedule recalculation by adding database columns to archive old schedule items and track versions, without disrupting the existing reminder system.

---

## Detailed Implementation Steps

### 1. Create Migration for New Columns

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_recalculation_support_to_scheduled_savings.php`

**Migration Details:**
```php
up() {
    // Add 'archived' column (boolean, nullable, defaults to null/false)
    // - null/false = active schedule item
    // - true = archived (old schedule item from previous version)
    $table->boolean('archived')->nullable()->default(false)->after('status');

    // Add 'recalculation_version' column (unsigned integer, defaults to 1)
    // - Version 1 = original schedule
    // - Version 2+ = recalculated schedules
    // - Increments each time user recalculates
    $table->unsignedInteger('recalculation_version')->default(1)->after('archived');

    // Add index for efficient querying of active schedules
    $table->index(['piggy_bank_id', 'archived', 'status']);
}

down() {
    // Drop the index first
    $table->dropIndex(['piggy_bank_id', 'archived', 'status']);

    // Drop the columns
    $table->dropColumn(['archived', 'recalculation_version']);
}
```

**Rationale:**
- **`archived` column:** Using boolean instead of soft deletes because:
  - We need to preserve old schedule items for audit trail
  - Soft deletes would complicate queries unnecessarily
  - NULL-safe approach (nullable + default false) for backward compatibility

- **`recalculation_version` column:**
  - Tracks which generation of schedule this item belongs to
  - Enables tracking schedule evolution over time
  - Helps with debugging and user support

- **Index:** Composite index on `(piggy_bank_id, archived, status)` because:
  - Most queries filter by piggy bank + non-archived items
  - SendSavingReminders filters by status='pending'
  - Improves performance for recalculation operations

**Migration Command:**
```bash
./vendor/bin/sail artisan make:migration add_recalculation_support_to_scheduled_savings --table=scheduled_savings --no-interaction
```

### 2. Update ScheduledSaving Model

**File:** `app/Models/ScheduledSaving.php`

**Changes Required:**

a. **Add new fillable fields:**
```php
protected $fillable = [
    'piggy_bank_id',
    'saving_number',
    'amount',
    'status',
    'saving_date',
    'archived',              // NEW
    'recalculation_version', // NEW
];
```

b. **Add casts for new columns:**
```php
protected $casts = [
    'saving_date' => 'date',
    'amount' => 'decimal:2',
    'archived' => 'boolean',  // NEW - ensures proper boolean handling
];
```

c. **Update PHPDoc block:**
```php
/**
 * @property int $id
 * @property int $piggy_bank_id
 * @property int $saving_number
 * @property float $amount
 * @property string $status
 * @property bool $archived              // NEW
 * @property int $recalculation_version  // NEW
 * @property Carbon $saving_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PiggyBank $piggyBank
 */
```

d. **Add query scope for non-archived items (recommended for future use):**
```php
/**
 * Scope to only include non-archived scheduled savings
 */
public function scopeActive($query): void
{
    $query->where(function($q) {
        $q->whereNull('archived')
          ->orWhere('archived', false);
    });
}
```

**Rationale:**
- Adding to fillable allows mass assignment during recalculation
- Boolean cast ensures consistent handling (null/0/false all become false, 1/true becomes true)
- Query scope provides convenient way to filter non-archived items
- PHPDoc update maintains IDE autocomplete support

### 3. Update SendSavingReminders Command (Critical)

**File:** `app/Console/Commands/SendSavingReminders.php`

**Change:** Update query on line 48 to exclude archived items:

```php
// BEFORE:
$scheduledSavings = ScheduledSaving::whereDate('saving_date', $today)
    ->where('status', 'pending')
    ->whereHas('piggyBank', function ($query) {
        $query->where('status', 'active');
    })
    ->with(['piggyBank', 'piggyBank.user'])
    ->get();

// AFTER:
$scheduledSavings = ScheduledSaving::whereDate('saving_date', $today)
    ->where('status', 'pending')
    ->where(function($query) {
        $query->whereNull('archived')
              ->orWhere('archived', false);
    })
    ->whereHas('piggyBank', function ($query) {
        $query->where('status', 'active');
    })
    ->with(['piggyBank', 'piggyBank.user'])
    ->get();
```

**Rationale:**
- Ensures archived schedule items never trigger reminder emails
- NULL-safe check handles existing data (where archived is NULL)
- Critical for maintaining reminder system integrity during recalculation

### 4. Create Test for New Schema

**File:** `tests/Feature/ScheduledSavingRecalculationSchemaTest.php`

**Test Cases:**
```php
it('has archived and recalculation_version columns')
it('defaults archived to false and recalculation_version to 1')
it('can create scheduled saving with archived flag')
it('can create scheduled saving with custom recalculation_version')
it('active scope excludes archived items')
it('SendSavingReminders excludes archived items')
```

---

## Migration Execution Checklist

- [ ] Run `./vendor/bin/sail artisan make:migration add_recalculation_support_to_scheduled_savings --table=scheduled_savings --no-interaction`
- [ ] Edit the migration file with the schema changes above
- [ ] Update `app/Models/ScheduledSaving.php` with new fillable, casts, PHPDoc, and scope
- [ ] Update `app/Console/Commands/SendSavingReminders.php` to exclude archived items
- [ ] Run `./vendor/bin/sail artisan migrate` to apply changes
- [ ] Create and run tests to verify schema changes
- [ ] Run `./vendor/bin/pint` to fix code style
- [ ] Run `./vendor/bin/sail pest --filter=ScheduledSaving` to ensure existing tests pass

---

## Data Migration Considerations

**Existing Data:**
- All existing `scheduled_savings` records will have `archived = false` and `recalculation_version = 1` (from defaults)
- No data migration script needed - defaults handle backward compatibility
- Existing queries will continue to work unchanged

**Rollback Safety:**
- Migration down() safely removes columns
- No data loss on rollback (new columns only)
- Existing functionality unaffected if migration is rolled back

---

## Dependencies & Next Steps

**This Phase Enables:**
- Phase 2 (Service Layer) - will use these columns to archive old schedules and create new versions
- Phase 3 (UI) - will display recalculation history using version numbers

**No Breaking Changes:**
- Existing queries continue to work
- Default values ensure backward compatibility
- SendSavingReminders update is additive (more restrictive query)

---

## Risk Assessment

**Low Risk:**
- Non-destructive schema additions
- Default values protect existing data
- Reminder system update is conservative (excludes more, not less)

**Testing Priority:**
- Verify SendSavingReminders still sends for non-archived items
- Verify archived items are excluded from reminders
- Verify default values are applied correctly

---

This implementation plan provides a solid foundation for the schedule recalculation feature while maintaining full backward compatibility with the existing system.
