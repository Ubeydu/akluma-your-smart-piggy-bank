<?php

namespace Tests\Unit;

use App\Models\PiggyBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Initial State Tests (at creation)
test('initial remaining amount when piggy bank is created', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'starting_amount' => 250.00,
        'currency' => 'USD',
    ]);

    // Simulate starting deposit via transaction
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'starting_amount',
        'amount' => 250.00,
        'note' => 'Initial deposit at creation',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->actual_final_total_saved)->toBe(250.00)
        ->and($piggyBank->remaining_amount)->toBe(1000.00);  // total needed - actual saved
});

// Post-Creation State Tests
test('remaining amount after saving some money', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'starting_amount' => 250.00,
        'currency' => 'USD',
    ]);

    // Simulate starting deposit
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'starting_amount',
        'amount' => 250.00,
        'note' => 'Initial deposit at creation',
    ]);
    // Simulate saving more
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'scheduled_add',
        'amount' => 250.00,
        'note' => 'Added scheduled saving',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->actual_final_total_saved)->toBe(500.00)
        ->and($piggyBank->remaining_amount)->toBe(750.00);
});

test('remaining amount when fully saved', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'starting_amount' => 250.00,
        'currency' => 'USD',
    ]);

    // Simulate starting deposit
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'starting_amount',
        'amount' => 250.00,
        'note' => 'Initial deposit at creation',
    ]);
    // Simulate saving all money
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'scheduled_add',
        'amount' => 1000.00,
        'note' => 'Added scheduled saving',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->actual_final_total_saved)->toBe(1250.00)
        ->and($piggyBank->remaining_amount)->toBe(0.00);
});

test('remaining amount when oversaved', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'starting_amount' => 250.00,
        'currency' => 'USD',
    ]);

    // Simulate starting deposit
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'starting_amount',
        'amount' => 250.00,
        'note' => 'Initial deposit at creation',
    ]);
    // Simulate saving more than needed
    $piggyBank->transactions()->create([
        'user_id' => $piggyBank->user_id,
        'type' => 'scheduled_add',
        'amount' => 1250.00,
        'note' => 'Added scheduled saving',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->actual_final_total_saved)->toBe(1500.00)
        ->and($piggyBank->remaining_amount)->toBe(-250.00);
});

test('remaining amount returns zero when override throws an error', function () {
    test()->markTestSkipped('remainingAmountOverride was removed from the PiggyBank model. This test is obsolete.');
    // This test can be deleted if you’re confident you’ll never need override logic again.
});
