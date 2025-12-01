# Troubleshooting: Authorization Pattern in Controllers

## Problem

When writing authorization checks in controllers, you might be tempted to use Laravel's `$this->authorize()` method:

```php
// ❌ INCORRECT - Method not found error
public function resume(Request $request, PiggyBankDraft $draft)
{
    $this->authorize('update', $draft);
    // ...
}
```

This causes IDE warning: **"Method 'authorize' not found in Controller"**

## Solution

This application uses the **Gate facade with explicit abort** pattern:

```php
// ✅ CORRECT - Use Gate::allows() with abort(403)
public function resume(Request $request, PiggyBankDraft $draft)
{
    if (! Gate::allows('update', $draft)) {
        abort(403);
    }
    // ...
}
```

## Why This Happens

The `$this->authorize()` method requires the `AuthorizesRequests` trait in the base Controller class:

```php
// Laravel default base controller
abstract class Controller
{
    use AuthorizesRequests; // ← Required for $this->authorize()
}
```

**Our app's base controller** (`app/Http/Controllers/Controller.php`) does not include this trait, so we use Gate facade instead.

## Pattern to Follow

### ✅ Correct Pattern (Use This)

```php
use Illuminate\Support\Facades\Gate;

class YourController extends Controller
{
    public function yourMethod(Model $model)
    {
        // Check authorization
        if (! Gate::allows('update', $model)) {
            abort(403);
        }

        // Continue with logic
        $model->update([...]);
    }
}
```

### ❌ Incorrect Pattern (Don't Use)

```php
class YourController extends Controller
{
    public function yourMethod(Model $model)
    {
        // ❌ This will fail - method doesn't exist
        $this->authorize('update', $model);
    }
}
```

## Examples from Codebase

### VaultController (Correct Pattern)

**File:** `app/Http/Controllers/VaultController.php`

```php
use Illuminate\Support\Facades\Gate;

class VaultController extends Controller
{
    public function show($vault_id): View
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('view', $vault)) {
            abort(403);
        }

        return view('vaults.show', compact('vault'));
    }

    public function destroy(Vault $vault): RedirectResponse
    {
        if (! Gate::allows('delete', $vault)) {
            abort(403);
        }

        $vault->delete();

        return redirect(localizedRoute('localized.vaults.index'))
            ->with('success', __('Vault deleted successfully!'));
    }
}
```

### PiggyBankDraftController (Correct Pattern)

**File:** `app/Http/Controllers/PiggyBankDraftController.php`

```php
use Illuminate\Support\Facades\Gate;

class PiggyBankDraftController extends Controller
{
    public function resume(Request $request, PiggyBankDraft $draft)
    {
        if (! Gate::allows('update', $draft)) {
            abort(403);
        }

        // Restore session and redirect...
    }

    public function destroy(PiggyBankDraft $draft)
    {
        if (! Gate::allows('delete', $draft)) {
            abort(403);
        }

        $draft->delete();

        return redirect(localizedRoute('localized.draft-piggy-banks.index'))
            ->with('success', __('Draft deleted successfully!'));
    }
}
```

## How Policies Work

Even though we use `Gate::allows()`, the authorization logic still comes from **Policy classes**.

**Example:** `app/Policies/PiggyBankDraftPolicy.php`

```php
class PiggyBankDraftPolicy
{
    public function update(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }

    public function delete(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }
}
```

**How it connects:**
1. `Gate::allows('update', $draft)` is called
2. Laravel finds `PiggyBankDraftPolicy` registered for `PiggyBankDraft` model
3. Laravel calls `PiggyBankDraftPolicy::update($user, $draft)`
4. Returns `true` (allowed) or `false` (denied)
5. Controller aborts with 403 if denied

## Alternative Patterns (Not Used in This App)

### Alternative 1: Middleware (For entire routes)

```php
// In routes/web.php
Route::get('/drafts/{draft}', [DraftController::class, 'show'])
    ->middleware('can:view,draft');
```

**Not used in this app** - We check authorization inside controller methods instead.

### Alternative 2: authorize() Method (Requires trait)

```php
// Would work if base Controller had AuthorizesRequests trait
public function update(Request $request, Draft $draft)
{
    $this->authorize('update', $draft);
    // ...
}
```

**Not available in this app** - Base Controller doesn't have the trait.

## Quick Reference

| Scenario | Code Pattern |
|----------|-------------|
| Check view permission | `if (! Gate::allows('view', $model)) { abort(403); }` |
| Check update permission | `if (! Gate::allows('update', $model)) { abort(403); }` |
| Check delete permission | `if (! Gate::allows('delete', $model)) { abort(403); }` |
| Check create permission | `if (! Gate::allows('create', ModelClass::class)) { abort(403); }` |

**Always remember:**
1. ✅ Import: `use Illuminate\Support\Facades\Gate;`
2. ✅ Pattern: `if (! Gate::allows('action', $model)) { abort(403); }`
3. ❌ Don't use: `$this->authorize()`

## Related Files

- **Base Controller:** `app/Http/Controllers/Controller.php`
- **Gate Facade:** `Illuminate\Support\Facades\Gate`
- **Example Controllers:** `app/Http/Controllers/VaultController.php`, `app/Http/Controllers/PiggyBankDraftController.php`
- **Policies:** `app/Policies/`

---

**TL;DR:** Always use `Gate::allows()` with `abort(403)` for authorization checks in controllers. Don't use `$this->authorize()` - it won't work in this app.
