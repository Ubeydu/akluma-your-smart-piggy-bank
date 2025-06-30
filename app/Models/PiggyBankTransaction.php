<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiggyBankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'piggy_bank_id',
        'user_id',
        'type',
        'amount',
        'note',
        'scheduled_for',
    ];

    // Optional: Add relationships if you want to navigate back
    public function piggyBank()
    {
        return $this->belongsTo(PiggyBank::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
