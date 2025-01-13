<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PiggyBank;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

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

test('remaining amount returns zero money object when currencies mismatch', function () {
    // Create the piggy bank with valid data
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'current_balance' => 250.00,
        'currency' => 'EUR'
    ]);

    // Replace the getRemainingAmountAttribute method temporarily for this test
    $piggyBank->setRemainingAmountOverride(function() use ($piggyBank) {
        // Force a currency mismatch by creating two Money objects with different currencies
        $totalSavings = Money::of($piggyBank->total_savings, 'EUR');
        $currentBalance = Money::of($piggyBank->current_balance ?? 0, 'USD');

        return $totalSavings->minus($currentBalance);  // This should throw MoneyMismatchException
    });

    $remainingAmount = $piggyBank->remaining_amount;

    expect($remainingAmount)
        ->toBeInstanceOf(Money::class)
        ->and($remainingAmount->isZero())->toBeTrue()
        ->and($remainingAmount->getCurrency()->getCurrencyCode())->toBe('EUR');
});


test('remaining amount equals total savings when current balance is null', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'current_balance' => null,
        'currency' => 'USD'
    ]);

    $remainingAmount = $piggyBank->remaining_amount;

    expect($remainingAmount)
        ->toBeInstanceOf(Money::class)
        ->and($remainingAmount->getAmount()->__toString())->toBe('1000.00')
        ->and($remainingAmount->getCurrency()->getCurrencyCode())->toBe('USD');
});


test('remaining amount is zero when current balance exactly equals total savings', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'current_balance' => 1000.00,
        'currency' => 'USD'
    ]);

    $remainingAmount = $piggyBank->remaining_amount;

    expect($remainingAmount)
        ->toBeInstanceOf(Money::class)
        ->and($remainingAmount->getAmount()->__toString())->toBe('0.00')
        ->and($remainingAmount->getCurrency()->getCurrencyCode())->toBe('USD');
});

test('remaining amount is negative when current balance exceeds total savings', function () {
    $piggyBank = PiggyBank::factory()->create([
        'total_savings' => 1000.00,
        'current_balance' => 1200.00,
        'currency' => 'USD'
    ]);

    $remainingAmount = $piggyBank->remaining_amount;

    expect($remainingAmount)
        ->toBeInstanceOf(Money::class)
        ->and($remainingAmount->getAmount()->__toString())->toBe('-200.00')
        ->and($remainingAmount->getCurrency()->getCurrencyCode())->toBe('USD');
});
