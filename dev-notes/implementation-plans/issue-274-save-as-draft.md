# Implementation Plan for Issue #274: Save as Draft Feature

**Issue:** https://github.com/Ubeydu/akluma-your-smart-piggy-bank/issues/274

## Context Summary

**Goal:**
Allow authenticated users to save their piggy bank creation progress as a draft on the summary page (final step before creating). Users can then access, resume, or delete their drafts from a dedicated page.

**Key Decisions:**
- ✅ Separate `piggy_bank_drafts` table (not a status on piggy_banks)
- ✅ Dedicated draft list page at `/{locale}/draft-piggy-banks`
- ✅ Multiple drafts allowed per user
- ✅ Resume drafts jumps directly to summary page
- ✅ Drafts persist indefinitely (no expiration)
- ✅ Users can delete drafts
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

### Data Flow for Resuming Draft

```
User clicks "Resume" on draft
    ↓
Controller loads draft from database
    ↓
Deserialize JSON data back to Money objects
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

    // Strategy context
    $table->enum('strategy', ['pick-date', 'enter-saving-amount']);
    $table->enum('frequency', ['days', 'weeks', 'months', 'years']);

    // All creation data stored as JSON
    $table->json('step1_data'); // Product info, price, starting_amount, preview
    $table->json('step3_data'); // Strategy-specific calculations
    $table->json('payment_schedule'); // Generated schedule

    // Summary for quick display in list
    $table->decimal('price', 15, 2); // For sorting/filtering
    $table->string('preview_image')->nullable(); // For thumbnail display

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
     * Scope: Only drafts for authenticated user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
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
     */
    public function index()
    {
        $drafts = PiggyBankDraft::forUser(Auth::id())
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
     * Resume a draft (restore session and redirect to summary)
     * Route: POST /{locale}/draft-piggy-banks/{draft}/resume
     */
    public function resume(Request $request, PiggyBankDraft $draft)
    {
        // Authorization check
        if ($draft->user_id !== Auth::id()) {
            abort(403);
        }

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
            $summaryRoute = 'create-piggy-bank.pick-date.show-summary';
        } else {
            $request->session()->put('enter_saving_amount_step3', $step3Data);
            $summaryRoute = 'create-piggy-bank.enter-saving-amount.show-summary';
        }

        // Redirect to appropriate summary page
        return redirect()->route($summaryRoute);
    }

    /**
     * Delete a draft
     * Route: DELETE /{locale}/draft-piggy-banks/{draft}
     */
    public function destroy(PiggyBankDraft $draft)
    {
        // Authorization check
        if ($draft->user_id !== Auth::id()) {
            abort(403);
        }

        $draft->delete();

        return redirect()
            ->route('draft-piggy-banks.index')
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
// Draft Piggy Banks Management
Route::get(RouteSlugHelper::get('draft-piggy-banks'), [PiggyBankDraftController::class, 'index'])
    ->name('draft-piggy-banks.index');

Route::post(RouteSlugHelper::get('draft-piggy-banks') . '/store', [PiggyBankDraftController::class, 'store'])
    ->name('draft-piggy-banks.store');

Route::post(RouteSlugHelper::get('draft-piggy-banks') . '/{draft}/resume', [PiggyBankDraftController::class, 'resume'])
    ->name('draft-piggy-banks.resume');

Route::delete(RouteSlugHelper::get('draft-piggy-banks') . '/{draft}', [PiggyBankDraftController::class, 'destroy'])
    ->name('draft-piggy-banks.destroy');
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
     * Determine if the user can view the draft
     */
    public function view(User $user, PiggyBankDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }

    /**
     * Determine if the user can update/resume the draft
     */
    public function update(User $user, PiggyBankDraft $draft): bool
    {
        return $user->id === $draft->user_id;
    }

    /**
     * Determine if the user can delete the draft
     */
    public function delete(User $user, PiggyBankDraft $draft): bool
    {
        return $user->id === $draft->user_id;
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

```blade
<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('My Draft Piggy Banks') }}
            </h1>

            <a href="{{ localizedRoute('create-piggy-bank.step-1') }}" class="btn-primary">
                {{ __('Create New Piggy Bank') }}
            </a>
        </div>

        {{-- Empty State --}}
        @if($drafts->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <div class="text-gray-400 dark:text-gray-600 mb-4">
                    <svg class="mx-auto h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('No drafts yet') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('When you save a piggy bank as draft, it will appear here.') }}
                </p>
            </div>
        @endif

        {{-- Draft Cards Grid --}}
        @if($drafts->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($drafts as $draft)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow">
                        {{-- Draft Image --}}
                        <div class="aspect-video bg-gray-200 dark:bg-gray-700 rounded-t-lg overflow-hidden">
                            <img src="{{ $draft->preview_image }}"
                                 alt="{{ $draft->name }}"
                                 class="w-full h-full object-cover">
                        </div>

                        {{-- Draft Info --}}
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 truncate">
                                {{ $draft->name }}
                            </h3>

                            <div class="space-y-2 mb-4 text-sm text-gray-600 dark:text-gray-400">
                                <p>
                                    <span class="font-medium">{{ __('Price') }}:</span>
                                    {{ $draft->formatted_price }}
                                </p>
                                <p>
                                    <span class="font-medium">{{ __('Strategy') }}:</span>
                                    {{ __($draft->strategy) }}
                                </p>
                                <p>
                                    <span class="font-medium">{{ __('Frequency') }}:</span>
                                    {{ __($draft->frequency) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ __('Saved') }}: {{ $draft->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex gap-2">
                                {{-- Resume Button --}}
                                <form method="POST"
                                      action="{{ localizedRoute('draft-piggy-banks.resume', ['draft' => $draft->id]) }}"
                                      class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn-primary w-full">
                                        {{ __('Resume') }}
                                    </button>
                                </form>

                                {{-- Delete Button --}}
                                <form method="POST"
                                      action="{{ localizedRoute('draft-piggy-banks.destroy', ['draft' => $draft->id]) }}"
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this draft?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $drafts->links() }}
            </div>
        @endif
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
    "No drafts yet": "No drafts yet",
    "When you save a piggy bank as draft, it will appear here.": "When you save a piggy bank as draft, it will appear here.",
    "Resume": "Resume",
    "Draft saved successfully!": "Draft saved successfully!",
    "Draft deleted successfully!": "Draft deleted successfully!",
    "Are you sure you want to delete this draft?": "Are you sure you want to delete this draft?",
    "No piggy bank creation in progress.": "No piggy bank creation in progress.",
    "Missing required data to save draft.": "Missing required data to save draft.",
    "My Drafts": "My Drafts",
    "Saved": "Saved",
    "pick-date": "Pick Date",
    "enter-saving-amount": "Enter Saving Amount"
}
```

**Turkish (`lang/tr.json`):**
```json
{
    "Save as Draft": "Taslak Olarak Kaydet",
    "My Draft Piggy Banks": "Taslak Kumbaralarım",
    "No drafts yet": "Henüz taslak yok",
    "When you save a piggy bank as draft, it will appear here.": "Bir kumbarayı taslak olarak kaydettiğinizde burada görünecektir.",
    "Resume": "Devam Et",
    "Draft saved successfully!": "Taslak başarıyla kaydedildi!",
    "Draft deleted successfully!": "Taslak başarıyla silindi!",
    "Are you sure you want to delete this draft?": "Bu taslağı silmek istediğinizden emin misiniz?",
    "No piggy bank creation in progress.": "Devam eden kumbara oluşturma işlemi yok.",
    "Missing required data to save draft.": "Taslak kaydetmek için gerekli veriler eksik.",
    "My Drafts": "Taslaklarım",
    "Saved": "Kaydedildi",
    "pick-date": "Tarih Seç",
    "enter-saving-amount": "Birikim Tutarı Gir"
}
```

**French (`lang/fr.json`):**
```json
{
    "Save as Draft": "Enregistrer comme brouillon",
    "My Draft Piggy Banks": "Mes tirelires brouillons",
    "No drafts yet": "Pas encore de brouillons",
    "When you save a piggy bank as draft, it will appear here.": "Lorsque vous enregistrez une tirelire comme brouillon, elle apparaîtra ici.",
    "Resume": "Reprendre",
    "Draft saved successfully!": "Brouillon enregistré avec succès!",
    "Draft deleted successfully!": "Brouillon supprimé avec succès!",
    "Are you sure you want to delete this draft?": "Êtes-vous sûr de vouloir supprimer ce brouillon?",
    "No piggy bank creation in progress.": "Aucune création de tirelire en cours.",
    "Missing required data to save draft.": "Données requises manquantes pour enregistrer le brouillon.",
    "My Drafts": "Mes brouillons",
    "Saved": "Enregistré",
    "pick-date": "Choisir la date",
    "enter-saving-amount": "Entrer le montant d'épargne"
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

This implementation sets up the foundation for Issue #234 (guest user drafts):

**What's Already Prepared:**
- `user_id` is nullable in schema
- `email` column exists for guest identification
- Separate table keeps concerns isolated
- Same serialization logic can be reused

**What Will Need to be Added for #234:**
- Guest user flow to capture email before saving draft
- Draft association logic when guest registers
- Migration to link existing email-based drafts to new users
- UI changes to show guest drafts after registration

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
