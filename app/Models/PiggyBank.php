<?php

namespace App\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Log;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int $user_id
 * @property string $name
 * @property float $price
 * @property float|null $starting_amount
 * @property float|null $current_balance
 * @property float $target_amount
 * @property float|null $extra_savings
 * @property float $total_savings
 * @property string|null $link
 * @property string|null $details
 * @property string $chosen_strategy
 * @property string $selected_frequency
 * @property string $preview_image
 * @property string $currency
 * @property string $status
 * @property string|null $preview_title
 * @property string|null $preview_description
 * @property string|null $preview_url
 * @property-read float $final_total
 * @property-read float $remaining_amount
 */
class PiggyBank extends Model
{
    use HasFactory;

    public const STATUS_OPTIONS = ['active', 'paused', 'done', 'cancelled'];

    public const MAX_ACTIVE_PIGGY_BANKS = 10;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'starting_amount',
        'current_balance',
        'target_amount',
        'extra_savings',
        'total_savings',
        'link',
        'details',
        'chosen_strategy',
        'selected_frequency',
        'preview_image',
        'currency',
        'status',
        'preview_title',
        'preview_description',
        'preview_url',
    ];

    protected $attributes = [
        'preview_image' => 'images/piggy_banks/default_piggy_bank.png',
        'currency' => 'TRY',
        'status' => 'active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduledSavings(): HasMany
    {
        return $this->hasMany(ScheduledSaving::class);
    }

    private $remainingAmountOverride = null;

    public function setRemainingAmountOverride(callable $override)
    {
        $this->remainingAmountOverride = $override;
    }

    /**
     * Eloquent accessor for the "final_total" attribute.
     *
     * ⚠️ This method is called automatically by Laravel/Eloquent
     * when you access $piggyBank->final_total in your code or Blade views.
     *
     * Even if PhpStorm says "not used", it *is* used via magic property access.
     * Usage example: $piggyBank->final_total in Blade or PHP.
     * @noinspection PhpUnused
     */
    public function getFinalTotalAttribute(): float
    {
        \Log::info('getFinalTotalAttribute called', ['piggy_bank_id' => $this->id]);
        try {
            return ($this->total_savings ?? 0) + ($this->starting_amount ?? 0);
        } catch (\Throwable $e) {
            \Log::error('Error calculating final total', [
                'piggy_bank_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    /**
     * Eloquent accessor for the "remaining_amount" attribute.
     *
     * ⚠️ This method is called automatically by Laravel/Eloquent
     * when you access $piggyBank->remaining_amount.
     *
     * PhpStorm may not detect usage, but this IS used!
     * @noinspection PhpUnused
     * @return float
     */
    public function getRemainingAmountAttribute(): float
    {
        \Log::info('getRemainingAmountAttribute called', ['piggy_bank_id' => $this->id]);
        if ($this->remainingAmountOverride) {
            try {
                // Since remainingAmountOverride previously returned a Money object,
                // we need to get its amount as a float
                $overrideResult = call_user_func($this->remainingAmountOverride);
                if ($overrideResult instanceof Money) {
                    return $overrideResult->getAmount()->toFloat();
                }

                return (float) $overrideResult;
            } catch (\Throwable $e) {
                Log::error('Error in remaining amount override calculation', [
                    'piggy_bank_id' => $this->id,
                    'error' => $e->getMessage(),
                ]);

                return 0.0;
            }
        }

        // Original implementation simplified to work with raw values
        try {

            //            \Log::info('Calculating remaining amount', [
            //                'final_total' => $this->final_total,
            //                'current_balance' => $this->current_balance,
            //                'total_savings' => $this->total_savings,
            //                'starting_amount' => $this->starting_amount
            //            ]);

            return $this->final_total - ($this->current_balance ?? 0);
        } catch (\Throwable $e) {
            Log::error('Invalid money calculation in piggy bank', [
                'piggy_bank_id' => $this->id,
                'total_savings' => $this->total_savings,
                'current_balance' => $this->current_balance,
                'currency' => $this->currency,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    public static function getStatusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }
}
