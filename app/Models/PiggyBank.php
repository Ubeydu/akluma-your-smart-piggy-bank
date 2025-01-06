<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property int $user_id
 * @property string $name
 * @property float $price
 * @property float|null $starting_amount
 * @property float|null $current_balance
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
 */
class PiggyBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'starting_amount',
        'current_balance',
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
        'preview_title',
        'preview_description',
        'preview_url'
    ];

    protected $attributes = [
        'preview_image' => 'images/piggy_banks/default_piggy_bank.png',
        'currency' => 'TRY',
        'status' => 'active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduledSavings(): HasMany
    {
        return $this->hasMany(ScheduledSaving::class);
    }
}
