# Phase 1: Remaining Amount Database Column - Deployment Guide

**Date:** 2025-11-09
**Status:** Phase 1 Complete, Ready for Deployment

## Overview

We've implemented Phase 1 of storing `remaining_amount` as a database column instead of computing it on-the-fly. This improves performance and allows us to use database-level observers for automatic status updates in Phase 2.

## What Was Done in Phase 1

### 1. Database Migration
- ✅ Created migration: `2025_11_09_151627_add_remaining_amount_to_piggy_banks_table.php`
- ✅ Added `remaining_amount` column (decimal 12,2, nullable)
- ✅ Backfilled existing piggy banks with calculated values using chunk processing
- ✅ Migration already executed in development

### 2. Model Updates
- ✅ Added `remaining_amount` to `$fillable` in `PiggyBank` model
- ✅ Created hybrid accessor (prefers DB column, falls back to calculation)
- ✅ Added `updateRemainingAmount()` method for explicit updates

### 3. Controller Updates
Added `updateRemainingAmount()` calls in:
- ✅ `PiggyBankController::addOrRemoveMoney()` (after transaction)
- ✅ `ScheduledSavingController::update()` (after marking saving as saved/pending)
- ✅ `PiggyBankCreateController::storePickDate()` (initial creation)
- ✅ `PiggyBankCreateController::storeEnterAmount()` (initial creation)
- ✅ `ScheduleRecalculationService::recalculateSchedule()` (after schedule recalculation)

### 4. UI Polish
- ✅ Disabled recalculate form when status is done/cancelled/paused
- ✅ Always show recalculate section (no longer hidden)
- ✅ Applied opacity and cursor styling for disabled state

## NEXT STEPS: Deployment Process

### Step 0: Stage, Commit & Push Feature Branch

```bash
# Check status to see all changes
git status

# Stage all changes
git add -A

# Commit with descriptive message
git commit -m "Phase 1: Add remaining_amount database column and disable recalculate form for non-active status

- Add migration with backfill for remaining_amount column
- Update PiggyBank model with hybrid accessor and updateRemainingAmount() method
- Add updateRemainingAmount() calls in all financial change points
- Disable recalculate schedule form when status is done/cancelled/paused
- Always render recalculate section (previously hidden for non-active)
- Add translations for validation errors and success messages
- Enable staging workflow

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"

# Push to remote feature branch
git push origin feat/re-calculate-saving-schedule
```

### Step 1: Create Pull Request → `dev`

- Open GitHub
- Create PR from base: `dev` ← compare: `feat/re-calculate-saving-schedule`
- Review & merge

### Step 2: Sync Local `dev` After GitHub Merge

```bash
git switch dev
git pull origin dev
```

### Step 3: Sync `dev` with `main` (Keep Dev Up-To-Date)

Pull any hotfixes or changes that might have been pushed directly to `main`:

```bash
git switch dev
git pull origin main
# If divergent branches error occurs, use: git pull origin main --no-rebase
git push origin dev
```

### Step 4: Merge `dev` to `main` (Triggers Staging Deploy)

```bash
git switch main
git pull origin main
git merge dev
git push origin main
```

✅ GitHub Actions will automatically deploy to staging: `https://akluma-staging.fly.dev`

### Step 5: Test on Staging

**Critical Test Cases:**

1. **Mark Saving as Saved/Pending:**
   - Mark a pending saving as saved
   - Verify `remaining_amount` updates in database
   - Verify UI shows updated remaining amount
   - Mark it back to pending
   - Verify values update correctly

2. **Manual Add/Remove Money:**
   - Add money manually to a piggy bank
   - Verify `remaining_amount` updates in database
   - Verify UI reflects change
   - Withdraw money
   - Verify values update correctly

3. **Schedule Recalculation:**
   - Recalculate schedule with new periodic amount
   - Verify `remaining_amount` updates correctly
   - Verify `uptodate_final_total` is updated
   - Verify old pending items are archived

4. **Create New Piggy Bank:**
   - Create new piggy bank (both strategies: Pick Date and Enter Amount)
   - Verify `remaining_amount` is populated on creation
   - Verify value is correct

5. **Status Changes:**
   - Verify piggy bank status changes to 'done' when remaining_amount reaches 0 or less
   - Verify status changes back to 'active' when remaining_amount becomes positive again

6. **Recalculate Form Disabled State:**
   - Check piggy banks with status done/cancelled/paused
   - Verify recalculate section is visible but disabled (grayed out)
   - Verify form inputs and buttons cannot be interacted with
   - Check piggy banks with status active
   - Verify recalculate form is fully functional

7. **Edge Cases:**
   - Test the scenario from issue #279:
     - Piggy bank with remaining=200
     - Two pending savings of 200 each (user manually added 200)
     - Recalculate with 100 periodic amount
     - Verify status changes to 'done' when remaining becomes 0

### Step 6: Promote to Production

**Only proceed if all staging tests pass!**

```bash
# Merge main into prod branch
git switch prod
git pull origin prod
git merge main
git push origin prod
```

✅ GitHub Actions will automatically deploy to production: `https://akluma-prod.fly.dev` / `https://akluma.com`

### Step 7: Verify on Production

- Check a few existing piggy banks
- Verify `remaining_amount` column is populated
- Verify financial operations work correctly
- Monitor error logs for any issues

### Step 8: Sync Dev Branch

```bash
# Keep dev branch up-to-date
git switch dev
git merge main
git push origin dev
```

---

## PHASE 2: Observer Pattern (Future)

**DO NOT IMPLEMENT YET - Only after Phase 1 is stable in production**

### Goals:
- Centralize status management logic
- Automatically update piggy bank status when `remaining_amount` changes
- Remove manual status checks from controllers

### Tasks:

1. **Create PiggyBankObserver**
   ```php
   php artisan make:observer PiggyBankObserver --model=PiggyBank
   ```

   Implement `updated()` method to watch `remaining_amount` changes:
   - If `remaining_amount <= 0` and status not in ['paused', 'cancelled'] → set status to 'done'
   - If `remaining_amount > 0` and status === 'done' → set status to 'active'

2. **Register Observer**
   - Add to `App\Providers\AppServiceProvider::boot()`
   - `PiggyBank::observe(PiggyBankObserver::class);`

3. **Remove Hybrid Accessor**
   - Simplify `getRemainingAmountAttribute()` to only return DB column
   - Remove fallback calculation logic

4. **Remove Manual Status Checks**
   - Remove status update logic from:
     - `PiggyBankController::addOrRemoveMoney()`
     - `ScheduledSavingController::update()`
   - Observer will handle these automatically

5. **Test Thoroughly**
   - Same test cases as Phase 1
   - Verify observer triggers correctly
   - Verify no duplicate status updates
   - Check for infinite loops

6. **Deploy Following Same CD Workflow**
   - Merge to `main` to trigger staging deploy
   - Test thoroughly on staging
   - Merge `main` to `prod` to trigger production deploy

---

## Important Notes

- **Migration is irreversible:** The migration has already run in development. Ensure it runs successfully in staging/production.
- **Backwards Compatible:** Phase 1 is fully backwards compatible. The hybrid accessor ensures old code still works.
- **Phase 2 Timing:** Only proceed to Phase 2 after Phase 1 has been stable in production for at least a week.
- **Monitoring:** Watch error logs closely after each deployment for any calculation issues.

---

## Files Modified in Phase 1

### Migrations:
- `database/migrations/2025_11_09_151627_add_remaining_amount_to_piggy_banks_table.php`

### Models:
- `app/Models/PiggyBank.php`

### Controllers:
- `app/Http/Controllers/PiggyBankController.php`
- `app/Http/Controllers/ScheduledSavingController.php`
- `app/Http/Controllers/PiggyBankCreateController.php`

### Services:
- `app/Services/ScheduleRecalculationService.php`

### Views:
- `resources/views/piggy-banks/show.blade.php`

### Translations:
- `lang/en.json`
- `lang/tr.json`
- `lang/fr.json`
