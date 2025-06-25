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
        'starting_amount' => 250.00,  // current_balance will be 250.00
        'currency' => 'USD',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->remaining_amount)->toBe(1000.00);  // total_savings + starting_amount
});

// Post-Creation State Tests
test('remaining amount after saving some money', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'starting_amount' => 250.00,  // current_balance starts at 250.00
        'currency' => 'USD',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00);  // Verify final_total is constant

    // Simulate saving money
    $piggyBank->current_balance = 500.00;
    $piggyBank->save();

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->remaining_amount)->toBe(750.00);  // Verify final_total hasn't changed
});

test('remaining amount when fully saved', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'starting_amount' => 250.00,  // current_balance starts at 250.00
        'currency' => 'USD',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00);  // Verify final_total is constant

    // Simulate saving all money
    $piggyBank->current_balance = 1250.00; // total_savings + starting_amount
    $piggyBank->save();

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->remaining_amount)->toBe(0.00);  // Verify final_total hasn't changed
});

test('remaining amount when oversaved', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'starting_amount' => 250.00,  // current_balance starts at 250.00
        'currency' => 'USD',
    ]);

    expect($piggyBank->final_total)->toBe(1250.00);  // Verify final_total is constant

    // Simulate saving more than needed
    $piggyBank->current_balance = 1500.00;
    $piggyBank->save();

    expect($piggyBank->final_total)->toBe(1250.00)
        ->and($piggyBank->remaining_amount)->toBe(-250.00);  // Verify final_total hasn't changed
});

test('remaining amount returns zero when override throws an error', function () {
    test()->markTestSkipped('remainingAmountOverride was removed from the PiggyBank model. This test is obsolete.');
    //    $piggyBank = PiggyBank::factory()->create([
    //        'total_savings' => 1000.00,
    //        'starting_amount' => 250.00,
    //        'currency' => 'EUR',
    //    ]);
    //
    //    expect($piggyBank->final_total)->toBe(1250.00);  // Verify final_total
    //
    //    $piggyBank->setRemainingAmountOverride(function () {
    //        throw new \Exception('Forced error for testing');
    //    });
    //
    //    expect($piggyBank->remaining_amount)->toBe(0.0);
});
