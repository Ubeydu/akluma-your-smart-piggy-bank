<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function hasDecimalPlaces($currencyCode): bool
    {
        return config("app.currencies.$currencyCode.decimal_places", 2) > 0;
    }
}
