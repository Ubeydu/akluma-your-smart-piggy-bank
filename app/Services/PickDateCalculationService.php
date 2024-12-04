<?php

namespace App\Services;

use Brick\Math\Exception\MathException;
use Brick\Money\Exception\MoneyMismatchException;
use Carbon\Carbon;
use Brick\Money\Money;

class PickDateCalculationService
{
    public function calculateAllFrequencyOptions(Money $price, ?Money $startingAmount, string $purchaseDate): array
    {
        try {
            $targetAmount = !$startingAmount ? $price : $price->minus($startingAmount);
        } catch (MathException|MoneyMismatchException $e) {

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
                'amount' => $diffInMinutes > 0 ? $targetAmount->dividedBy($diffInMinutes, 4) : null,
                'frequency' => $diffInMinutes
            ],
            'hours' => [
                'amount' => $diffInHours > 0 ? $targetAmount->dividedBy($diffInHours, 4) : null,
                'frequency' => $diffInHours
            ],
            'days' => [
                'amount' => $diffInDays > 0 ? $targetAmount->dividedBy($diffInDays, 4) : null,
                'frequency' => $diffInDays
            ],
            'weeks' => [
                'amount' => $diffInWeeks > 0 ? $targetAmount->dividedBy($diffInWeeks, 4) : null,
                'frequency' => $diffInWeeks
            ],
            'months' => [
                'amount' => $diffInMonths > 0 ? $targetAmount->dividedBy($diffInMonths, 4) : null,
                'frequency' => $diffInMonths
            ],
            'years' => [
                'amount' => $diffInYears > 0 ? $targetAmount->dividedBy($diffInYears, 4) : null,
                'frequency' => $diffInYears
            ]
        ];
    }
}
