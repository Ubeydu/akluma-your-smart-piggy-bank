# Implementation Plan for Issue #274: Save as Draft Feature

**Issue:** https://github.com/Ubeydu/akluma-your-smart-piggy-bank/issues/274

## Context Summary

**Goal:**
Allow authenticated users to save their piggy bank creation progress as a draft on the summary page (final step before creating). Users can then access, resume, or delete their drafts from a dedicated page.

**Key Decisions:**
- ✅ Separate `piggy_bank_drafts` table (not a status on piggy_banks)
- ✅ Dedicated draft list page at `/{locale}/draft-piggy-banks`
- ✅ Draft detail/show page at `/{locale}/draft-piggy-banks/{draft}` (read-only view)
- ✅ Multiple drafts allowed per user
- ✅ Click draft card → view details (consistent with piggy bank pattern)
- ✅ Resume from detail page → restores session and jumps to summary page
- ✅ Warn user before resuming if active creation session exists
- ✅ Drafts persist indefinitely (no expiration)
- ✅ Users can delete drafts from detail page
- ✅ Save ALL data including calculated values and payment schedule
- ✅ "Save as Draft" button appears next to "Create New Piggy Bank" button

**Why Separate Table:**
This design enables future Issue #234 (guest user drafts) by allowing drafts to be associated with email addresses before user registration, keeping concerns cleanly separated.

---

## Architecture Overview

### Data Flow for Saving Draft

```
User on Summary Page
    ↓
Clicks "Save as Draft"
    ↓
Controller reads ALL session data:
  - pick_date_step1 (or enter_saving_amount step1)
  - chosen_strategy
  - pick_date_step3 (or enter_saving_amount_step3)
  - payment_schedule
    ↓
Serialize Money objects to floats + currency code
    ↓
Store in piggy_bank_drafts table as JSON
    ↓
Clear session data
    ↓
Redirect to draft list page with success message
```

### Data Flow for Viewing Draft

```
User on Draft List Page
    ↓
Clicks on draft card
    ↓
Redirects to draft detail page (read-only view)
    ↓
Shows all draft information:
  - Product details (name, price, link, image)
  - Savings plan (strategy, frequency, target date)
  - Financial summary (target, extra, total)
  - Payment schedule table
    ↓
User can:
  - Resume draft (restore session & go to summary)
  - Delete draft
  - Go back to list
```

### Data Flow for Resuming Draft

```
User clicks "Resume" on draft detail page
    ↓
Check if active creation session exists
    ↓
If session exists → Show warning dialog:
  "You're currently creating a piggy bank.
   Resuming this draft will discard your current progress."

  Buttons:
  - "Resume This Draft" → Continue with resume
  - "Cancel" → Stay on detail page
    ↓
If no session OR user confirms → Proceed with resume:
    ↓
Controller loads draft from database
    ↓
Deserialize JSON data back to Money objects
    ↓
Clear any existing session data
    ↓
Restore ALL session keys:
  - pick_date_step1
  - chosen_strategy
  - pick_date_step3 (or enter_saving_amount_step3)
  - payment_schedule
    ↓
Redirect to appropriate summary page
    ↓
Summary page renders normally using session data
```

---

## Database Schema

### Migration: Create piggy_bank_drafts Table

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_piggy_bank_drafts_table.php`

**Command:** `php artisan make:migration create_piggy_bank_drafts_table`

```php
Schema::create('piggy_bank_drafts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
    // nullable for future Issue #234 - guest users with email

    $table->string('email')->nullable(); // For Issue #234 - guest user drafts
    $table->string('name'); // Piggy bank name for display in list
    $table->string('currency', 3); // ISO currency code

    // Strategy context - using varchar to match piggy_banks table
    $table->string('strategy'); // 'pick-date' or 'enter-saving-amount'
    $table->string('frequency'); // 'days', 'weeks', 'months', 'years'

    // All creation data stored as JSON
    $table->json('step1_data'); // Product info, price, starting_amount, preview
    $table->json('step3_data'); // Strategy-specific calculations
    $table->json('payment_schedule'); // Generated schedule

    // Summary for quick display in list
    $table->decimal('price', 12, 2); // Match piggy_banks table precision
    $table->string('preview_image')->default('images/piggy_banks/default_piggy_bank.png');

    $table->timestamps();

    // Indexes
    $table->index('user_id');
    $table->index('email'); // For Issue #234
    $table->index('created_at');
});
```

**Key Design Decisions:**
- `user_id` is nullable to support Issue #234 (guest drafts with email)
- Store everything as JSON to preserve exact session state
- Duplicate `name`, `price`, `preview_image` for list display without deserializing JSON
- `currency` at top level for proper money reconstruction

---

## Model Implementation

### Create PiggyBankDraft Model

**File:** `app/Models/PiggyBankDraft.php`

**Command:** `php artisan make:model PiggyBankDraft`

```php
<?php

namespace App\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiggyBankDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'currency',
        'strategy',
        'frequency',
        'step1_data',
        'step3_data',
        'payment_schedule',
        'price',
        'preview_image',
    ];

    protected $casts = [
        'step1_data' => 'array',
        'step3_data' => 'array',
        'payment_schedule' => 'array',
        'price' => 'decimal:2',
    ];

    /**
     * Relationship: Draft belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Serialize session data to database-storable format
     * Converts Money objects to [amount => float, currency => string]
     */
    public static function serializeSessionData(array $sessionData, string $currency): array
    {
        $serialized = [];

        foreach ($sessionData as $key => $value) {
            if ($value instanceof Money) {
                $serialized[$key] = [
                    'amount' => $value->getAmount()->toFloat(),
                    'currency' => $value->getCurrency()->getCurrencyCode(),
                ];
            } elseif (is_array($value)) {
                $serialized[$key] = self::serializeSessionData($value, $currency);
            } else {
                $serialized[$key] = $value;
            }
        }

        return $serialized;
    }

    /**
     * Deserialize database data back to session format
     * Reconstructs Money objects from [amount, currency] arrays
     */
    public static function deserializeToSession(array $data, string $currency): array
    {
        $deserialized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Check if this is a serialized Money object
                if (isset($value['amount']) && isset($value['currency'])) {
                    $deserialized[$key] = Money::of(
                        $value['amount'],
                        $value['currency']
                    );
                } else {
                    // Recursively deserialize nested arrays
                    $deserialized[$key] = self::deserializeToSession($value, $currency);
                }
            } else {
                $deserialized[$key] = $value;
            }
        }

        return $deserialized;
    }

    /**
     * Get formatted price for display
     */
    public function getFormattedPriceAttribute(): string
    {
        return Money::of($this->price, $this->currency)
            ->formatTo(app()->getLocale());
    }

    /**
     * Scope: Drafts for authenticated user
     * Returns drafts where:
     * - user_id matches (authenticated user's drafts), OR
     * - user_id is null AND email matches (guest drafts from Issue #234)
     */
    public function scopeForUser($query, int $userId, ?string $email = null)
    {
        return $query->where(function ($q) use ($userId, $email) {
            // Get drafts created by authenticated user
            $q->where('user_id', $userId);

            // Also get guest drafts with matching email (Issue #234)
            if ($email) {
                $q->orWhere(function ($subQ) use ($email) {
                    $subQ->whereNull('user_id')
                        ->where('email', $email);
                });
            }
        });
    }
}
```

---

## Controller Implementation

### Create PiggyBankDraftController

**File:** `app/Http/Controllers/PiggyBankDraftController.php`

**Command:** `php artisan make:controller PiggyBankDraftController`

```php
<?php

namespace App\Http\Controllers;

use App\Models\PiggyBankDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PiggyBankDraftController extends Controller
{
    /**
     * Display list of user's drafts
     * Route: GET /{locale}/draft-piggy-banks
     *
     * Note: For Issue #274, we only pass user_id (authenticated users only).
     * For Issue #234, we'll also pass email to include guest drafts.
     */
    public function index()
    {
        // Issue #274: Only authenticated user's drafts
        // Issue #234: Add Auth::user()->email to also fetch guest drafts
        $drafts = PiggyBankDraft::forUser(Auth::id(), Auth::user()->email)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('draft-piggy-banks.index', compact('drafts'));
    }

    /**
     * Store a new draft from summary page
     * Route: POST /{locale}/draft-piggy-banks/store
     */
    public function store(Request $request)
    {
        // Get strategy to determine which session keys to read
        $strategy = session('chosen_strategy');

        if (!$strategy) {
            return redirect()
                ->route('create-piggy-bank.step-1')
                ->with('error', __('No piggy bank creation in progress.'));
        }

        // Get session data based on strategy
        $step1Data = session('pick_date_step1');

        if ($strategy === 'pick-date') {
            $step3Data = session('pick_date_step3');
        } else {
            $step3Data = session('enter_saving_amount_step3');
        }

        $paymentSchedule = session('payment_schedule');

        // Validate required data exists
        if (!$step1Data || !$step3Data || !$paymentSchedule) {
            return redirect()
                ->route('create-piggy-bank.step-1')
                ->with('error', __('Missing required data to save draft.'));
        }

        // Serialize data (convert Money objects to storable format)
        $currency = $step1Data['currency'];
        $serializedStep1 = PiggyBankDraft::serializeSessionData($step1Data, $currency);
        $serializedStep3 = PiggyBankDraft::serializeSessionData($step3Data, $currency);
        $serializedSchedule = PiggyBankDraft::serializeSessionData($paymentSchedule, $currency);

        // Create draft
        $draft = PiggyBankDraft::create([
            'user_id' => Auth::id(),
            'name' => $step1Data['name'],
            'currency' => $currency,
            'strategy' => $strategy,
            'frequency' => $step3Data['selected_frequency'] ?? $step3Data['calculations']['selected_frequency'] ?? 'weeks',
            'step1_data' => $serializedStep1,
            'step3_data' => $serializedStep3,
            'payment_schedule' => $serializedSchedule,
            'price' => $step1Data['price']->getAmount()->toFloat(),
            'preview_image' => $step1Data['preview']['image'] ?? 'images/default_piggy_bank.png',
        ]);

        // Clear session data
        $request->session()->forget([
            'pick_date_step1',
            'pick_date_step3',
            'enter_saving_amount_step3',
            'chosen_strategy',
            'payment_schedule',
            'final_payment_date',
        ]);

        return redirect()
            ->route('draft-piggy-banks.index')
            ->with('success', __('Draft saved successfully!'));
    }

    /**
     * Show draft details (read-only view)
     * Route: GET /{locale}/draft-piggy-banks/{draft}
     */
    public function show(PiggyBankDraft $draft)
    {
        // Authorization check
        if (! Gate::allows('view', $draft)) {
            abort(403);
        }

        // Deserialize data for display
        $currency = $draft->currency;
        $step1Data = PiggyBankDraft::deserializeToSession($draft->step1_data, $currency);
        $step3Data = PiggyBankDraft::deserializeToSession($draft->step3_data, $currency);
        $paymentSchedule = PiggyBankDraft::deserializeToSession($draft->payment_schedule, $currency);

        // Prepare data for view (similar to summary pages)
        $summary = [
            'pick_date_step1' => $step1Data,
            $draft->strategy === 'pick-date' ? 'pick_date_step3' : 'enter_saving_amount_step3' => $step3Data,
        ];

        // Check if user has active creation session
        $hasActiveSession = session()->has('chosen_strategy')
            || session()->has('pick_date_step1')
            || session()->has('enter_saving_amount_step3');

        return view('draft-piggy-banks.show', compact(
            'draft',
            'summary',
            'paymentSchedule',
            'hasActiveSession'
        ));
    }

    /**
     * Resume a draft (restore session and redirect to summary)
     * Route: POST /{locale}/draft-piggy-banks/{draft}/resume
     */
    public function resume(Request $request, PiggyBankDraft $draft)
    {
        // Authorization check
        if (! Gate::allows('update', $draft)) {
            abort(403);
        }

        // Clear any existing session data first
        $request->session()->forget([
            'pick_date_step1',
            'pick_date_step3',
            'enter_saving_amount_step3',
            'chosen_strategy',
            'payment_schedule',
            'final_payment_date',
        ]);

        // Deserialize data back to session format
        $currency = $draft->currency;
        $step1Data = PiggyBankDraft::deserializeToSession($draft->step1_data, $currency);
        $step3Data = PiggyBankDraft::deserializeToSession($draft->step3_data, $currency);
        $paymentSchedule = PiggyBankDraft::deserializeToSession($draft->payment_schedule, $currency);

        // Restore session data
        $request->session()->put('pick_date_step1', $step1Data);
        $request->session()->put('chosen_strategy', $draft->strategy);
        $request->session()->put('payment_schedule', $paymentSchedule);

        if ($draft->strategy === 'pick-date') {
            $request->session()->put('pick_date_step3', $step3Data);
            $summaryRoute = 'localized.create-piggy-bank.pick-date.show-summary';
        } else {
            $request->session()->put('enter_saving_amount_step3', $step3Data);
            $summaryRoute = 'localized.create-piggy-bank.enter-saving-amount.show-summary';
        }

        // Redirect to appropriate summary page
        return redirect(localizedRoute($summaryRoute));
    }

    /**
     * Delete a draft
     * Route: DELETE /{locale}/draft-piggy-banks/{draft}
     */
    public function destroy(PiggyBankDraft $draft)
    {
        // Authorization check
        if (! Gate::allows('delete', $draft)) {
            abort(403);
        }

        $draft->delete();

        return redirect(localizedRoute('localized.draft-piggy-banks.index'))
            ->with('success', __('Draft deleted successfully!'));
    }
}
```

**Key Implementation Notes:**
- Strategy detection determines which session keys to use
- Serialization handles Money objects recursively
- Session restoration recreates exact state before draft was saved
- Authorization checks ensure users only access their own drafts

---

## Update PiggyBankCreateController

### Add "Save as Draft" Handling to Summary Pages

**File:** `app/Http/Controllers/PiggyBankCreateController.php`

**Changes Needed:**

#### 1. Update showSummary() method (Pick Date)
Around line 565, modify to handle draft parameter:

```php
public function showSummary(Request $request)
{
    // Add this check at the beginning
    if ($request->input('from_draft')) {
        // Coming from draft resume, session already populated
        // Just render the view
    }

    // ... existing code continues ...
}
```

#### 2. Update showEnterSavingAmountSummary() method
Around line 803, add same draft check:

```php
public function showEnterSavingAmountSummary(Request $request)
{
    // Add this check at the beginning
    if ($request->input('from_draft')) {
        // Coming from draft resume, session already populated
        // Just render the view
    }

    // ... existing code continues ...
}
```

**Why These Changes:**
When resuming from draft, we don't want to recalculate or regenerate anything - we want to use the exact session data that was restored. The `from_draft` parameter tells the controller to skip recalculations.

---

## Routes Configuration

### Add Draft Routes

**File:** `routes/web.php`

Add these routes inside the `auth` middleware group:

```php
use App\Http\Controllers\PiggyBankDraftController;

// Draft Piggy Banks Management
Route::localizedGet('draft-piggy-banks', [PiggyBankDraftController::class, 'index'])
    ->name('localized.draft-piggy-banks.index')
    ->middleware(['auth', 'verified']);

Route::localizedGet('draft-piggy-banks/{draft}', [PiggyBankDraftController::class, 'show'])
    ->name('localized.draft-piggy-banks.show')
    ->middleware(['auth', 'verified'])
    ->where('draft', '[0-9]+');

Route::localizedPost('draft-piggy-banks/store', [PiggyBankDraftController::class, 'store'])
    ->name('localized.draft-piggy-banks.store')
    ->middleware(['auth', 'verified']);

Route::localizedPost('draft-piggy-banks/{draft}/resume', [PiggyBankDraftController::class, 'resume'])
    ->name('localized.draft-piggy-banks.resume')
    ->middleware(['auth', 'verified'])
    ->where('draft', '[0-9]+');

Route::localizedDelete('draft-piggy-banks/{draft}', [PiggyBankDraftController::class, 'destroy'])
    ->name('localized.draft-piggy-banks.destroy')
    ->middleware(['auth', 'verified'])
    ->where('draft', '[0-9]+');
```

### Add Route Slugs

**File:** `config/route-slugs.php`

Add translations:

```php
'draft-piggy-banks' => [
    'en' => 'draft-piggy-banks',
    'tr' => 'taslak-kumbaralar',
    'fr' => 'tirelires-brouillons',
],
```

---

## Policy Implementation

### Create PiggyBankDraftPolicy

**File:** `app/Policies/PiggyBankDraftPolicy.php`

**Command:** `php artisan make:policy PiggyBankDraftPolicy --model=PiggyBankDraft`

```php
<?php

namespace App\Policies;

use App\Models\PiggyBankDraft;
use App\Models\User;

class PiggyBankDraftPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view their own drafts list
    }

    /**
     * Determine whether the user can view the model.
     * Allows access if:
     * - Draft belongs to user (user_id match), OR
     * - Draft is a guest draft (user_id null) with matching email (Issue #234)
     */
    public function view(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }

    /**
     * Determine whether the user can update the model.
     * Allows access if:
     * - Draft belongs to user (user_id match), OR
     * - Draft is a guest draft (user_id null) with matching email (Issue #234)
     */
    public function update(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }

    /**
     * Determine whether the user can delete the model.
     * Allows access if:
     * - Draft belongs to user (user_id match), OR
     * - Draft is a guest draft (user_id null) with matching email (Issue #234)
     */
    public function delete(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }
}
```

**Register Policy:**

**File:** `app/Providers/AuthServiceProvider.php`

```php
protected $policies = [
    PiggyBankDraft::class => PiggyBankDraftPolicy::class,
];
```

---

## Frontend Implementation

### 1. Update Summary Pages - Add "Save as Draft" Button

#### Pick Date Summary

**File:** `resources/views/create-piggy-bank/pick-date/summary.blade.php`

Find the button container (around line with "Create New Piggy Bank" button) and update:

```blade
<div class="flex gap-4 mt-8">
    {{-- Cancel Button --}}
    <form method="POST" action="{{ localizedRoute('create-piggy-bank.cancel') }}">
        @csrf
        <button type="submit" class="btn-secondary">
            {{ __('Cancel') }}
        </button>
    </form>

    {{-- Previous Button --}}
    <a href="{{ localizedRoute('create-piggy-bank.pick-date.step-3') }}" class="btn-secondary">
        {{ __('Previous') }}
    </a>

    {{-- Save as Draft Button (NEW) --}}
    @auth
    <form method="POST" action="{{ localizedRoute('draft-piggy-banks.store') }}">
        @csrf
        <button type="submit" class="btn-outline">
            {{ __('Save as Draft') }}
        </button>
    </form>
    @endauth

    {{-- Create New Piggy Bank Button --}}
    <form method="POST" action="{{ localizedRoute('create-piggy-bank.pick-date.store') }}">
        @csrf
        <button type="submit" class="btn-primary">
            {{ __('Create New Piggy Bank') }}
        </button>
    </form>
</div>
```

#### Enter Saving Amount Summary

**File:** `resources/views/create-piggy-bank/enter-saving-amount/summary.blade.php`

Apply same button structure change as above, using appropriate route:
```blade
{{-- Save as Draft Button (NEW) --}}
@auth
<form method="POST" action="{{ localizedRoute('draft-piggy-banks.store') }}">
    @csrf
    <button type="submit" class="btn-outline">
        {{ __('Save as Draft') }}
    </button>
</form>
@endauth
```

**Button Styling Notes:**
- Use existing button classes from the app
- `btn-outline` for secondary action (draft)
- `btn-primary` for main action (create)
- Only show "Save as Draft" for authenticated users

---

### 2. Create Draft List Page

**File:** `resources/views/draft-piggy-banks/index.blade.php`

**Design Note:** Following the piggy bank index pattern - cards are clickable links that navigate to detail page.

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                {{ __('My Draft Piggy Banks') }}
            </h2>
            <a href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                <span class="hidden sm:inline">{{ __('Create New Piggy Bank') }}</span>
                <span class="sm:hidden">{{ __('Create') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-4 px-4">
                    <div class="mt-4">
                        {{-- Empty State --}}
                        @if($drafts->isEmpty())
                            <x-empty-state
                                :title="__('draft.empty_state.title')"
                                :message="__('draft.empty_state.message')"
                                :buttonText="__('draft.empty_state.button_text')"
                                buttonLink="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
                            />
                        @else
                            {{-- Draft Cards Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($drafts as $draft)
                                    <x-draft-card :draft="$draft" />
                                @endforeach
                            </div>

                            {{-- Pagination --}}
                            @if($drafts->hasPages())
                                <div class="mt-6">
                                    {{ $drafts->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### 3. Create Draft Card Component

**File:** `resources/views/components/draft-card.blade.php`

**Design Note:** Simpler than piggy-bank-card. Entire card is a clickable link to detail page.

```blade
@props(['draft'])

<a href="{{ localizedRoute('localized.draft-piggy-banks.show', ['draft' => $draft->id]) }}"
   class="block text-current hover:no-underline">
    <div class="p-5 border rounded-lg shadow-md bg-white hover:bg-gray-50 transition-all duration-300">

        {{-- Header Section --}}
        <div class="flex items-start mb-4">
            {{-- Preview Image --}}
            <div class="mr-4 w-16 h-16 shrink-0">
                <img src="{{ asset($draft->preview_image) }}"
                     alt="{{ $draft->name }}"
                     class="w-full h-full object-cover rounded-lg shadow-xs">
            </div>

            {{-- Title and Strategy Badge --}}
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $draft->name }}</h3>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    {{ __('draft.strategy.' . $draft->strategy) }}
                </span>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-gray-500 block">{{ __('price') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    {{ \App\Helpers\MoneyFormatHelper::format($draft->price, $draft->currency) }}
                </span>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">{{ __('Saving Frequency') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    {{ ucfirst(__(strtolower($draft->frequency))) }}
                </span>
            </div>

            @if(isset($draft->step1_data['starting_amount']) && $draft->step1_data['starting_amount'])
                <div>
                    <span class="text-xs text-gray-500 block">{{ __('starting_amount') }}</span>
                    <span class="text-sm font-semibold text-gray-900">
                        @php
                            $startingAmount = $draft->step1_data['starting_amount'];
                            if (is_array($startingAmount) && isset($startingAmount['amount'])) {
                                echo \App\Helpers\MoneyFormatHelper::format(
                                    $startingAmount['amount'],
                                    $draft->currency
                                );
                            }
                        @endphp
                    </span>
                </div>
            @endif

            <div>
                <span class="text-xs text-gray-500 block">{{ __('created_at') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    {{ $draft->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
    </div>
</a>
```

### 4. Create Draft Detail Page

**File:** `resources/views/draft-piggy-banks/show.blade.php`

**Design Note:** Uses summary page structure as reference. Includes warning dialog if active session exists.

```blade
@php use Brick\Money\Money; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Draft Details') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-6 px-8">

                    {{-- Draft Status Badge --}}
                    <div class="mb-4">
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            {{ __('Draft') }} • {{ __('Saved') }} {{ $draft->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-semibold mb-6">{{ $summary['pick_date_step1']['name'] }}</h1>

                    {{-- Product Information Section --}}
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Product Details') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Name') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step1']['name'] }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Price') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ isset($summary['pick_date_step1']['price']) ? $summary['pick_date_step1']['price']->formatTo(App::getLocale()) : '-' }}
                                    </p>
                                </div>

                                @if(isset($summary['pick_date_step1']['starting_amount']) && $summary['pick_date_step1']['starting_amount'])
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Starting Amount') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{ $summary['pick_date_step1']['starting_amount']->formatTo(App::getLocale()) }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            <div class="w-48 mx-auto mt-1">
                                <div class="aspect-square h-32 md:aspect-auto md:h-32 relative overflow-hidden rounded-lg shadow-xs bg-gray-50 mx-auto">
                                    <div class="relative w-full h-full">
                                        <img
                                            src="{{ $summary['pick_date_step1']['preview']['image'] ?? asset('images/default_piggy_bank.png') }}"
                                            alt="{{ $summary['pick_date_step1']['name'] }}"
                                            class="absolute inset-0 w-full h-full object-contain"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Product Link and Details (outside grid) --}}
                        <div class="space-y-4 mt-6">
                            @if(isset($summary['pick_date_step1']['link']))
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Product Link') }}</h3>
                                <a href="{{ $summary['pick_date_step1']['link'] }}"
                                   target="_blank"
                                   class="mt-1 text-base text-blue-600 hover:text-blue-800 break-all">
                                    {{ $summary['pick_date_step1']['link'] }}
                                </a>
                            </div>
                            @endif

                            @if(isset($summary['pick_date_step1']['details']))
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Details') }}</h3>
                                <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step1']['details'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Savings Plan Section --}}
                    <div class="mb-8">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ ucfirst(__(strtolower($draft->frequency))) }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Strategy') }}</h3>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ __('draft.strategy.' . $draft->strategy) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Schedule Section --}}
                    @if(isset($paymentSchedule) && count($paymentSchedule) > 0)
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Saving Schedule') }}</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            {{ __('Saving #') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            {{ __('Date') }}
                                        </th>
                                        <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            {{ __('Amount') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paymentSchedule as $payment)
                                        <tr>
                                            <td class="px-2 py-4 text-sm font-medium text-gray-900">
                                                {{ $payment['payment_number'] ?? '-' }}
                                            </td>
                                            <td class="px-2 py-4 text-sm text-gray-500">
                                                {{ $payment['formatted_date'] ?? '-' }}
                                            </td>
                                            <td class="px-2 py-4 text-sm text-gray-900">
                                                {{ $payment['amount']->formatTo(App::getLocale()) ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:justify-between sm:space-y-0 mt-8">

                        {{-- Back to List --}}
                        <a href="{{ localizedRoute('localized.draft-piggy-banks.index') }}"
                           class="w-[200px] sm:w-auto text-center">
                            <x-secondary-button type="button" class="w-full justify-center">
                                {{ __('Back to Drafts') }}
                            </x-secondary-button>
                        </a>

                        {{-- Resume and Delete --}}
                        <div class="flex flex-col items-center sm:items-start space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">

                            {{-- Delete with confirmation --}}
                            <div x-data="{ showConfirmDelete: false }">
                                <x-danger-button
                                    @click="showConfirmDelete = true"
                                    class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                >
                                    {{ __('Delete Draft') }}
                                </x-danger-button>

                                <x-confirmation-dialog>
                                    <x-slot:title>
                                        {{ __('Are you sure you want to delete this draft?') }}
                                    </x-slot>

                                    <x-slot:actions>
                                        <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                            <form action="{{ localizedRoute('localized.draft-piggy-banks.destroy', ['draft' => $draft->id]) }}"
                                                  method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                    {{ __('Yes, delete') }}
                                                </x-danger-button>
                                            </form>

                                            <x-secondary-button
                                                @click="showConfirmDelete = false"
                                                class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                            >
                                                {{ __('Cancel') }}
                                            </x-secondary-button>
                                        </div>
                                    </x-slot:actions>
                                </x-confirmation-dialog>
                            </div>

                            {{-- Resume with session warning if needed --}}
                            <div x-data="{ showSessionWarning: {{ $hasActiveSession ? 'true' : 'false' }} }">
                                @if($hasActiveSession)
                                    {{-- Show warning button if active session --}}
                                    <x-primary-button
                                        @click="showSessionWarning = true"
                                        class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                    >
                                        {{ __('Resume Draft') }}
                                    </x-primary-button>

                                    <x-confirmation-dialog>
                                        <x-slot:title>
                                            {{ __('draft.session_warning.title') }}
                                        </x-slot>

                                        <x-slot:content>
                                            <p class="text-sm text-gray-600">
                                                {{ __('draft.session_warning.message') }}
                                            </p>
                                        </x-slot:content>

                                        <x-slot:actions>
                                            <div class="flex flex-col sm:flex-row items-center sm:items-stretch space-y-4 sm:space-y-0 sm:gap-3 sm:justify-end">
                                                <form action="{{ localizedRoute('localized.draft-piggy-banks.resume', ['draft' => $draft->id]) }}"
                                                      method="POST">
                                                    @csrf
                                                    <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                                        {{ __('draft.session_warning.resume_button') }}
                                                    </x-primary-button>
                                                </form>

                                                <x-secondary-button
                                                    @click="showSessionWarning = false"
                                                    class="w-[200px] sm:w-auto justify-center sm:justify-start"
                                                >
                                                    {{ __('Cancel') }}
                                                </x-secondary-button>
                                            </div>
                                        </x-slot:actions>
                                    </x-confirmation-dialog>
                                @else
                                    {{-- No active session, resume directly --}}
                                    <form action="{{ localizedRoute('localized.draft-piggy-banks.resume', ['draft' => $draft->id]) }}"
                                          method="POST">
                                        @csrf
                                        <x-primary-button type="submit" class="w-[200px] sm:w-auto justify-center sm:justify-start">
                                            {{ __('Resume Draft') }}
                                        </x-primary-button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

### 3. Add Navigation Link (Optional)

**File:** `resources/views/layouts/navigation.blade.php`

Add link to draft piggy banks in the navigation menu:

```blade
<x-nav-link :href="localizedRoute('draft-piggy-banks.index')" :active="request()->routeIs('draft-piggy-banks.*')">
    {{ __('My Drafts') }}
</x-nav-link>
```

---

## Translation Keys

### Add to Language Files

**Files:** `lang/en.json`, `lang/tr.json`, `lang/fr.json`

**English (`lang/en.json`):**
```json
{
    "Save as Draft": "Save as Draft",
    "My Draft Piggy Banks": "My Draft Piggy Banks",
    "Draft": "Draft",
    "Draft Details": "Draft Details",
    "Back to Drafts": "Back to Drafts",
    "Resume Draft": "Resume Draft",
    "Delete Draft": "Delete Draft",
    "Yes, delete": "Yes, delete",
    "Saving Frequency": "Saving Frequency",
    "Strategy": "Strategy",
    "Draft saved successfully!": "Draft saved successfully!",
    "Draft deleted successfully!": "Draft deleted successfully!",
    "Are you sure you want to delete this draft?": "Are you sure you want to delete this draft?",
    "No piggy bank creation in progress.": "No piggy bank creation in progress.",
    "Missing required data to save draft.": "Missing required data to save draft.",
    "Missing frequency data. Please start over.": "Missing frequency data. Please start over.",
    "draft.empty_state.title": "No Drafts Yet",
    "draft.empty_state.message": "You haven't saved any drafts. Start creating a piggy bank to save a draft.",
    "draft.empty_state.button_text": "Create Your Piggy Bank",
    "draft.strategy.pick-date": "Pick Date Strategy",
    "draft.strategy.enter-saving-amount": "Enter Amount Strategy",
    "draft.session_warning.title": "Unsaved Progress",
    "draft.session_warning.message": "You're currently creating a piggy bank. Resuming this draft will discard your current progress.",
    "draft.session_warning.resume_button": "Resume This Draft"
}
```

**Turkish (`lang/tr.json`):**
```json
{
    "Save as Draft": "Taslak Olarak Kaydet",
    "My Draft Piggy Banks": "Taslak Kumbaralarım",
    "Draft": "Taslak",
    "Draft Details": "Taslak Detayları",
    "Back to Drafts": "Taslak Listesine Dön",
    "Resume Draft": "Taslağa Devam Et",
    "Delete Draft": "Taslağı Sil",
    "Yes, delete": "Evet, sil",
    "Saving Frequency": "Birikim Sıklığı",
    "Strategy": "Strateji",
    "Draft saved successfully!": "Taslak başarıyla kaydedildi!",
    "Draft deleted successfully!": "Taslak başarıyla silindi!",
    "Are you sure you want to delete this draft?": "Bu taslağı silmek istediğinizden emin misiniz?",
    "No piggy bank creation in progress.": "Devam eden kumbara oluşturma işlemi yok.",
    "Missing required data to save draft.": "Taslak kaydetmek için gerekli veriler eksik.",
    "Missing frequency data. Please start over.": "Sıklık verisi eksik. Lütfen baştan başlayın.",
    "draft.empty_state.title": "Henüz Taslak Yok",
    "draft.empty_state.message": "Henüz hiç taslak kaydetmediniz. Taslak kaydetmek için kumbara oluşturmaya başlayın.",
    "draft.empty_state.button_text": "Kumbaranı Oluştur",
    "draft.strategy.pick-date": "Tarih Seçme Stratejisi",
    "draft.strategy.enter-saving-amount": "Tutar Girme Stratejisi",
    "draft.session_warning.title": "Kaydedilmemiş İlerleme",
    "draft.session_warning.message": "Şu anda bir kumbara oluşturuyorsunuz. Bu taslağa devam ederseniz mevcut ilerlemeniz kaybolacak.",
    "draft.session_warning.resume_button": "Bu Taslağa Devam Et"
}
```

**French (`lang/fr.json`):**
```json
{
    "Save as Draft": "Enregistrer comme brouillon",
    "My Draft Piggy Banks": "Mes tirelires brouillons",
    "Draft": "Brouillon",
    "Draft Details": "Détails du brouillon",
    "Back to Drafts": "Retour aux brouillons",
    "Resume Draft": "Reprendre le brouillon",
    "Delete Draft": "Supprimer le brouillon",
    "Yes, delete": "Oui, supprimer",
    "Saving Frequency": "Fréquence d'épargne",
    "Strategy": "Stratégie",
    "Draft saved successfully!": "Brouillon enregistré avec succès!",
    "Draft deleted successfully!": "Brouillon supprimé avec succès!",
    "Are you sure you want to delete this draft?": "Êtes-vous sûr de vouloir supprimer ce brouillon?",
    "No piggy bank creation in progress.": "Aucune création de tirelire en cours.",
    "Missing required data to save draft.": "Données requises manquantes pour enregistrer le brouillon.",
    "Missing frequency data. Please start over.": "Données de fréquence manquantes. Veuillez recommencer.",
    "draft.empty_state.title": "Aucun brouillon pour le moment",
    "draft.empty_state.message": "Vous n'avez enregistré aucun brouillon. Commencez à créer une tirelire pour enregistrer un brouillon.",
    "draft.empty_state.button_text": "Créer votre tirelire",
    "draft.strategy.pick-date": "Stratégie de choix de date",
    "draft.strategy.enter-saving-amount": "Stratégie de saisie de montant",
    "draft.session_warning.title": "Progression non enregistrée",
    "draft.session_warning.message": "Vous êtes en train de créer une tirelire. Reprendre ce brouillon supprimera votre progression actuelle.",
    "draft.session_warning.resume_button": "Reprendre ce brouillon"
}
```

---

## Testing Strategy

### Manual Testing Checklist

#### Draft Creation
- [ ] Save draft from Pick Date summary page
- [ ] Save draft from Enter Saving Amount summary page
- [ ] Verify session is cleared after saving draft
- [ ] Verify redirect to draft list page with success message
- [ ] Verify draft appears in list with correct data

#### Draft List Page
- [ ] View empty state when no drafts exist
- [ ] View multiple drafts in grid layout
- [ ] Verify draft thumbnails display correctly
- [ ] Verify pagination works with >12 drafts
- [ ] Verify draft metadata (price, strategy, frequency, saved date)

#### Resume Draft
- [ ] Resume Pick Date draft → goes to Pick Date summary
- [ ] Resume Enter Saving Amount draft → goes to Enter Saving Amount summary
- [ ] Verify all session data is restored correctly
- [ ] Verify Money objects are reconstructed properly
- [ ] Verify payment schedule displays correctly
- [ ] Verify can create piggy bank from resumed draft
- [ ] Verify can navigate back with "Previous" button after resume

#### Delete Draft
- [ ] Delete draft shows confirmation dialog
- [ ] Delete removes draft from database
- [ ] Delete redirects to list with success message
- [ ] Verify authorization (can't delete other user's drafts)

#### Edge Cases
- [ ] Try to save draft without session data → error message
- [ ] Try to resume non-existent draft → 404
- [ ] Try to access another user's draft → 403
- [ ] Save multiple drafts with same name → all saved uniquely
- [ ] Resume draft, modify data, save as new draft → two drafts exist

#### Multi-Currency Testing
- [ ] Save draft with XOF (0 decimals) → resume correctly
- [ ] Save draft with USD (2 decimals) → resume correctly
- [ ] Verify currency formatting in draft list

#### Localization Testing
- [ ] Test routes in all locales (en, tr, fr)
- [ ] Verify translations display correctly
- [ ] Verify date formatting respects locale

### Automated Testing (Optional - Future Enhancement)

**Feature Test:** `tests/Feature/PiggyBankDraftTest.php`

```php
// Test scenarios:
it('saves a draft from summary page');
it('lists user drafts');
it('resumes a draft correctly');
it('deletes a draft');
it('prevents accessing another user\'s draft');
it('serializes and deserializes Money objects correctly');
```

---

## Migration Path & Deployment

### Step-by-Step Deployment

1. **Create migration and run:**
   ```bash
   php artisan make:migration create_piggy_bank_drafts_table
   php artisan migrate
   ```

2. **Create model and policy:**
   ```bash
   php artisan make:model PiggyBankDraft
   php artisan make:policy PiggyBankDraftPolicy --model=PiggyBankDraft
   ```

3. **Create controller:**
   ```bash
   php artisan make:controller PiggyBankDraftController
   ```

4. **Implement code changes in order:**
   - Model with serialization methods
   - Policy with authorization rules
   - Controller with CRUD methods
   - Routes configuration
   - View files (summary buttons, draft list page)
   - Translations

5. **Test thoroughly in local environment**

6. **Run Pint for code style:**
   ```bash
   ./vendor/bin/pint --dirty
   ```

7. **Create feature branch and commit:**
   ```bash
   git checkout -b feature/issue-274-save-as-draft
   git add .
   git commit -m "Add save as draft feature for piggy bank creation"
   ```

8. **Deploy to staging and test**

9. **Deploy to production**

---

## Future Considerations (Issue #234)

This implementation is **already future-proofed** for Issue #234 (guest user drafts):

**What's Already Built (Issue #234 Ready):**
- ✅ `user_id` is nullable in schema
- ✅ `email` column exists for guest identification
- ✅ Separate table keeps concerns isolated
- ✅ Same serialization logic works for guest and auth users
- ✅ **Policy checks both `user_id` match AND email-based guest drafts**
- ✅ **Model scope retrieves both user drafts AND email-matched guest drafts**
- ✅ **Controller already passes email to scope for future guest draft retrieval**

**What Will Need to be Added for #234:**
- Guest user flow to capture email before saving draft on summary page
- UI change: Redirect guest to registration after saving (not to draft list)
- Success message for guest: "Draft saved! Register to view your drafts."
- Optional: Migration/command to link existing email-based drafts when guest registers (auto-update `user_id`)

---

## Common Pitfalls to Avoid

1. **Don't forget to clear session after saving draft** - Otherwise user can create both draft and piggy bank
2. **Serialize Money objects properly** - They won't store directly in JSON
3. **Handle both strategies** - Pick Date and Enter Saving Amount have different session structures
4. **Test with different currencies** - Especially 0-decimal currencies (XOF, XAF)
5. **Don't skip authorization checks** - Always verify user owns the draft
6. **Remember locale context** - All routes and dates must respect current locale
7. **Test navigation** - Ensure "Previous" button works after resuming draft

---

## Success Metrics

**Feature Complete When:**
- ✅ Users can save draft from both summary pages
- ✅ Drafts appear in dedicated list page
- ✅ Users can resume and complete drafts
- ✅ Users can delete drafts
- ✅ All session data is preserved exactly
- ✅ Money objects serialize/deserialize correctly
- ✅ Multi-currency support works
- ✅ Localization works for all languages
- ✅ Authorization prevents cross-user access
- ✅ No bugs in edge cases

---

This implementation plan provides a complete, production-ready solution for Issue #274 while laying the groundwork for future Issue #234 (guest user drafts).
