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
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PickDateCalculationService
{
    // Constants to help us identify which group a time period belongs to
    private const SHORT_TERM_PERIODS = ['hour', 'day'];
    private const LONG_TERM_PERIODS = ['week', 'month', 'year'];


    /**
     * Calculate savings frequency options with appropriate rounding based on time period
     *
     * @param int $timeDiff Number of periods until target date
     * @param string $period Type of period (hour, day, week, month, year)
     * @param Money $targetAmount Total amount needed to save
     * @return array Calculation results with amounts and frequency
     */
    private function calculateFrequencyOption(int $timeDiff, string $period, Money $targetAmount): array
    {
        // If we have less than one period, we can't create a saving plan
        if ($timeDiff <= 0) {
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => "You need less than a $period to reach your saving goal."
            ];
        }

        // Determine if this is short-term or long-term saving
        $isShortTerm = in_array($period, self::SHORT_TERM_PERIODS);

        // Validate period type
        if (!$isShortTerm && !in_array($period, self::LONG_TERM_PERIODS)) {
            throw new InvalidArgumentException('Invalid period type provided');
        }

        // Define single payment thresholds as Money objects for proper comparison
        $thresholds = [
            'hour' => Money::of(20, $targetAmount->getCurrency()->getCurrencyCode()),
            'day' => Money::of(50, $targetAmount->getCurrency()->getCurrencyCode()),
            'week' => Money::of(200, $targetAmount->getCurrency()->getCurrencyCode()),
            'month' => Money::of(500, $targetAmount->getCurrency()->getCurrencyCode()),
            'year' => Money::of(5000, $targetAmount->getCurrency()->getCurrencyCode())
        ];

        // Check if we should use single payment
        $threshold = $thresholds[$period] ?? Money::of(PHP_FLOAT_MAX, $targetAmount->getCurrency()->getCurrencyCode());
        $useSinglePayment = $timeDiff === 1 || $targetAmount->isLessThanOrEqualTo($threshold);

        try {
            if ($useSinglePayment) {
                // For single payments, we just use the target amount directly
                $roundedAmount = $targetAmount;
                $neededPeriods = 1;
            } else {
                // Add this logging before the division
                Log::info('About to perform division:', [
                    'targetAmount' => [
                        'value' => $targetAmount->getAmount()->__toString(),
                        'currency' => $targetAmount->getCurrency()->getCurrencyCode()
                    ],
                    'timeDiff' => $timeDiff,
                    'period' => $period
                ]);

                try {
                    // Calculate initial amount per period using Money division
                    // We use exact scale to avoid rounding issues in intermediate calculations
                    $initialAmount = $targetAmount->dividedBy($timeDiff, RoundingMode::UP);

                    Log::info('Division successful:', [
                        'result' => [
                            'value' => $initialAmount->getAmount()->__toString(),
                            'currency' => $initialAmount->getCurrency()->getCurrencyCode()
                        ]
                    ]);
                } catch (Exception $e) {
                    Log::error('Division failed:', [
                        'error' => $e->getMessage(),
                        'error_type' => get_class($e)
                    ]);
                    throw $e;
                }

                // When converting to base units, we need to multiply by 100 since TRY has 2 decimal places
                // This ensures we're working with whole numbers (cents) instead of decimals
                $baseAmount = (int)($initialAmount->getAmount()->toFloat() * 100);

                // Apply rounding rules based on period type
                if ($isShortTerm) {
                    $roundedBase = $this->roundShortTermBase($baseAmount, $period);
                } else {
                    $roundedBase = $this->roundLongTermBase($baseAmount, $period);
                }

                // Add this right after we calculate roundedBase
                Log::info('After base rounding:', [
                    'initial_base' => $baseAmount,
                    'rounded_base' => $roundedBase,
                    'period' => $period
                ]);

                try {
                    $roundedAmount = Money::ofMinor($roundedBase, $targetAmount->getCurrency()->getCurrencyCode(), null, RoundingMode::CEILING);
                } catch (RoundingNecessaryException $e) {
                    // If rounding is still needed, force ceiling rounding
                    $roundedAmount = Money::ofMinor($roundedBase, $targetAmount->getCurrency()->getCurrencyCode(), null, RoundingMode::CEILING);
                }

                // Add this after converting back to Money object
                Log::info('After converting back to Money:', [
                    'rounded_amount' => $roundedAmount->getAmount()->__toString(),
                    'currency' => $roundedAmount->getCurrency()->getCurrencyCode()
                ]);

                // Calculate how many periods we need
                $neededPeriods = $this->calculateNeededPeriods($targetAmount, $roundedAmount);

                // Ensure we don't exceed available time
                if ($neededPeriods > $timeDiff) {
                    $neededPeriods = $timeDiff;
                    // Recalculate amount needed per period
                    $roundedAmount = $targetAmount->dividedBy($timeDiff, RoundingMode::CEILING);
                }
            }

            // Calculate final totals using Money arithmetic
            $totalSavings = $roundedAmount->multipliedBy($neededPeriods);
            $extraSavings = $totalSavings->minus($targetAmount);

            return [
                'amount' => [
                    'amount' => $roundedAmount,
                    'formatted_value' => $roundedAmount->formatTo(App::getLocale())
                ],
                'frequency' => $neededPeriods,
                'message' => null,
                'extra_savings' => [
                    'amount' => $extraSavings,
                    'formatted_value' => $extraSavings->formatTo(App::getLocale())
                ],
                'total_savings' => [
                    'amount' => $totalSavings,
                    'formatted_value' => $totalSavings->formatTo(App::getLocale())
                ],
                'target_amount' => [
                    'amount' => $targetAmount,
                    'formatted_value' => $targetAmount->formatTo(App::getLocale())
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error in calculateFrequencyOption: ' . $e->getMessage());
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => 'There was an error calculating the savings amounts. Please check your input values.'
            ];
        }
    }

    /**
     * Round amount base units for short-term periods
     * Works with integer amounts (e.g., cents) to avoid floating-point issues
     */
    private function roundShortTermBase(int $amount, string $period): int
    {
        switch ($period) {
            case 'hour':
                if ($amount < 5000) return (int)ceil($amount / 500) * 500;  // 5 TRY increments
                if ($amount < 20000) return (int)ceil($amount / 1000) * 1000;  // 10 TRY increments
                if ($amount < 100000) return (int)ceil($amount / 5000) * 5000;  // 50 TRY increments
                return (int)ceil($amount / 10000) * 10000;  // 100 TRY increments

            case 'day':
                if ($amount < 10000) return (int)ceil($amount / 1000) * 1000;  // 10 TRY increments
                if ($amount < 100000) return (int)ceil($amount / 5000) * 5000;  // 50 TRY increments
                if ($amount < 1000000) return (int)ceil($amount / 10000) * 10000;  // 100 TRY increments
                return (int)ceil($amount / 50000) * 50000;  // 500 TRY increments

            default:
                throw new InvalidArgumentException('Invalid period for short-term rounding');
        }
    }

    /**
     * Round amount base units for long-term periods
     * Works with integer amounts (e.g., cents) to avoid floating-point issues
     */
    private function roundLongTermBase(int $amount, string $period): int
    {
        Log::info('Starting roundLongTermBase:', [
            'amount' => $amount,
            'period' => $period
        ]);

        switch ($period) {
            case 'week':
                if ($amount < 50000) return (int)ceil($amount / 5000) * 5000;  // 50 TRY increments
                if ($amount < 200000) return (int)ceil($amount / 10000) * 10000;  // 100 TRY increments
                if ($amount < 1000000) return (int)ceil($amount / 50000) * 50000;  // 500 TRY increments
                return (int)ceil($amount / 100000) * 100000;  // 1000 TRY increments

            case 'month':
                if ($amount < 100000) return (int)ceil($amount / 5000) * 5000;    // 50 TRY increments
                if ($amount < 200000) return (int)ceil($amount / 10000) * 10000;  // 100 TRY increments
                if ($amount < 500000) return (int)ceil($amount / 25000) * 25000;  // 250 TRY increments
                if ($amount < 1000000) return (int)ceil($amount / 50000) * 50000; // 500 TRY increments
                return (int)ceil($amount / 100000) * 100000;                      // 1000 TRY increments

            case 'year':
                if ($amount < 500000) return (int)ceil($amount / 25000) * 25000;      // 250 TRY increments
                if ($amount < 1500000) return (int)ceil($amount / 50000) * 50000;     // 500 TRY increments
                if ($amount < 3000000) return (int)ceil($amount / 100000) * 100000;   // 1000 TRY increments
                if ($amount < 5000000) return (int)ceil($amount / 250000) * 250000;   // 2500 TRY increments
                if ($amount < 10000000) return (int)ceil($amount / 500000) * 500000;  // 5000 TRY increments
                return (int)ceil($amount / 1000000) * 1000000;                        // 10000 TRY increments

            default:
                throw new InvalidArgumentException('Invalid period for long-term rounding');
        }
    }

    /**
     * Calculate how many periods are needed to reach target amount
     */
    private function calculateNeededPeriods(Money $targetAmount, Money $roundedAmount): int
    {
        return (int)ceil($targetAmount->getAmount()->toInt() / $roundedAmount->getAmount()->toInt());
    }


    public function calculateAllFrequencyOptions(Money $price, ?Money $startingAmount, string $purchaseDate): array
    {
        try {
            // Validate purchase date first
            $purchaseDateTime = Carbon::parse($purchaseDate);
            if ($purchaseDateTime->isPast()) {
                return [
                    'success' => false,
                    'error' => 'Purchase date cannot be in the past.',
                ];
            }

            // Validate starting amount against price
            if ($startingAmount && $startingAmount->isGreaterThan($price)) {
                return [
                    'success' => false,
                    'error' => 'Starting amount cannot be greater than the price.',
                ];
            }

            // Calculate target amount
            $targetAmount = !$startingAmount ? $price : $price->minus($startingAmount);

            $today = Carbon::now();

            // Calculate all frequency options
            return [
                // Short-term options
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
                    (int)ceil(Carbon::tomorrow()->startOfDay()->diffInDays($purchaseDateTime->endOfDay()) / 7),
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

        } catch (MathException|MoneyMismatchException $e) {
            Log::error('Error calculating target amount: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'There was an issue calculating the target amount. Please check currency compatibility.',
            ];
        } catch (InvalidArgumentException $e) {
            Log::error('Invalid argument provided: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Invalid input provided. Please check your values.',
            ];
        } catch (Exception $e) {
            Log::error('Unexpected error in calculateAllFrequencyOptions: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again.',
            ];
        }
    }




}
