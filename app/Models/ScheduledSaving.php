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
 * @property Carbon $saving_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PiggyBank $piggyBank
 */
class ScheduledSaving extends Model
{
    protected $fillable = [
        'piggy_bank_id',
        'saving_number',
        'amount',
        'status',
        'saving_date'
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'saving_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function piggyBank(): BelongsTo
    {
        return $this->belongsTo(PiggyBank::class);
    }
}
