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
        'actual_completed_at',
        'preview_title',
        'preview_description',
        'preview_url',
    ];

    protected $attributes = [
        'preview_image' => 'images/piggy_banks/default_piggy_bank.png',
        'currency' => 'TRY',
        'status' => 'active',
    ];

    protected $casts = [
        'actual_completed_at' => 'datetime',
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
     * Returns the planned goal (final_total) minus the actual total saved (sum of all transactions).
     * This value tells you how much more needs to be saved to reach the goal, in real time.
     *
     * ⚠️ This method is called automatically by Laravel/Eloquent
     * when you access $piggyBank->remaining_amount.
     *
     * @noinspection PhpUnused
     */
    public function getRemainingAmountAttribute(): float
    {
        try {
            // Calculate remaining amount as planned final total minus actual total saved
            return $this->final_total - $this->actual_final_total_saved;
        } catch (\Throwable $e) {
            Log::error('Invalid money calculation in piggy bank', [
                'piggy_bank_id' => $this->id,
                'total_savings' => $this->total_savings,
                'actual_final_total_saved' => $this->actual_final_total_saved,
                'currency' => $this->currency,
                'error' => $e->getMessage(),
            ]);
            return 0.0;
        }
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
     */
    public function getFinalTotalAttribute(): float
    {
        // Always return the stored attribute.
        return $this->attributes['final_total'] ?? 0.0;
    }

    /**
     * Get the actual total money saved, dynamically calculated from transactions.
     *
     * Usage: $piggyBank->actual_final_total_saved
     *
     * @noinspection PhpUnused
     */
    public function getActualFinalTotalSavedAttribute(): float
    {
        // Sum all transaction amounts (additions and withdrawals)
        return $this->transactions()->sum('amount');
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
