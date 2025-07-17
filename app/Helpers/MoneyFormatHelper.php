<?php

namespace App\Helpers;

use Brick\Money\Context\CustomContext;
use Brick\Money\Money;

class MoneyFormatHelper
{
    public static function format(?float $amount, string $currency): string
    {
        // If amount is null, default to 0
        $amount = $amount ?? 0.0;

        $currencyConfig = config('app.currencies.'.$currency);
        $decimalPlaces = $currencyConfig['decimal_places'] ?? 2;

        $context = new CustomContext($decimalPlaces);

        try {
            return Money::of($amount, $currency, $context)
                ->formatTo(app()->getLocale());
        } catch (\Throwable $e) {
            \Log::error('Money formatting error', [
                'amount' => $amount,
                'currency' => $currency,
                'decimal_places' => $decimalPlaces,
                'error' => $e->getMessage(),
            ]);

            return $currency.' '.number_format($amount, $decimalPlaces);
        }
    }
}
