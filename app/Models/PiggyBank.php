<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PiggyBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'link',
        'details',
        'starting_amount',
        'image',
        'currency',
        'balance',
        'date',
        'purchase_date',
        'status',
    ];

    // Default attributes
    protected $attributes = [
        'image' => 'images/piggy_banks/default_piggy_bank.png',
    ];

    // Relationship with User model
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with periodic savings
    public function periodicSavings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PeriodicSaving::class);
    }

    // Relationship with transactions
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Accessor for remaining amount
    public function getRemainingAmountAttribute(): float
    {
        return $this->price - $this->starting_amount;
    }

    // Accessor for total saved amount (sum of all 'in' transactions)
    public function getTotalSavedAttribute(): float
    {
        return $this->transactions()->where('type', 'in')->sum('amount');
    }

    // Accessor for total withdrawn amount (sum of all 'out' transactions)
    public function getTotalWithdrawnAttribute(): float
    {
        return $this->transactions()->where('type', 'out')->sum('amount');
    }
}
