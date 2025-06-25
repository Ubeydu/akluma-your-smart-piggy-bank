<?php

namespace App\Models;

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
 * @property float $final_total
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

    /**
     * Eloquent accessor for the "remaining_amount" attribute.
     *
     * ⚠️ This method is called automatically by Laravel/Eloquent
     * when you access $piggyBank->remaining_amount.
     *
     * @noinspection PhpUnused
     */
    public function getRemainingAmountAttribute(): float
    {
        try {
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

    /**
     * Calculate the current balance by summing all transaction rows for this piggy bank.
     *
     * @noinspection PhpUnused
     */
    public function getCurrentBalanceAttribute(): float
    {
        // Get all related transactions
        return $this->transactions()->sum('amount');
    }

    /**
     * Eloquent accessor for the "final_total" attribute.
     *
     * This method returns the stable, stored value of the user's intended total—
     * i.e., the amount the user will find in their piggy bank if they complete all scheduled savings,
     * **regardless of any manual additions or withdrawals** made along the way.
     *
     * This is set at creation (starting amount + planned savings), and is only updated if the user
     * manually increases or decreases their piggy bank goal. It is NOT dynamically recalculated from
     * transaction history, and does not reflect the live current balance.
     *
     * Usage example: $piggyBank->final_total (used in Blade and PHP)
     *
     * @noinspection PhpUnused
     * @return float
     */
    public function getFinalTotalAttribute(): float
    {
        // Always return the stored attribute.
        return $this->attributes['final_total'] ?? 0.0;
    }

    public static function getStatusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PiggyBankTransaction::class);
    }
}
