<?php

namespace App\Services;

use Brick\Math\Exception\MathException;
use Brick\Money\Exception\MoneyMismatchException;
use Carbon\Carbon;
use Brick\Money\Money;
use Brick\Math\RoundingMode;

class PickDateCalculationService
{
    public function calculateAllFrequencyOptions(Money $price, ?Money $startingAmount, string $purchaseDate): array
    {
        try {
            // Calculate the target amount, considering any starting amount
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

        // Calculate time differences and round up to ensure we always give enough time
        // We use ceil() because we want to round up partial periods to the next whole number
        $diffInMinutes = (int)ceil($today->diffInMinutes($purchaseDateTime));
        $diffInHours = (int)ceil($today->diffInHours($purchaseDateTime));
        $diffInDays = (int)ceil($today->diffInDays($purchaseDateTime));
        $diffInWeeks = (int)ceil($today->diffInDays($purchaseDateTime) / 7);
        $diffInMonths = (int)ceil($today->diffInMonths($purchaseDateTime));
        $diffInYears = (int)ceil($today->diffInYears($purchaseDateTime));

        // Create a helper function to handle both the frequency calculation and zero-value cases
        $calculateFrequencyOption = function(int $timeDiff, string $period) use ($targetAmount) {
            if ($timeDiff <= 0) {
                return [
                    'amount' => null,
                    'frequency' => 0,
                    'message' => "You need less than a $period to reach your saving goal."
                ];
            }

            return [
                'amount' => $targetAmount->dividedBy($timeDiff, RoundingMode::CEILING),
                'frequency' => $timeDiff,
                'message' => null
            ];
        };

        // Return the calculated options for each time period
        return [
            'minutes' => $calculateFrequencyOption($diffInMinutes, 'minute'),
            'hours' => $calculateFrequencyOption($diffInHours, 'hour'),
            'days' => $calculateFrequencyOption($diffInDays, 'day'),
            'weeks' => $calculateFrequencyOption($diffInWeeks, 'week'),
            'months' => $calculateFrequencyOption($diffInMonths, 'month'),
            'years' => $calculateFrequencyOption($diffInYears, 'year')
        ];
    }
}
