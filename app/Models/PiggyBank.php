<?php

namespace App\Models;

use Brick\Math\Exception\MathException;
use Brick\Money\Exception\MoneyException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 */
class PiggyBank extends Model
{
    use HasFactory;

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
        'preview_url'
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

    /**
     * Get the remaining amount to save
     *
     * @throws MoneyException If money calculation fails
     */
    public function getRemainingAmountAttribute(): Money
    {
        try {
            return Money::of($this->total_savings, $this->currency)
                ->minus(Money::of($this->current_balance ?? 0, $this->currency));
        } catch (MathException $e) {
            Log::error('Invalid money calculation in piggy bank', [
                'piggy_bank_id' => $this->id,
                'total_savings' => $this->total_savings,
                'current_balance' => $this->current_balance,
                'currency' => $this->currency,
                'error' => $e->getMessage()
            ]);

            // Return a fallback value (zero in the current currency)
            return Money::zero($this->currency);
        } catch (MoneyMismatchException $e) {
            Log::error('Currency mismatch in piggy bank', [
                'piggy_bank_id' => $this->id,
                'currency' => $this->currency,
                'error' => $e->getMessage()
            ]);

            // Return a fallback value (zero in the current currency)
            return Money::zero($this->currency);
        }
    }

}
