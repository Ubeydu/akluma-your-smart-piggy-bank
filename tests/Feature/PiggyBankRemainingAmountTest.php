<?php

use App\Models\PiggyBank;
use Brick\Money\Money;

test('remaining amount is correctly calculated when current balance is less than total savings', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'current_balance' => 250.00,
        'currency' => 'USD'
    ]);

    $remainingAmount = $piggyBank->remaining_amount;

    expect($remainingAmount)
        ->toBeInstanceOf(Money::class)
        ->and($remainingAmount->getAmount()->__toString())->toBe('750.00')
        ->and($remainingAmount->getCurrency()->getCurrencyCode())->toBe('USD');
});

test('remaining amount returns zero money object when calculation fails', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'current_balance' => null, // This should trigger our error handling
        'currency' => 'USD'
    ]);

    $remainingAmount = $piggyBank->remaining_amount;

    expect($remainingAmount)
        ->toBeInstanceOf(Money::class)
        ->and($remainingAmount->isZero())->toBeTrue()
        ->and($remainingAmount->getCurrency()->getCurrencyCode())->toBe('USD');
});
