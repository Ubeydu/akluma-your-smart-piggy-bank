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
        'status',
    ];

    // Relationship with User model
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
