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
            $targetAmount = !$startingAmount ? $price : $price->minus($startingAmount);
        } catch (MathException|MoneyMismatchException $e) {
            \Log::error('Error calculating target amount: ' . $e->getMessage());
            // Return a fallback array with an error indicator or default values
            return [
                'success' => false,
                'error' => 'There was an issue calculating the target amount. Please try again.',
            ];
        }
        $today = Carbon::now();
        $purchaseDateTime = Carbon::parse($purchaseDate);

        $diffInMinutes = $today->diffInMinutes($purchaseDateTime);
        $diffInHours = $today->diffInHours($purchaseDateTime);
        $diffInDays = $today->diffInDays($purchaseDateTime);
        $diffInWeeks = ceil($today->diffInDays($purchaseDateTime) / 7);
        $diffInMonths = $today->diffInMonths($purchaseDateTime);
        $diffInYears = $today->diffInYears($purchaseDateTime);

        return [
            'minutes' => [
                'amount' => $diffInMinutes > 0 ? $targetAmount->dividedBy($diffInMinutes, RoundingMode::UP) : null,
                'frequency' => (int)$diffInMinutes  // Cast to integer
            ],
            'hours' => [
                'amount' => $diffInHours > 0 ? $targetAmount->dividedBy($diffInHours, RoundingMode::UP) : null,
                'frequency' => (int)$diffInHours    // Cast to integer
            ],
            'days' => [
                'amount' => $diffInDays > 0 ? $targetAmount->dividedBy($diffInDays, RoundingMode::UP) : null,
                'frequency' => (int)$diffInDays     // Cast to integer
            ],
            'weeks' => [
                'amount' => $diffInWeeks > 0 ? $targetAmount->dividedBy($diffInWeeks, RoundingMode::UP) : null,
                'frequency' => (int)$diffInWeeks    // Cast to integer
            ],
            'months' => [
                'amount' => $diffInMonths > 0 ? $targetAmount->dividedBy($diffInMonths, RoundingMode::UP) : null,
                'frequency' => (int)$diffInMonths   // Cast to integer
            ],
            'years' => [
                'amount' => $diffInYears > 0 ? $targetAmount->dividedBy($diffInYears, RoundingMode::UP) : null,
                'frequency' => (int)$diffInYears    // Cast to integer
            ]
        ];
    }
}
