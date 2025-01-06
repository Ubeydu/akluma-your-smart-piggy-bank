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

    private function logCalculationDetails($calculationResult): array {
        return [
            'amount' => $calculationResult['amount'] ?? null,
            'frequency' => $calculationResult['frequency'] ?? null,
            'message' => $calculationResult['message'] ?? null
        ];
    }


    // Constants to help us identify which group a time period belongs to
    private const SHORT_TERM_PERIODS = ['day', 'week'];
    private const LONG_TERM_PERIODS = ['month', 'year'];


    /**
     * Calculate savings frequency options with appropriate rounding based on time period
     *
     * @param  int  $timeDiff  Number of periods until target date
     * @param  string  $period  Type of period (day, week, month, year)
     * @param  Money  $targetAmount  Total amount needed to save
     * @return array Calculation results with amounts and frequency
     * @throws MathException
     * @throws MoneyMismatchException
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     */
    private function calculateFrequencyOption(int $timeDiff, string $period, Money $targetAmount): array
    {
//        \Log::debug('Calculating Frequency Option', [
//            'time_diff' => $timeDiff,
//            'period' => $period,
//            'target_amount' => [
//                'amount' => $targetAmount->getAmount()->__toString(),
//                'currency' => $targetAmount->getCurrency()->getCurrencyCode()
//            ]
//        ]);


        // If we have less than one period, we can't create a saving plan
        if ($timeDiff <= 0) {
            return [
                'amount' => null,
                'frequency' => 0,
                'message' => "You need less than a $period to reach your saving goal."
            ];
        }

//        Log::info('Period received:', ['period' => $period]);

        // Determine if this is short-term or long-term saving
        $isShortTerm = in_array($period, self::SHORT_TERM_PERIODS);

        // Validate period type
        if (!$isShortTerm && !in_array($period, self::LONG_TERM_PERIODS)) {
            throw new InvalidArgumentException('Invalid period type provided');
        }

        // Define single payment thresholds as Money objects for proper comparison
        $thresholds = [
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
//                Log::info('About to perform division:', [
//                    'targetAmount' => [
//                        'value' => $targetAmount->getAmount()->__toString(),
//                        'currency' => $targetAmount->getCurrency()->getCurrencyCode()
//                    ],
//                    'timeDiff' => $timeDiff,
//                    'period' => $period
//                ]);

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
                    \Log::error('Division Failed in Frequency Calculation', [
                        'target_amount' => $targetAmount->getAmount()->__toString(),
                        'time_diff' => $timeDiff,
                        'error_message' => $e->getMessage(),
                        'error_class' => get_class($e)
                    ]);
                    return [
                        'amount' => null,
                        'frequency' => 0,
                        'message' => 'Error calculating savings frequency.'
                    ];
                }


                \Log::info('Initial amount object:', ['initialAmount' => $initialAmount]);
                \Log::info('Amount as float:', ['float_value' => $initialAmount->getAmount()->toFloat()]);


//                // When converting to base units, we need to multiply by 100 since TRY has 2 decimal places
//                // This ensures we're working with whole numbers (cents) instead of decimals
//                $baseAmount = (int)($initialAmount->getAmount()->toFloat() * 100);


                // Get currency's decimal places and calculate multiplier
                $decimalPlaces = $initialAmount->getCurrency()->getDefaultFractionDigits();
                $multiplier = 10 ** $decimalPlaces;

                // Convert to base units using the correct multiplier
                $baseAmount = (int)($initialAmount->getAmount()->toFloat() * $multiplier);


                \Log::info('Final baseAmount:', ['baseAmount' => $baseAmount]);


                // Apply rounding rules based on period type
                if ($isShortTerm) {
                    $roundedBase = $this->roundShortTermBase($baseAmount, $period);
                } else {
                    $roundedBase = $this->roundLongTermBase($baseAmount, $period);
                }

                // Add this right after we calculate roundedBase
//                Log::info('After base rounding:', [
//                    'initial_base' => $baseAmount,
//                    'rounded_base' => $roundedBase,
//                    'period' => $period
//                ]);

                try {
                    $roundedAmount = Money::ofMinor($roundedBase, $targetAmount->getCurrency()->getCurrencyCode(), null, RoundingMode::CEILING);
                } catch (RoundingNecessaryException $e) {
                    // If rounding is still needed, force ceiling rounding
                    $roundedAmount = Money::ofMinor($roundedBase, $targetAmount->getCurrency()->getCurrencyCode(), null, RoundingMode::CEILING);
                }

                // Add this after converting back to Money object
//                Log::info('After converting back to Money:', [
//                    'rounded_amount' => $roundedAmount->getAmount()->__toString(),
//                    'currency' => $roundedAmount->getCurrency()->getCurrencyCode()
//                ]);

                // Calculate how many periods we need
                $neededPeriods = $this->calculateNeededPeriods($targetAmount, $roundedAmount);

                // Ensure we don't exceed available time
                if ($neededPeriods > $timeDiff) {
                    $neededPeriods = $timeDiff;
                    // Recalculate amount needed per period
                    $roundedAmount = $targetAmount->dividedBy($timeDiff, RoundingMode::CEILING);
                }
            }


//            Log::info('About to calculate total savings:', [
//                'rounded_amount_value' => $roundedAmount->getAmount()->__toString(),
//                'rounded_amount_scale' => $roundedAmount->getAmount()->getScale(),
//                'needed_periods' => $neededPeriods
//            ]);


            // Calculate final totals using Money arithmetic
            $totalSavings = $roundedAmount->multipliedBy($neededPeriods);

//            Log::info('Total savings calculated:', [
//                'total_savings' => $totalSavings->getAmount()->__toString(),
//                'needed_periods' => $neededPeriods,
//                'per_period' => $roundedAmount->getAmount()->__toString()
//            ]);

            $extraSavings = $totalSavings->minus($targetAmount);

//            Log::info('Extra savings calculated:', [
//                'extra_savings' => $extraSavings->getAmount()->__toString(),
//                'total_collected' => $totalSavings->getAmount()->__toString(),
//                'target_was' => $targetAmount->getAmount()->__toString()
//            ]);


//            \Log::debug('Frequency Option Calculation Result', [
//                'period' => $period,
//                'calculated_amount' => $roundedAmount ? $roundedAmount->getAmount()->__toString() : 'null',
//                'needed_periods' => $neededPeriods ?? 'null',
//                'is_amount_null' => $roundedAmount === null
//            ]);



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
//        \Log::debug('Rounding Short Term Base', [
//            'original_amount' => $amount,
//            'period' => $period,
//            'amount_in_currency' => $amount / 100
//        ]);


        // Add at the start of roundShortTermBase method
//        Log::debug('Short term rounding input:', [
//            'original_amount' => $amount,
//            'period' => $period,
//            'amount_decimal' => $amount / 100 // Show in currency units
//        ]);

        switch ($period) {

            case 'day':
                if ($amount < 10000) return (int)ceil($amount / 1000) * 1000;  // 10 TRY increments
                if ($amount < 100000) return (int)ceil($amount / 5000) * 5000;  // 50 TRY increments
                if ($amount < 1000000) return (int)ceil($amount / 10000) * 10000;  // 100 TRY increments
                return (int)ceil($amount / 50000) * 50000;  // 500 TRY increments


            case 'week':
                // Up to 5000 TRY (500,000 kuruş)
                if ($amount < 500000) return (int)ceil($amount / 2500) * 2500;      // 25 TRY increments

                // From 5000 TRY to 10000 TRY (500,000 to 1,000,000 kuruş)
                if ($amount < 1000000) return (int)ceil($amount / 5000) * 5000;     // 50 TRY increments

                // From 10000 TRY to 50000 TRY (1,000,000 to 5,000,000 kuruş)
                if ($amount < 5000000) return (int)ceil($amount / 10000) * 10000;   // 100 TRY increments

                // From 50000 TRY to 100000 TRY (5,000,000 to 10,000,000 kuruş)
                if ($amount < 10000000) return (int)ceil($amount / 25000) * 25000;  // 250 TRY increments

                // Above 100000 TRY (10,000,000 kuruş)
                return (int)ceil($amount / 50000) * 50000;                          // 500 TRY increments

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
//        \Log::debug('Rounding Long Term Base', [
//            'original_amount' => $amount,
//            'period' => $period,
//            'amount_in_currency' => $amount / 100
//        ]);


//        Log::info('Starting roundLongTermBase with detailed logging:', [
//            'input_amount' => $amount,
//            'input_amount_as_try' => $amount / 100,
//            'period' => $period
//        ]);

        switch ($period) {

            case 'month':
                if ($amount < 100000) return (int)ceil($amount / 2500) * 2500;    // 25 TRY increments
                if ($amount < 1000000) return (int)ceil($amount / 10000) * 10000;  // 100 TRY increments
                if ($amount < 10000000) return (int)ceil($amount / 50000) * 50000; // 500 TRY increments
                return (int)ceil($amount / 100000) * 100000;                     // 1000 TRY increments

            case 'year':
                if ($amount < 500000) return (int)ceil($amount / 25000) * 25000;      // 250 TRY increments
                if ($amount < 1500000) return (int)ceil($amount / 50000) * 50000;     // 500 TRY increments
                if ($amount < 10000000) return (int)ceil($amount / 100000) * 100000;   // 1000 TRY increments
                return (int)ceil($amount / 500000) * 500000;                        // 5000 TRY increments

            default:
                throw new InvalidArgumentException('Invalid period for long-term rounding');
        }
    }

    /**
     * Calculate how many periods are needed to reach target amount
     * @throws MathException
     */
    private function calculateNeededPeriods(Money $targetAmount, Money $roundedAmount): int
    {

        // Add this logging
//        Log::debug('Calculating periods with raw values:', [
//            'target_base' => $targetAmount->getMinorAmount()->toInt(),
//            'rounded_base' => $roundedAmount->getMinorAmount()->toInt()
//        ]);

        // Use minor amounts (cents) for the calculation to avoid rounding issues
        $targetMinor = $targetAmount->getMinorAmount()->toInt();
        $roundedMinor = $roundedAmount->getMinorAmount()->toInt();

        return (int)ceil($targetMinor / $roundedMinor);
    }







    public function calculateAllFrequencyOptions(Money $price, ?Money $startingAmount, string $purchaseDate): array
    {
//        \Log::info('Calculation Started', [
//            'price' => [
//                'amount' => $price->getAmount()->__toString(),
//                'currency' => $price->getCurrency()->getCurrencyCode()
//            ],
//            'starting_amount' => $startingAmount ? [
//                'amount' => $startingAmount->getAmount()->__toString(),
//                'currency' => $startingAmount->getCurrency()->getCurrencyCode()
//            ] : null,
//            'purchase_date' => $purchaseDate
//        ]);




        try {

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


//            \Log::debug('Before Frequency Calculations', [
//                'target_amount' => [
//                    'amount' => $targetAmount->getAmount()->__toString(),
//                    'currency' => $targetAmount->getCurrency()->getCurrencyCode()
//                ],
//                'time_diff_days' => (int)ceil($today->diffInDays($purchaseDateTime)),
//                'time_diff_weeks' => (int)ceil(Carbon::tomorrow()->startOfDay()->diffInDays($purchaseDateTime->endOfDay()) / 7),
//                'time_diff_months' => (int)ceil($today->diffInMonths($purchaseDateTime)),
//                'time_diff_years' => (int)ceil($today->diffInYears($purchaseDateTime))
//            ]);


//            // Calculate all frequency options
//            return [
//                // Short-term options - start counting from current time since immediate action is possible
//                'days' => $this->calculateFrequencyOption(
//                    (int)ceil($today->diffInDays($purchaseDateTime)),
//                    'day',
//                    $targetAmount
//                ),
//
//                'weeks' => $this->calculateFrequencyOption(
//                    // Weekly savings require special handling for better user experience:
//                    // 1. Start from tomorrow morning (00:00:00) to give full weeks
//                    // 2. Count until end of purchase date (23:59:59)
//                    // This aligns with typical weekly budget/salary cycles and
//                    // ensures users get complete weeks for their saving plan
//                    (int)ceil(Carbon::tomorrow()->startOfDay()->diffInDays($purchaseDateTime->endOfDay()) / 7),
//                    'week',
//                    $targetAmount
//                ),
//                // Long-term options
//
//                // Monthly/Yearly calculations can handle partial periods since they're
//                // longer timeframes and users can adjust their saving amounts accordingly
//                'months' => $this->calculateFrequencyOption(
//                    (int)ceil($today->diffInMonths($purchaseDateTime)),
//                    'month',
//                    $targetAmount
//                ),
//                'years' => $this->calculateFrequencyOption(
//                    (int)ceil($today->diffInYears($purchaseDateTime)),
//                    'year',
//                    $targetAmount
//                )
//            ];




            $calculations = [
                'days' => $this->calculateFrequencyOption(
                    (int)ceil($today->diffInDays($purchaseDateTime)),
                    'day',
                    $targetAmount
                ),
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

//            \Log::debug('Frequency Calculations Final Result', [
//                'days' => $this->logCalculationDetails($calculations['days']),
//                'weeks' => $this->logCalculationDetails($calculations['weeks']),
//                'months' => $this->logCalculationDetails($calculations['months']),
//                'years' => $this->logCalculationDetails($calculations['years'])
//            ]);

            return $calculations;


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
