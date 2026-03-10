<?php

use App\Models\PiggyBank;
use App\Models\User;

use function Pest\Laravel\actingAs;

// ──────────────────────────────────────────────────
// Choice Screen & Type Preference
// ──────────────────────────────────────────────────

it('shows the choice screen for authenticated users without a preference', function () {
    app()->setLocale('en');
    $user = User::factory()->create(['preferred_piggy_bank_type' => null]);

    actingAs($user)
        ->get('/en/create-piggy-bank/choose-type')
        ->assertOk();
});

it('redirects to classic form when user has classic preference', function () {
    app()->setLocale('en');
    $user = User::factory()->create(['preferred_piggy_bank_type' => 'classic']);

    actingAs($user)
        ->get('/en/create-piggy-bank/choose-type')
        ->assertRedirect('/en/create-piggy-bank/classic');
});

it('stores type selection and saves preference when remember is checked', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    actingAs($user)
        ->post('/en/create-piggy-bank/choose-type', [
            'type' => 'classic',
            'remember_choice' => '1',
        ])
        ->assertRedirect();

    expect($user->fresh()->preferred_piggy_bank_type)->toBe('classic');
});

it('stores type selection without saving preference when remember is not checked', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    actingAs($user)
        ->post('/en/create-piggy-bank/choose-type', [
            'type' => 'classic',
        ])
        ->assertRedirect();

    expect($user->fresh()->preferred_piggy_bank_type)->toBeNull();
});

it('clears the type preference', function () {
    app()->setLocale('en');
    $user = User::factory()->create(['preferred_piggy_bank_type' => 'classic']);

    actingAs($user)
        ->get('/en/create-piggy-bank/clear-preference')
        ->assertRedirect();

    expect($user->fresh()->preferred_piggy_bank_type)->toBeNull();
});

// ──────────────────────────────────────────────────
// Classic Form & Creation
// ──────────────────────────────────────────────────

it('shows the classic piggy bank form for guests', function () {
    app()->setLocale('en');

    $this->get('/en/create-piggy-bank/classic')
        ->assertOk();
});

it('shows the classic form for authenticated users', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    actingAs($user)
        ->get('/en/create-piggy-bank/classic')
        ->assertOk();
});

it('creates a classic piggy bank with required fields only', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    actingAs($user)
        ->post('/en/create-piggy-bank/classic/store', [
            'name' => 'My Savings Jar',
            'currency' => 'USD',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('piggy_banks', [
        'user_id' => $user->id,
        'type' => 'classic',
        'name' => 'My Savings Jar',
        'currency' => 'USD',
        'status' => 'active',
        'target_amount' => 0,
    ]);
});

it('creates a classic piggy bank with all fields', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    actingAs($user)
        ->post('/en/create-piggy-bank/classic/store', [
            'name' => 'Vacation Fund',
            'currency' => 'EUR',
            'details' => 'Saving for summer trip',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('piggy_banks', [
        'user_id' => $user->id,
        'type' => 'classic',
        'name' => 'Vacation Fund',
        'currency' => 'EUR',
        'details' => 'Saving for summer trip',
    ]);
});

it('rejects classic piggy bank creation with invalid currency', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    actingAs($user)
        ->post('/en/create-piggy-bank/classic/store', [
            'name' => 'Test',
            'currency' => 'XYZ',
        ])
        ->assertSessionHasErrors(['currency']);
});

it('rejects classic piggy bank creation without a name', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    actingAs($user)
        ->post('/en/create-piggy-bank/classic/store', [
            'name' => '',
            'currency' => 'USD',
        ])
        ->assertSessionHasErrors(['name']);
});

it('requires authentication to store a classic piggy bank', function () {
    app()->setLocale('en');

    $this->post('/en/create-piggy-bank/classic/store', [
        'name' => 'Test',
        'currency' => 'USD',
    ])->assertRedirect();

    $this->assertDatabaseMissing('piggy_banks', ['name' => 'Test']);
});

it('enforces the active piggy bank limit', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    PiggyBank::factory()
        ->count(PiggyBank::MAX_ACTIVE_PIGGY_BANKS)
        ->for($user)
        ->classic()
        ->create();

    actingAs($user)
        ->post('/en/create-piggy-bank/classic/store', [
            'name' => 'One Too Many',
            'currency' => 'USD',
        ]);

    $this->assertDatabaseMissing('piggy_banks', ['name' => 'One Too Many']);
});

// ──────────────────────────────────────────────────
// Classic Show Page
// ──────────────────────────────────────────────────

it('shows the classic piggy bank detail page', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create();

    actingAs($user)
        ->get("/en/piggy-banks/$piggyBank->id")
        ->assertOk();
});

it('returns 403 when viewing another users classic piggy bank', function () {
    app()->setLocale('en');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($owner)->classic()->create();

    actingAs($other)
        ->get("/en/piggy-banks/$piggyBank->id")
        ->assertForbidden();
});

// ──────────────────────────────────────────────────
// Add / Remove Money
// ──────────────────────────────────────────────────

it('adds money to a classic piggy bank', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create(['currency' => 'USD']);

    $this->actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'manual_add',
            'amount' => '50.00',
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('piggy_bank_transactions', [
        'piggy_bank_id' => $piggyBank->id,
        'type' => 'manual_add',
        'amount' => 50.00,
    ]);
});

it('withdraws money from a classic piggy bank', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create(['currency' => 'USD']);

    $piggyBank->transactions()->create([
        'user_id' => $user->id,
        'type' => 'manual_add',
        'amount' => 100,
    ]);

    $this->actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'manual_withdraw',
            'amount' => '30.00',
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('piggy_bank_transactions', [
        'piggy_bank_id' => $piggyBank->id,
        'type' => 'manual_withdraw',
        'amount' => -30.00,
    ]);
});

it('prevents withdrawing more than the balance', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create(['currency' => 'USD']);

    $piggyBank->transactions()->create([
        'user_id' => $user->id,
        'type' => 'manual_add',
        'amount' => 20,
    ]);

    $this->actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'manual_withdraw',
            'amount' => '50.00',
        ])
        ->assertStatus(422);
});

it('saves an optional note with a money transaction', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create(['currency' => 'USD']);

    $this->actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'manual_add',
            'amount' => '25.00',
            'note' => 'Birthday money',
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('piggy_bank_transactions', [
        'piggy_bank_id' => $piggyBank->id,
        'note' => 'Birthday money',
    ]);
});

it('rejects adding money with zero amount', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create();

    actingAs($user)
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'manual_add',
            'amount' => '0',
        ])
        ->assertUnprocessable();
});

it('rejects adding money with invalid type', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create();

    actingAs($user)
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'steal',
            'amount' => '10.00',
        ])
        ->assertUnprocessable();
});

it('rejects decimal amounts for zero-decimal currencies', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create([
        'currency' => 'XOF',
    ]);

    actingAs($user)
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'manual_add',
            'amount' => '10.50',
        ])
        ->assertUnprocessable();
});

it('prevents another user from adding money', function () {
    app()->setLocale('en');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($owner)->classic()->create();

    actingAs($other)
        ->postJson("/en/piggy-banks/$piggyBank->id/add-remove-money", [
            'type' => 'manual_add',
            'amount' => '10.00',
        ])
        ->assertForbidden();
});

// ──────────────────────────────────────────────────
// Status Changes
// ──────────────────────────────────────────────────

it('marks a classic piggy bank as done', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create();

    actingAs($user)
        ->patchJson("/en/piggy-banks/$piggyBank->id/update-status-done", [
            'status' => 'done',
        ])
        ->assertSuccessful();

    expect($piggyBank->fresh()->status)->toBe('done');
    expect($piggyBank->fresh()->actual_completed_at)->not->toBeNull();
});

it('marks a classic piggy bank as cancelled', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create();

    actingAs($user)
        ->patchJson("/en/piggy-banks/$piggyBank->id/update-status-cancelled", [
            'status' => 'cancelled',
        ])
        ->assertSuccessful();

    expect($piggyBank->fresh()->status)->toBe('cancelled');
});

it('prevents changing status of an already done piggy bank', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create(['status' => 'done']);

    actingAs($user)
        ->patchJson("/en/piggy-banks/$piggyBank->id/update-status-cancelled", [
            'status' => 'cancelled',
        ])
        ->assertStatus(400);
});

it('prevents changing status of an already cancelled piggy bank', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create(['status' => 'cancelled']);

    actingAs($user)
        ->patchJson("/en/piggy-banks/$piggyBank->id/update-status-done", [
            'status' => 'done',
        ])
        ->assertStatus(400);
});

it('prevents another user from changing status', function () {
    app()->setLocale('en');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($owner)->classic()->create();

    actingAs($other)
        ->patchJson("/en/piggy-banks/$piggyBank->id/update-status-done", [
            'status' => 'done',
        ])
        ->assertForbidden();
});

// ──────────────────────────────────────────────────
// Financial Summary Authorization
// ──────────────────────────────────────────────────

it('returns financial summary for the owner', function () {
    app()->setLocale('en');
    $user = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($user)->classic()->create();

    actingAs($user)
        ->get("/en/piggy-banks/$piggyBank->id/financial-summary")
        ->assertOk();
});

it('returns 403 for financial summary of another users piggy bank', function () {
    app()->setLocale('en');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $piggyBank = PiggyBank::factory()->for($owner)->classic()->create();

    actingAs($other)
        ->get("/en/piggy-banks/$piggyBank->id/financial-summary")
        ->assertForbidden();
});

// ──────────────────────────────────────────────────
// Guest Stash Flow
// ──────────────────────────────────────────────────

it('stashes classic data in session and redirects to register', function () {
    app()->setLocale('en');

    $this->post('/en/create-piggy-bank/classic/stash', [
        'name' => 'Guest Bank',
        'currency' => 'USD',
        'link' => '',
        'details' => '',
        'redirect_to' => 'register',
    ])->assertRedirect();

    expect(session('pending_classic_piggy_bank'))->not->toBeNull();
    expect(session('pending_classic_piggy_bank.name'))->toBe('Guest Bank');
});

it('stashes classic data in session and redirects to login', function () {
    app()->setLocale('en');

    $this->post('/en/create-piggy-bank/classic/stash', [
        'name' => 'Guest Bank',
        'currency' => 'EUR',
        'link' => '',
        'details' => '',
        'redirect_to' => 'login',
    ])->assertRedirect();

    expect(session('pending_classic_piggy_bank.currency'))->toBe('EUR');
});

it('rejects stash with invalid currency', function () {
    app()->setLocale('en');

    $this->post('/en/create-piggy-bank/classic/stash', [
        'name' => 'Guest Bank',
        'currency' => 'XYZ',
        'redirect_to' => 'register',
    ])->assertSessionHasErrors(['currency']);
});

it('creates classic piggy bank after registration with stashed data', function () {
    app()->setLocale('en');

    $this->withSession(['pending_classic_piggy_bank' => [
        'name' => 'Stashed Bank',
        'currency' => 'USD',
        'link' => null,
        'details' => null,
    ]]);

    $this->post('/en/register', [
        'name' => 'Test User',
        'email' => 'stashtest@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
        'privacy' => true,
    ]);

    $this->assertDatabaseHas('piggy_banks', [
        'type' => 'classic',
        'name' => 'Stashed Bank',
        'currency' => 'USD',
    ]);
});

it('creates classic piggy bank after login with stashed data', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    $this->withSession(['pending_classic_piggy_bank' => [
        'name' => 'Login Stashed Bank',
        'currency' => 'TRY',
        'link' => null,
        'details' => null,
    ]]);

    $this->post('/en/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertDatabaseHas('piggy_banks', [
        'user_id' => $user->id,
        'type' => 'classic',
        'name' => 'Login Stashed Bank',
        'currency' => 'TRY',
    ]);
});

it('does not create piggy bank from session if user is at the limit', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    PiggyBank::factory()
        ->count(PiggyBank::MAX_ACTIVE_PIGGY_BANKS)
        ->for($user)
        ->classic()
        ->create();

    $this->withSession(['pending_classic_piggy_bank' => [
        'name' => 'Over Limit',
        'currency' => 'USD',
        'link' => null,
        'details' => null,
    ]]);

    $this->post('/en/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertDatabaseMissing('piggy_banks', ['name' => 'Over Limit']);
});

// ──────────────────────────────────────────────────
// Dashboard Stats Exclusion
// ──────────────────────────────────────────────────

it('loads dashboard with classic and scheduled piggy banks', function () {
    app()->setLocale('en');
    $user = User::factory()->create();

    PiggyBank::factory()->for($user)->classic()->create();
    PiggyBank::factory()->for($user)->create([
        'type' => 'scheduled',
        'target_amount' => 1000,
        'remaining_amount' => 800,
        'status' => 'active',
    ]);

    actingAs($user)
        ->get('/en/dashboard')
        ->assertOk();
});
