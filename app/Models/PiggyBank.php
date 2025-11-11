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
        'vault_id',
        'uptodate_final_total',
        'remaining_amount',
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
     * HYBRID APPROACH (Phase 1 of migration):
     * - If DB column has a value, use it (preferred)
     * - Otherwise, calculate on-the-fly (fallback for safety during migration)
     *
     * Returns how much more needs to be saved to reach the current goal.
     * Uses uptodate_final_total if set (after recalculation), otherwise falls back to final_total.
     * Both represent the COMPLETE goal (starting_amount + scheduled savings).
     *
     * Formula: Goal - Actual Saved
     * Negative value means user exceeded their goal.
     *
     * ⚠️ This method is called automatically by Laravel/Eloquent
     * when you access $piggyBank->remaining_amount.
     *
     * @noinspection PhpUnused
     */
    public function getRemainingAmountAttribute(): float
    {
        try {
            // PHASE 1: Prefer DB column if it exists and has a value
            if (isset($this->attributes['remaining_amount']) && $this->attributes['remaining_amount'] !== null) {
                return (float) $this->attributes['remaining_amount'];
            }

            // FALLBACK: Calculate on-the-fly (for safety during migration or if column not yet populated)
            $projectedTotal = $this->uptodate_final_total ?? $this->final_total;

            return $projectedTotal - $this->actual_final_total_saved;
        } catch (\Throwable $e) {
            Log::error('Invalid money calculation in piggy bank', [
                'piggy_bank_id' => $this->id,
                'total_savings' => $this->total_savings,
                'actual_final_total_saved' => $this->actual_final_total_saved,
                'uptodate_final_total' => $this->uptodate_final_total,
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

    /**
     * Get the net manual money (additions minus withdrawals)
     *
     * Returns the total amount of money manually added or removed.
     * Positive = net additions, Negative = net withdrawals
     *
     * @noinspection PhpUnused
     */
    public function getManualMoneyNetAttribute(): float
    {
        return $this->transactions()
            ->whereIn('type', ['manual_add', 'manual_withdraw'])
            ->sum('amount');
    }

    /**
     * Calculate the up-to-date projected final total
     *
     * This represents the COMPLETE total money user will have when done.
     * Formula: starting_amount + sum of all active scheduled savings (saved + pending, excluding archived)
     *
     * This value changes when:
     * - Schedule is recalculated (new amounts)
     *
     * This value does NOT change when:
     * - User marks savings as saved/pending (total scheduled amount stays same)
     * - User manually adds/withdraws money (schedule target doesn't change)
     * - User pauses/resumes (dates change, amounts don't)
     */
    public function calculateUptodateFinalTotal(): float
    {
        // The complete total money user will have when done includes:
        // 1. Starting amount (initial deposit)
        // 2. All active scheduled savings (both saved and pending, excluding archived)
        // 3. Manual money added/withdrawn outside the schedule
        return ($this->starting_amount ?? 0)
            + $this->scheduledSavings()
                ->where('archived', false)
                ->sum('amount')
            + $this->manual_money_net;
    }

    /**
     * Update the remaining_amount column in the database
     *
     * Call this method after any financial change:
     * - Transaction created/deleted
     * - Schedule recalculated
     * - Initial piggy bank creation
     *
     * This ensures the DB column stays in sync with the calculated value.
     */
    public function updateRemainingAmount(): void
    {
        $projectedTotal = $this->uptodate_final_total ?? $this->final_total;
        $actualTotal = $this->transactions()->sum('amount');
        $remainingAmount = $projectedTotal - $actualTotal;

        $this->update(['remaining_amount' => $remainingAmount]);
    }

    public static function getStatusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PiggyBankTransaction::class);
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }
}
