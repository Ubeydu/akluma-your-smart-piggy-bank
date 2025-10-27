<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $piggy_bank_id
 * @property int $saving_number
 * @property float $amount
 * @property string $status
 * @property bool $archived
 * @property int $recalculation_version
 * @property Carbon $saving_date
 * @property Carbon|null $last_modified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PiggyBank $piggyBank
 */
class ScheduledSaving extends Model
{
    public const STATUS_SAVED = 'saved';

    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'piggy_bank_id',
        'saving_number',
        'amount',
        'status',
        'saving_date',
        'archived',
        'recalculation_version',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected $casts = [
        'saving_date' => 'date',
        'amount' => 'decimal:2',
        'archived' => 'boolean',
        'last_modified_at' => 'datetime',
    ];

    public function piggyBank(): BelongsTo
    {
        return $this->belongsTo(PiggyBank::class);
    }

    /**
     * Scope to only include non-archived scheduled savings
     */
    public function scopeActive($query): void
    {
        $query->where(function ($q) {
            $q->whereNull('archived')
                ->orWhere('archived', false);
        });
    }
}
