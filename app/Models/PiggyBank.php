<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Brick\Money\Money;

class PiggyBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'link',
        'details',
        'starting_amount',
        'image',
        'currency',
    ];

    protected $attributes = [
        'image' => 'images/piggy_banks/default_piggy_bank.png',
        'currency' => 'TRY',
        'status' => 'active',
        'balance' => 0,
        'starting_amount' => 0,
    ];

    protected $casts = [
        'price' => MoneyCast::class,
        'starting_amount' => MoneyCast::class,
        'balance' => MoneyCast::class,
    ];

    // Relationship with User model
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with periodic savings
    public function periodicSavings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PeriodicSaving::class);
    }

    // Relationship with transactions
//    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
//    {
//        return $this->hasMany(Transaction::class);
//    }

    // Accessor for remaining amount (in minor units)
    public function getRemainingAmountAttribute(): Money
    {
        return $this->price->minus($this->balance);
    }

    // Accessor for formatted remaining amount (as float)
    public function getFormattedRemainingAmountAttribute(): float
    {
        return $this->remaining_amount->getAmount()->toFloat();
    }

    // Accessor for total saved amount (in minor units)
//    public function getTotalSavedAttribute(): int
//    {
//        return $this->transactions()->where('type', 'in')->sum('amount');
//    }

    // Accessor for formatted total saved (as float)
//    public function getFormattedTotalSavedAttribute(): float
//    {
//        return $this->total_saved / 100;
//    }

    // Accessor for total withdrawn amount (in minor units)
//    public function getTotalWithdrawnAttribute(): int
//    {
//        return $this->transactions()->where('type', 'out')->sum('amount');
//    }

    // Accessor for formatted total withdrawn (as float)
//    public function getFormattedTotalWithdrawnAttribute(): float
//    {
//        return $this->total_withdrawn / 100;
//    }
}
