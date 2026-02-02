# Implementation Plan for Issue #301: Custom Save Amount

**Issue:** https://github.com/Ubeydu/akluma-your-smart-piggy-bank/issues/301
**Date:** 2026-01-30

## Summary

Allow users to save a custom amount (different from scheduled) when marking a scheduled saving as "saved".

## Key Decisions

- **Keep existing checkbox** - less disruptive than replacing with button
- **Add `saved_amount` column** - tracks actual saved amount (separate from scheduled `amount`)
- **Currency-aware decimals** - currencies with `decimal_places: 2` (USD, EUR, etc.) allow decimals; currencies with `decimal_places: 0` (XOF, XAF) require integers
- **Minimum amount** - 0.01 for decimal currencies, 1 for non-decimal currencies
- **Recalculation compatible** - no changes needed to recalculation service
- **Use existing helper** - `CurrencyHelper::hasDecimalPlaces()` for currency checks

## Data Model

| Column | Purpose | When Populated |
|--------|---------|----------------|
| `amount` | Scheduled/planned amount | At schedule creation |
| `saved_amount` (NEW) | Actual amount saved | When marked as saved |

## Files to Modify

### Phase 1: Database Migration
- [ ] `database/migrations/2026_01_30_XXXXXX_add_saved_amount_to_scheduled_savings.php` (CREATE)

### Phase 2: Model Update
- [ ] `app/Models/ScheduledSaving.php` (MODIFY)

### Phase 3: Controller Update
- [ ] `app/Http/Controllers/ScheduledSavingController.php` (MODIFY)

### Phase 4: Blade View Update
- [ ] `resources/views/partials/schedule.blade.php` (MODIFY)

### Phase 5: JavaScript Update
- [ ] `resources/js/scheduled-savings.js` (MODIFY)

### Phase 6: Translations
- [ ] `lang/en.json` (MODIFY)
- [ ] `lang/tr.json` (MODIFY)
- [ ] `lang/fr.json` (MODIFY)

### Phase 7: Tests
- [ ] `tests/Feature/ScheduledSavingCustomAmountTest.php` (CREATE)

---

## Phase 1: Database Migration

**File:** `database/migrations/2026_01_30_XXXXXX_add_saved_amount_to_scheduled_savings.php`

**Purpose:** Add nullable `saved_amount` column to track actual saved amounts.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            // Amount actually saved by user (NULL for pending, filled when saved)
            // Kept separate from 'amount' (scheduled) to preserve original schedule
            $table->decimal('saved_amount', 12, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_savings', function (Blueprint $table) {
            $table->dropColumn('saved_amount');
        });
    }
};
```

**Verification:** Run migration, check column exists in database.

---

## Phase 2: Model Update

**File:** `app/Models/ScheduledSaving.php`

**Changes:**

1. Add `saved_amount` to `$fillable` array
2. Add cast for `saved_amount`
3. Update PHPDoc block

**Before:**
```php
protected $fillable = [
    'piggy_bank_id',
    'saving_number',
    'amount',
    'status',
    'saving_date',
    'archived',
    'recalculation_version',
];
```

**After:**
```php
protected $fillable = [
    'piggy_bank_id',
    'saving_number',
    'amount',
    'saved_amount',  // NEW
    'status',
    'saving_date',
    'archived',
    'recalculation_version',
];
```

**Casts - Before:**
```php
protected $casts = [
    'saving_date' => 'date',
    'amount' => 'decimal:2',
    'archived' => 'boolean',
    'last_modified_at' => 'datetime',
];
```

**Casts - After:**
```php
protected $casts = [
    'saving_date' => 'date',
    'amount' => 'decimal:2',
    'saved_amount' => 'decimal:2',  // NEW
    'archived' => 'boolean',
    'last_modified_at' => 'datetime',
];
```

**PHPDoc - Add:**
```php
 * @property float|null $saved_amount
```

**Verification:** Tinker test - create/read a ScheduledSaving with saved_amount.

---

## Phase 3: Controller Update

**File:** `app/Http/Controllers/ScheduledSavingController.php`

**Method:** `update()` (lines ~53-135)

### Change 1: Update validation (line ~60-64)

**Before:**
```php
$validatedData = $request->validate([
    'piggy_bank_id' => 'required|exists:piggy_banks,id',
    'status' => ['required', Rule::in(['saved', 'pending'])],
    'amount' => 'required|numeric|min:0',
]);
```

**After:**
```php
use App\Helpers\CurrencyHelper;

// First validate piggy_bank_id to get the piggy bank
$piggyBankId = $request->input('piggy_bank_id');
$piggyBank = PiggyBank::findOrFail($piggyBankId);

// Currency-aware amount validation
$hasDecimals = CurrencyHelper::hasDecimalPlaces($piggyBank->currency);
$amountRules = $hasDecimals
    ? 'required|numeric|min:0.01'
    : 'required|integer|min:1';

$validatedData = $request->validate([
    'piggy_bank_id' => 'required|exists:piggy_banks,id',
    'status' => ['required', Rule::in(['saved', 'pending'])],
    'amount' => $amountRules,
]);
```

### Change 2: Store saved_amount when marking as saved (around line ~75-83)

**Before:**
```php
if ($validatedData['status'] === 'saved' && $periodicSaving->status === 'pending') {
    // Marked as saved: add positive transaction
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'scheduled_add',
        'amount' => $amount,
        'note' => 'Scheduled saving marked as saved',
        'scheduled_for' => $periodicSaving->saving_date,
    ]);
}
```

**After:**
```php
if ($validatedData['status'] === 'saved' && $periodicSaving->status === 'pending') {
    // Marked as saved: add positive transaction
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'scheduled_add',
        'amount' => $amount,
        'note' => 'Scheduled saving marked as saved',
        'scheduled_for' => $periodicSaving->saving_date,
    ]);

    // Store the actual saved amount
    $periodicSaving->saved_amount = $amount;
}
```

### Change 3: Use saved_amount for undo and clear it (around line ~84-93)

**Before:**
```php
} elseif ($validatedData['status'] === 'pending' && $periodicSaving->status === 'saved') {
    // Unmarked (was saved, now pending): add negative transaction
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'scheduled_add',
        'amount' => -1 * $amount,
        'note' => 'Scheduled saving unmarked as saved',
        'scheduled_for' => $periodicSaving->saving_date,
    ]);
}
```

**After:**
```php
} elseif ($validatedData['status'] === 'pending' && $periodicSaving->status === 'saved') {
    // Unmarked (was saved, now pending): add negative transaction
    // Use saved_amount for correct reversal (handles custom amounts)
    $amountToReverse = $periodicSaving->saved_amount ?? $amount;

    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'scheduled_add',
        'amount' => -1 * $amountToReverse,
        'note' => 'Scheduled saving unmarked as saved',
        'scheduled_for' => $periodicSaving->saving_date,
    ]);

    // Clear the saved amount
    $periodicSaving->saved_amount = null;
}
```

**Verification:** Test via API/Tinker - mark as saved with custom amount, verify saved_amount stored, undo, verify cleared.

---

## Phase 4: Blade View Update

**File:** `resources/views/partials/schedule.blade.php`

### Change 1: Add table header (after "Amount" column, around line ~70-72)

**Add after the Amount th:**
```html
<th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
    {{ __('Save Amount') }}
</th>
```

### Change 2: Update checkbox data attributes (around line ~107-113)

**Before:**
```html
<input type="checkbox"
       class="scheduled-saving-checkbox ..."
       {{ $saving->status === 'saved' ? 'checked' : '' }}
       {{ in_array($piggyBank->status, ['paused', 'cancelled', 'done']) ? 'disabled' : '' }}
       data-saving-id="{{ $saving->id }}"
       data-piggy-bank-id="{{ $piggyBank->id }}"
       data-amount="{{ $saving->amount }}">
```

**After:**
```html
<input type="checkbox"
       class="scheduled-saving-checkbox ..."
       {{ $saving->status === 'saved' ? 'checked' : '' }}
       {{ in_array($piggyBank->status, ['paused', 'cancelled', 'done']) ? 'disabled' : '' }}
       data-saving-id="{{ $saving->id }}"
       data-piggy-bank-id="{{ $piggyBank->id }}"
       data-amount="{{ $saving->saved_amount ?? $saving->amount }}"
       data-scheduled-amount="{{ $saving->amount }}">
```

### Change 3: Add new table cell for Save Amount (after Amount cell, around line ~121-123)

**Add after the Amount td:**
```html
@php
    $currencyHasDecimals = \App\Helpers\CurrencyHelper::hasDecimalPlaces($piggyBank->currency);
@endphp

<td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
    @if($saving->status === 'pending' && !in_array($piggyBank->status, ['paused', 'cancelled', 'done']))
        {{-- Editable input for pending items --}}
        <input type="number"
               class="save-amount-input w-20 px-2 py-1 border border-gray-300 rounded-sm text-sm focus:ring-blue-500 focus:border-blue-500"
               data-saving-id="{{ $saving->id }}"
               data-currency-has-decimals="{{ $currencyHasDecimals ? '1' : '0' }}"
               value="{{ $currencyHasDecimals ? $saving->amount : (int) $saving->amount }}"
               min="{{ $currencyHasDecimals ? '0.01' : '1' }}"
               step="{{ $currencyHasDecimals ? '0.01' : '1' }}"
               placeholder="{{ $currencyHasDecimals ? '' : __('Whole numbers only') }}">
    @elseif($saving->status === 'saved')
        {{-- Display saved amount for saved items --}}
        {{ \App\Helpers\MoneyFormatHelper::format($saving->saved_amount ?? $saving->amount, $piggyBank->currency) }}
    @else
        {{-- Disabled state for paused/cancelled/done --}}
        <span class="text-gray-400">-</span>
    @endif
</td>
```

**Verification:** Load piggy bank page, verify new column appears with input fields for pending items.

---

## Phase 5: JavaScript Update

**File:** `resources/js/scheduled-savings.js`

### Change: Modify `handleCheckboxChange()` function (around line ~104-120)

**Before:**
```javascript
async function handleCheckboxChange(checkbox) {
    const savingId = checkbox.dataset.savingId;
    const piggyBankId = checkbox.dataset.piggyBankId;
    const amount = parseFloat(checkbox.dataset.amount);
    const newStatus = checkbox.checked ? 'saved' : 'pending';
    // ... rest of function
```

**After:**
```javascript
async function handleCheckboxChange(checkbox) {
    const savingId = checkbox.dataset.savingId;
    const piggyBankId = checkbox.dataset.piggyBankId;
    const newStatus = checkbox.checked ? 'saved' : 'pending';

    let amount;

    if (checkbox.checked) {
        // Saving: read amount from input field
        const inputField = document.querySelector(`.save-amount-input[data-saving-id="${savingId}"]`);
        if (inputField) {
            const currencyHasDecimals = inputField.dataset.currencyHasDecimals === '1';
            amount = parseFloat(inputField.value);

            // Currency-aware validation
            const minAmount = currencyHasDecimals ? 0.01 : 1;
            if (isNaN(amount) || amount < minAmount) {
                const errorMsg = currencyHasDecimals
                    ? (window.piggyBankTranslations['invalid_amount_decimal'] || 'Please enter a valid amount (minimum 0.01)')
                    : (window.piggyBankTranslations['invalid_amount_integer'] || 'Please enter a valid whole number (minimum 1)');
                showFlashMessage(errorMsg, 'error');
                checkbox.checked = false;
                return;
            }

            // For non-decimal currencies, ensure it's a whole number
            if (!currencyHasDecimals && !Number.isInteger(amount)) {
                showFlashMessage(window.piggyBankTranslations['invalid_amount_integer'] || 'Please enter a valid whole number (minimum 1)', 'error');
                checkbox.checked = false;
                return;
            }
        } else {
            // Fallback: use data-amount (shouldn't happen for pending items)
            amount = parseFloat(checkbox.dataset.amount);
        }
    } else {
        // Undoing: use saved_amount from data-amount attribute
        amount = parseFloat(checkbox.dataset.amount);
    }
    // ... rest of function unchanged
```

### Change 2: Prevent non-numeric input for non-decimal currencies

**Add this function and call it in `DOMContentLoaded` and after `attachCheckboxListeners()`:**

```javascript
function attachInputRestrictions() {
    document.querySelectorAll('.save-amount-input').forEach(function(input) {
        if (input.dataset.currencyHasDecimals === '0') {
            // Prevent typing non-numeric characters (blocks . and ,)
            input.addEventListener('keypress', function(e) {
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight'];

                if (allowedKeys.includes(e.key)) {
                    return; // Allow navigation keys
                }

                // Block if not a digit (0-9)
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Block paste of non-integer values
            input.addEventListener('paste', function(e) {
                const pastedData = e.clipboardData.getData('text');
                if (!/^\d+$/.test(pastedData)) {
                    e.preventDefault();
                }
            });
        }
    });
}
```

**Call it in two places:**

1. In `DOMContentLoaded`:
```javascript
document.addEventListener('DOMContentLoaded', function () {
    // ... existing code ...
    attachInputRestrictions();
});
```

2. After schedule partial reload (in `reloadSchedulePartial` success handler):
```javascript
.then(html => {
    // ... existing code ...
    attachCheckboxListeners();
    attachInputRestrictions();  // ADD THIS
})
```

**Verification:**
1. Build assets (`npm run build`)
2. Test saving with default amount
3. Test saving with modified amount
4. Test undo functionality
5. Test XOF currency: verify cannot type "." or ","
6. Test USD currency: verify can type "." for decimals

---

## Phase 6: Translations

### File: `lang/en.json`

**Add:**
```json
"Save Amount": "Save Amount",
"Whole numbers only": "Whole numbers only",
"invalid_amount_decimal": "Please enter a valid amount (minimum 0.01)",
"invalid_amount_integer": "Please enter a valid whole number (minimum 1)"
```

### File: `lang/tr.json`

**Add:**
```json
"Save Amount": "Kaydedilecek Tutar",
"Whole numbers only": "Sadece tam sayılar",
"invalid_amount_decimal": "Lütfen geçerli bir tutar girin (minimum 0.01)",
"invalid_amount_integer": "Lütfen geçerli bir tam sayı girin (minimum 1)"
```

### File: `lang/fr.json`

**Add:**
```json
"Save Amount": "Montant à épargner",
"Whole numbers only": "Nombres entiers uniquement",
"invalid_amount_decimal": "Veuillez entrer un montant valide (minimum 0.01)",
"invalid_amount_integer": "Veuillez entrer un nombre entier valide (minimum 1)"
```

**Verification:** Switch language, verify translations appear.

---

## Phase 7: Tests

**File:** `tests/Feature/ScheduledSavingCustomAmountTest.php`

```php
<?php

use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->piggyBank = PiggyBank::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
        'currency' => 'USD',
    ]);
    $this->scheduledSaving = ScheduledSaving::factory()->create([
        'piggy_bank_id' => $this->piggyBank->id,
        'amount' => 100,
        'status' => 'pending',
        'saved_amount' => null,
    ]);
});

it('can save with scheduled amount', function () {
    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'saved',
            'amount' => 100,
        ]);

    $response->assertOk();

    $this->scheduledSaving->refresh();
    expect($this->scheduledSaving->status)->toBe('saved');
    expect((int) $this->scheduledSaving->saved_amount)->toBe(100);
});

it('can save with custom amount greater than scheduled', function () {
    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'saved',
            'amount' => 150,
        ]);

    $response->assertOk();

    $this->scheduledSaving->refresh();
    expect($this->scheduledSaving->status)->toBe('saved');
    expect((int) $this->scheduledSaving->saved_amount)->toBe(150);
    expect((int) $this->scheduledSaving->amount)->toBe(100); // Original unchanged
});

it('can save with custom amount less than scheduled', function () {
    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'saved',
            'amount' => 50,
        ]);

    $response->assertOk();

    $this->scheduledSaving->refresh();
    expect((int) $this->scheduledSaving->saved_amount)->toBe(50);
});

it('rejects zero amount', function () {
    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'saved',
            'amount' => 0,
        ]);

    $response->assertUnprocessable();
});

it('rejects negative amount', function () {
    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'saved',
            'amount' => -10,
        ]);

    $response->assertUnprocessable();
});

it('allows decimal amount for USD currency', function () {
    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'saved',
            'amount' => 50.75,
        ]);

    $response->assertOk();

    $this->scheduledSaving->refresh();
    expect((float) $this->scheduledSaving->saved_amount)->toBe(50.75);
});

it('rejects decimal amount for XOF currency', function () {
    // Create piggy bank with XOF currency (no decimals)
    $xofPiggyBank = PiggyBank::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
        'currency' => 'XOF',
    ]);
    $xofScheduledSaving = ScheduledSaving::factory()->create([
        'piggy_bank_id' => $xofPiggyBank->id,
        'amount' => 1000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$xofScheduledSaving->id}", [
            'piggy_bank_id' => $xofPiggyBank->id,
            'status' => 'saved',
            'amount' => 1000.50, // Decimal not allowed for XOF
        ]);

    $response->assertUnprocessable();
});

it('allows integer amount for XOF currency', function () {
    $xofPiggyBank = PiggyBank::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
        'currency' => 'XOF',
    ]);
    $xofScheduledSaving = ScheduledSaving::factory()->create([
        'piggy_bank_id' => $xofPiggyBank->id,
        'amount' => 1000,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$xofScheduledSaving->id}", [
            'piggy_bank_id' => $xofPiggyBank->id,
            'status' => 'saved',
            'amount' => 1500,
        ]);

    $response->assertOk();

    $xofScheduledSaving->refresh();
    expect((int) $xofScheduledSaving->saved_amount)->toBe(1500);
});

it('correctly undoes custom saved amount', function () {
    // First save with custom amount
    $this->scheduledSaving->update([
        'status' => 'saved',
        'saved_amount' => 150,
    ]);
    $this->piggyBank->transactions()->create([
        'user_id' => $this->user->id,
        'type' => 'scheduled_add',
        'amount' => 150,
    ]);

    // Now undo
    $response = $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'pending',
            'amount' => 150, // Should use saved_amount internally
        ]);

    $response->assertOk();

    $this->scheduledSaving->refresh();
    expect($this->scheduledSaving->status)->toBe('pending');
    expect($this->scheduledSaving->saved_amount)->toBeNull();

    // Check transaction was reversed correctly
    $totalTransactions = $this->piggyBank->transactions()->sum('amount');
    expect((int) $totalTransactions)->toBe(0);
});

it('clears saved_amount on undo', function () {
    $this->scheduledSaving->update([
        'status' => 'saved',
        'saved_amount' => 75,
    ]);

    $this->actingAs($this->user)
        ->patchJson("/{$this->user->language}/scheduled-savings/{$this->scheduledSaving->id}", [
            'piggy_bank_id' => $this->piggyBank->id,
            'status' => 'pending',
            'amount' => 75,
        ]);

    $this->scheduledSaving->refresh();
    expect($this->scheduledSaving->saved_amount)->toBeNull();
});
```

**Verification:** Run tests with `./vendor/bin/sail pest tests/Feature/ScheduledSavingCustomAmountTest.php`

---

## Manual Testing Checklist

After all phases complete:

### Basic Functionality
- [ ] Save with exact scheduled amount - verify saved_amount matches
- [ ] Save with higher amount ($150 when scheduled $100) - verify both columns
- [ ] Save with lower amount ($50 when scheduled $100) - verify both columns
- [ ] Try saving 0 - should show validation error
- [ ] Try saving negative - should show validation error
- [ ] Undo a custom saved amount - balance should be correct

### Currency-Specific Testing (Decimals)
- [ ] USD piggy bank: save $50.75 - should work
- [ ] USD piggy bank: input shows step="0.01" and min="0.01"
- [ ] XOF piggy bank: save 1000 (integer) - should work
- [ ] XOF piggy bank: save 1000.50 (decimal) - should show validation error
- [ ] XOF piggy bank: input shows step="1" and min="1"

### Edge Cases
- [ ] Test on paused piggy bank - input should be disabled/hidden
- [ ] Test on cancelled piggy bank - input should be disabled/hidden
- [ ] Test on done piggy bank - input should be disabled/hidden
- [ ] Recalculate schedule after custom saves - saved items preserved
- [ ] Verify remaining_amount updates correctly after custom save

---

## Rollback Plan

If issues arise:
1. Run migration rollback: `php artisan migrate:rollback --step=1`
2. Revert model changes
3. Revert controller changes
4. Revert blade changes
5. Revert JS changes (rebuild assets)

The `saved_amount` column is nullable, so existing functionality won't break if column exists but feature is reverted.
