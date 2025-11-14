# Confirmation Dialog Alpine.js Variable Issue

**Problem Type:** Alpine.js Runtime Error
**Symptoms:** `Uncaught ReferenceError: showConfirmCancel is not defined`

---

## Problem Description

When using the `<x-confirmation-dialog>` component, you may encounter Alpine.js errors about undefined variables like `showConfirmCancel`.

### Error Message

```
alpinejs.js?v=8a40b45d:430 Uncaught ReferenceError: showConfirmCancel is not defined
    at [Alpine] showConfirmCancel (eval at safeAsyncFunction (alpinejs.js?v=8a40b45d:477:19), <anonymous>:3:32)
```

---

## Root Cause

The `<x-confirmation-dialog>` component has a default `show` prop that expects a variable named `showConfirmCancel`. When you use a different variable name in your `x-data`, you must explicitly pass it to the component using the `:show` prop.

### Component Default

Looking at `resources/views/components/confirmation-dialog.blade.php`:

```blade
@props(['show' => 'showConfirmCancel'])

<div
    x-cloak
    x-show="{{ $show }}"
    ...
>
```

The component defaults to `show="showConfirmCancel"`, so it expects that specific variable name.

---

## Solution

**Always pass the `:show` prop** when using a custom variable name for your dialog state.

### ❌ Incorrect (Will Cause Error)

```blade
<div x-data="{ showDeleteWarning: false }">
    <x-danger-button @click="showDeleteWarning = true">
        Delete
    </x-danger-button>

    <x-confirmation-dialog>  {{-- Missing :show prop! --}}
        <x-slot:title>Are you sure?</x-slot>
        <x-slot:actions>
            <!-- actions -->
        </x-slot:actions>
    </x-confirmation-dialog>
</div>
```

### ✅ Correct

```blade
<div x-data="{ showDeleteWarning: false }">
    <x-danger-button @click="showDeleteWarning = true">
        Delete
    </x-danger-button>

    <x-confirmation-dialog :show="'showDeleteWarning'">  {{-- Pass custom variable name --}}
        <x-slot:title>Are you sure?</x-slot>
        <x-slot:actions>
            <!-- actions -->
        </x-slot:actions>
    </x-confirmation-dialog>
</div>
```

---

## Examples from Codebase

### Example 1: Delete Draft Dialog

```blade
<div x-data="{ showConfirmDelete: false }">
    <x-danger-button @click="showConfirmDelete = true">
        {{ __('Delete Draft') }}
    </x-danger-button>

    <x-confirmation-dialog :show="'showConfirmDelete'">
        <x-slot:title>
            {{ __('Are you sure you want to delete this draft?') }}
        </x-slot>
        <x-slot:actions>
            <form action="{{ route('delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <x-danger-button type="submit">
                    {{ __('Yes, delete') }}
                </x-danger-button>
            </form>
            <x-secondary-button @click="showConfirmDelete = false">
                {{ __('Cancel') }}
            </x-secondary-button>
        </x-slot:actions>
    </x-confirmation-dialog>
</div>
```

### Example 2: Session Warning Dialog

```blade
<div x-data="{ showSessionWarning: {{ $hasActiveSession ? 'true' : 'false' }} }">
    <x-primary-button @click="showSessionWarning = true">
        {{ __('Resume Draft') }}
    </x-primary-button>

    <x-confirmation-dialog :show="'showSessionWarning'">
        <x-slot:title>
            {{ __('Unsaved Progress') }}
        </x-slot>
        <x-slot:content>
            <p>You're currently creating a piggy bank. Resuming this draft will discard your current progress.</p>
        </x-slot:content>
        <x-slot:actions>
            <form action="{{ route('resume') }}" method="POST">
                @csrf
                <x-primary-button type="submit">
                    {{ __('Resume This Draft') }}
                </x-primary-button>
            </form>
            <x-secondary-button @click="showSessionWarning = false">
                {{ __('Cancel') }}
            </x-secondary-button>
        </x-slot:actions>
    </x-confirmation-dialog>
</div>
```

---

## Key Takeaways

1. **Always use `:show` prop** when your variable name is not `showConfirmCancel`
2. **Variable name in quotes**: `:show="'yourVariableName'"` (note the single quotes inside double quotes)
3. **Match all references**: Ensure the variable name matches in:
   - `x-data` initialization
   - `@click` handlers that set it to `true`
   - `@click` handlers that set it to `false`
   - The `:show` prop on `<x-confirmation-dialog>`

---

## Related Files

- Component: `resources/views/components/confirmation-dialog.blade.php`
- Example Usage: `resources/views/draft-piggy-banks/show.blade.php`
- Related Pattern: `dev-notes/troubleshooting/authorization-pattern.md`
