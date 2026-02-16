<?php

namespace App\Models;

use App\Notifications\LocalizedResetPassword;
use App\Notifications\LocalizedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'accepted_terms_at',
        'accepted_privacy_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
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
            'accepted_terms_at' => 'datetime',
            'accepted_privacy_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user has set a password (not a Google-only user).
     */
    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    public function piggyBanks(): HasMany
    {
        return $this->hasMany(PiggyBank::class);
    }

    public function vaults(): HasMany
    {
        return $this->hasMany(Vault::class);
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
                'push' => ['enabled' => true],
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
                'push' => ['enabled' => true],
            ];
            $user->save();
        });
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($this->language);

        // Change this line
        $this->notify(new LocalizedVerifyEmail);

        app()->setLocale($previousLocale);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($this->language);

        $this->notify(new LocalizedResetPassword($token));

        app()->setLocale($previousLocale);
    }
}
