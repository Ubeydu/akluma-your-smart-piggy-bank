<?php

namespace App\Services;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Carbon\Carbon;
use Brick\Money\Money;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PickDateCalculationService
{
    // Constants to help us identify which group a time period belongs to
    private const SHORT_TERM_PERIODS = ['minute', 'hour', 'day'];
    private const LONG_TERM_PERIODS = ['week', 'month', 'year'];

    /**
     * Handles rounding for short-term savings (minutes, hours, days)
     * These are typically for smaller amounts where people handle physical money
     */
    private function roundShortTerm(float $amount, string $period): float
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        switch ($period) {
            case 'minute':
                if ($amount < 1) return 1.0;  // Minimum 1 TRY per minute
                if ($amount < 10) return ceil($amount);  // Use 1 TRY increments
                if ($amount < 50) return ceil($amount / 5) * 5;  // Use 5 TRY increments
                return ceil($amount / 10) * 10;  // Use 10 TRY increments

            case 'hour':
                if ($amount < 50) return ceil($amount / 5) * 5;  // 5 TRY increments
                if ($amount < 200) return ceil($amount / 10) * 10;  // 10 TRY increments
                if ($amount < 1000) return ceil($amount / 50) * 50;  // 50 TRY increments
                return ceil($amount / 100) * 100;  // 100 TRY increments

            case 'day':
                if ($amount < 100) return ceil($amount / 10) * 10;  // 10 TRY increments
                if ($amount < 1000) return ceil($amount / 50) * 50;  // 50 TRY increments
                if ($amount < 10000) return ceil($amount / 100) * 100;  // 100 TRY increments
                return ceil($amount / 500) * 500;  // 500 TRY increments

            default:
                throw new InvalidArgumentException('Invalid period for short-term rounding');
        }
    }

    /**
     * Handles rounding for long-term savings (weeks, months, years)
     * These are typically for larger amounts where people use bank transfers
     */
    private function roundLongTerm(float $amount, string $period): float
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        switch ($period) {
            case 'week':
                if ($amount < 500) return ceil($amount / 50) * 50;  // 50 TRY increments
                if ($amount < 2000) return ceil($amount / 100) * 100;  // 100 TRY increments
                if ($amount < 10000) return ceil($amount / 500) * 500;  // 500 TRY increments
                return ceil($amount / 1000) * 1000;  // 1000 TRY increments

            case 'month':
                if ($amount < 1000) return ceil($amount / 100) * 100;  // 100 TRY increments
                if ($amount < 5000) return ceil($amount / 500) * 500;  // 500 TRY increments
                if ($amount < 50000) return ceil($amount / 1000) * 1000;  // 1000 TRY increments
                return ceil($amount / 5000) * 5000;  // 5000 TRY increments

            case 'year':
                if ($amount < 10000) return ceil($amount / 1000) * 1000;  // 1000 TRY increments
                if ($amount < 100000) return ceil($amount / 5000) * 5000;  // 5000 TRY increments
                if ($amount < 1000000) return ceil($amount / 10000) * 10000;  // 10000 TRY increments
                return ceil($amount / 50000) * 50000;  // 50000 TRY increments

            default:
                throw new InvalidArgumentException('Invalid period for long-term rounding');
        }
    }

    /**
     * Calculate savings frequency options with appropriate rounding based on time period
     */
    private function calculateFrequencyOption(int $timeDiff, string $period, Money $targetAmount): array
    {
        if ($timeDiff <= 0) {
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => "You need less than a $period to reach your saving goal."
            ];
        }

        // Determine if this is short-term or long-term saving
        $isShortTerm = in_array($period, self::SHORT_TERM_PERIODS);

        // Validate that the period is either short-term or long-term
        if (!$isShortTerm && !in_array($period, self::LONG_TERM_PERIODS)) {
            throw new InvalidArgumentException('Invalid period type provided');
        }

        // Get exact target amount
        $targetAmountFloat = $targetAmount->getAmount()->toFloat();

        // Calculate initial amount per period
        $exactAmount = $targetAmountFloat / $timeDiff;

// Define single payment thresholds for each period
        $singlePaymentThresholds = [
            'minute' => 10,    // If target is <= 10 TRY, consider single payment for minutes
            'hour' => 20,     // If target is <= 20 TRY, consider single payment for hours
            'day' => 50,     // If target is <= 50 TRY, consider single payment for days
            'week' => 200,    // If target is <= 200 TRY, consider single payment for weeks
            'month' => 500,  // If target is <= 500 TRY, consider single payment for months
            'year' => 5000    // If target is <= 5000 TRY, consider single payment for years
        ];

        // Right after getting the target amount
//        Log::info('Initial target amount float:', ['targetAmountFloat' => $targetAmountFloat]);

// Just before our single payment condition
//        Log::info('Checking single payment condition:', [
//            'timeDiff' => $timeDiff,
//            'period' => $period,
//            'threshold' => $singlePaymentThresholds[$period] ?? PHP_FLOAT_MAX
//        ]);

        // Check if we can and should achieve the target in one payment
        if ($timeDiff == 1 ||
            $targetAmountFloat <= ($singlePaymentThresholds[$period] ?? PHP_FLOAT_MAX)) {

            // Convert our target amount to a string with exactly 2 decimal places
            $amountStr = number_format($targetAmountFloat, 2, '.', '');

            // Split into whole and decimal parts
            list($whole, $decimal) = explode('.', $amountStr);

            // If we have any decimal part at all, round up to next whole number
            $roundedAmount = (float)$whole;
            if ($decimal > '00') {
                $roundedAmount = $roundedAmount + 1;
            }
        } else {
            // Round using appropriate strategy
            $roundedAmount = $isShortTerm
                ? $this->roundShortTerm($exactAmount, $period)
                : $this->roundLongTerm($exactAmount, $period);
        }

        // Initialize variables for calculation loop
        $foundValidAmount = false;
        $neededPeriods = $timeDiff;

        while (!$foundValidAmount) {
            // Calculate how many periods we need with current rounded amount
            $neededPeriods = ceil($targetAmountFloat / $roundedAmount);

            if ($neededPeriods <= $timeDiff) {
                // Calculate total savings
                $totalSavings = $roundedAmount * $neededPeriods;

                // Check if we've reached our target
                if ($totalSavings >= $targetAmountFloat) {
                    $foundValidAmount = true;
                } else {
                    // If we're still short, increase the amount using appropriate strategy
                    $roundedAmount = $isShortTerm
                        ? $this->roundShortTerm($roundedAmount + 1, $period)
                        : $this->roundLongTerm($roundedAmount + 100, $period);
                }
            } else {
                // If we need more periods than available, increase amount
                $exactAmount = $targetAmountFloat / $timeDiff;
                $roundedAmount = $isShortTerm
                    ? $this->roundShortTerm($exactAmount + 1, $period)
                    : $this->roundLongTerm($exactAmount + 100, $period);
                $neededPeriods = $timeDiff;
            }
        }

        // Convert final amount to Money object
        try {
            $roundedMoney = Money::of(
                number_format($roundedAmount, 2, '.', ''),
                $targetAmount->getCurrency()->getCurrencyCode(),
                null,
                RoundingMode::CEILING
            );
        } catch (NumberFormatException $e) {
            Log::error('Number format error in calculateFrequencyOption: ' . $e->getMessage());
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => 'There was an error with the number format. Please try again.'
            ];
        } catch (RoundingNecessaryException $e) {
            Log::error('Rounding error in calculateFrequencyOption: ' . $e->getMessage());
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => 'There was an error with number rounding. Please try again.'
            ];
        } catch (UnknownCurrencyException $e) {
            Log::error('Currency error in calculateFrequencyOption: ' . $e->getMessage());
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => 'The specified currency is not recognized. Please check the currency and try again.'
            ];
        }

        // Calculate final totals
        try {
            $totalSavings = $roundedMoney->multipliedBy($neededPeriods);
            $extraSavings = $totalSavings->minus($targetAmount);

            return [
                'amount' => [
                    'value' => $roundedMoney->getAmount()->__toString(),
                    'formatted_amount' => number_format($roundedAmount, 0),
                    'currency' => $roundedMoney->getCurrency()->getCurrencyCode()
                ],
                'frequency' => $neededPeriods,
                'message' => null,
                'extra_savings' => [
                    'value' => $extraSavings->getAmount()->__toString(),
                    'formatted_amount' => number_format((float)$extraSavings->getAmount()->__toString(), 0),
                    'currency' => $extraSavings->getCurrency()->getCurrencyCode()
                ]
            ];
        } catch (MathException|MoneyMismatchException $e) {
            Log::error('Error in calculateFrequencyOption during final calculations: ' . $e->getMessage());
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => 'There was an error calculating the savings amounts. This might happen if the currencies don\'t match or the numbers are too large. Please check your input values.'
            ];
        }


    }

    // Main function remains largely the same but handles time calculations more appropriately
    public function calculateAllFrequencyOptions(Money $price, ?Money $startingAmount, string $purchaseDate): array
    {
        try {
            $targetAmount = !$startingAmount ? $price : $price->minus($startingAmount);
        } catch (MathException|MoneyMismatchException $e) {
            \Log::error('Error calculating target amount: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'There was an issue calculating the target amount. Please try again.',
            ];
        }

        $today = Carbon::now();
        $purchaseDateTime = Carbon::parse($purchaseDate);

        // Calculate time differences - now separated by group for better handling
        return [
            // Short-term options
            'minutes' => $this->calculateFrequencyOption(
                (int)ceil($today->diffInMinutes($purchaseDateTime)),
                'minute',
                $targetAmount
            ),
            'hours' => $this->calculateFrequencyOption(
                (int)ceil($today->diffInHours($purchaseDateTime)),
                'hour',
                $targetAmount
            ),
            'days' => $this->calculateFrequencyOption(
                (int)ceil($today->diffInDays($purchaseDateTime)),
                'day',
                $targetAmount
            ),

            // Long-term options
            'weeks' => $this->calculateFrequencyOption(
                (int)ceil($today->diffInDays($purchaseDateTime) / 7),
                'week',
                $targetAmount
            ),
            'months' => $this->calculateFrequencyOption(
                (int)ceil($today->diffInMonths($purchaseDateTime)),
                'month',
                $targetAmount
            ),
            'years' => $this->calculateFrequencyOption(
                (int)ceil($today->diffInYears($purchaseDateTime)),
                'year',
                $targetAmount
            )
        ];
    }
}
