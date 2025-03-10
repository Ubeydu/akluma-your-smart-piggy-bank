<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property mixed $language
 * @property string $currency
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'language',
        'currency',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
        ];
    }

    public function piggyBanks(): HasMany
    {
        return $this->hasMany(PiggyBank::class);
    }

    public function updateTimezone($timezone): void
    {
        $this->update(['timezone' => $timezone]);
    }


    // Add a method to get default preferences
    public function getNotificationPreferencesAttribute($value)
    {
        if (empty($value)) {
            return [
                'email' => ['enabled' => true],
                'sms' => ['enabled' => true],
                'push' => ['enabled' => true]
            ];
        }

        return json_decode($value, true);
    }



    protected static function booted(): void
    {
        static::created(function ($user) {
            $user->notification_preferences = [
                'email' => ['enabled' => true],
                'sms' => ['enabled' => true],
                'push' => ['enabled' => true]
            ];
            $user->save();
        });
    }
}
