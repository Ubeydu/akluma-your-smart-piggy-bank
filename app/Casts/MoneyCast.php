<?php

namespace App\Casts;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return Money
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Money
    {
        $currencyCode = $attributes['currency'] ?? 'TRY';

        if ($value === null) {
            throw new InvalidArgumentException('Null value cannot be cast to Money.');
        }

        try {
            return Money::ofMinor($value, $currencyCode);
        } catch (UnknownCurrencyException $e) {
            throw new InvalidArgumentException("Invalid currency code: $currencyCode", 0, $e);
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     * @throws InvalidArgumentException|MathException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $currencyCode = $attributes['currency'] ?? 'TRY';

        if ($value === null) {
            throw new InvalidArgumentException('Money value cannot be null.');
        }

        if ($value instanceof Money) {
            $minorAmount = $value->getMinorAmount()->toInt();
            $currencyCode = $value->getCurrency()->getCurrencyCode();
        } elseif (is_numeric($value)) {
            try {
                $minorAmount = BigDecimal::of($value)
                    ->multipliedBy(100)
                    ->toScale(0, RoundingMode::HALF_UP)
                    ->toInt();
            } catch (MathException $e) {
                throw new InvalidArgumentException("Error converting numeric value to Money: $value", 0, $e);
            }
        } else {
            throw new InvalidArgumentException('Money value must be a numeric or Money instance: ' . var_export($value, true));
        }

        return [
            'amount' => $minorAmount,
            'currency' => $currencyCode,
        ];
    }
}
