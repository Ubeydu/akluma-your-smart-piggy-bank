<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDashboardStat extends Model
{
    protected $fillable = [
        'user_id',
        'stat_type',
        'currency_breakdown',
        'period',
        'calculated_at',
    ];

    protected $casts = [
        'currency_breakdown' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
