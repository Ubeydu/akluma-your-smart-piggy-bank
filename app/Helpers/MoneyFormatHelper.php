<?php

namespace App\Helpers;

use Brick\Money\Money;

class MoneyFormatHelper
{
    public static function format($amount, $currency)
    {
        return Money::of($amount, $currency);
    }
}
