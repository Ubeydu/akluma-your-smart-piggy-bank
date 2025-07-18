<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $details
 * @property string $created_at
 * @property string $updated_at
 */
class Vault extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'details',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function piggyBanks(): HasMany
    {
        return $this->hasMany(PiggyBank::class);
    }

    public function getTotalSavedAttribute(): array
    {
        return $this->piggyBanks()
            ->whereIn('status', ['active', 'paused', 'done'])
            ->get()
            ->groupBy('currency')
            ->map(function ($piggyBanks) {
                return $piggyBanks->sum('actual_final_total_saved');
            })
            ->toArray();
    }
}
