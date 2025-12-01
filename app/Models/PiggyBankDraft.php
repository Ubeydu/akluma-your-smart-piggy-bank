<?php

namespace App\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $email
 * @property string $name
 * @property string $currency
 * @property string $strategy
 * @property string $frequency
 * @property array $step1_data
 * @property array $step3_data
 * @property array $payment_schedule
 * @property float $price
 * @property string $preview_image
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder forUser(int $userId, ?string $email = null)
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class PiggyBankDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'currency',
        'strategy',
        'frequency',
        'step1_data',
        'step3_data',
        'payment_schedule',
        'price',
        'preview_image',
    ];

    protected $casts = [
        'step1_data' => 'array',
        'step3_data' => 'array',
        'payment_schedule' => 'array',
        'price' => 'decimal:2',
    ];

    /**
     * Relationship: Draft belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Serialize session data to database-storable format
     * Converts Money objects to [amount => float, currency => string]
     */
    public static function serializeSessionData(array $sessionData, string $currency): array
    {
        $serialized = [];

        foreach ($sessionData as $key => $value) {
            if ($value instanceof Money) {
                $serialized[$key] = [
                    'amount' => $value->getAmount()->toFloat(),
                    'currency' => $value->getCurrency()->getCurrencyCode(),
                ];
            } elseif (is_array($value)) {
                $serialized[$key] = self::serializeSessionData($value, $currency);
            } else {
                $serialized[$key] = $value;
            }
        }

        return $serialized;
    }

    /**
     * Deserialize database data back to session format
     * Reconstructs Money objects from [amount, currency] arrays
     */
    public static function deserializeToSession(array $data, string $currency): array
    {
        $deserialized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Check if this is a serialized Money object
                if (isset($value['amount']) && isset($value['currency'])) {
                    $deserialized[$key] = Money::of(
                        $value['amount'],
                        $value['currency']
                    );
                } else {
                    // Recursively deserialize nested arrays
                    $deserialized[$key] = self::deserializeToSession($value, $currency);
                }
            } else {
                $deserialized[$key] = $value;
            }
        }

        return $deserialized;
    }

    /**
     * Get formatted price for display
     */
    public function getFormattedPriceAttribute(): string
    {
        return Money::of($this->price, $this->currency)
            ->formatTo(app()->getLocale());
    }

    /**
     * Scope: Drafts for authenticated user
     * Returns drafts where:
     * - user_id matches (authenticated user's drafts), OR
     * - user_id is null AND email matches (guest drafts from Issue #234)
     */
    public function scopeForUser($query, int $userId, ?string $email = null)
    {
        return $query->where(function ($q) use ($userId, $email) {
            // Get drafts created by authenticated user
            $q->where('user_id', $userId);

            // Also get guest drafts with matching email (Issue #234)
            if ($email) {
                $q->orWhere(function ($subQ) use ($email) {
                    $subQ->whereNull('user_id')
                        ->where('email', $email);
                });
            }
        });
    }
}
