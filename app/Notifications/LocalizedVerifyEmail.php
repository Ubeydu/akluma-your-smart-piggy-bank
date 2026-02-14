<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class LocalizedVerifyEmail extends VerifyEmail
{
    /**
     * Verification link lifetime in minutes (7 days).
     */
    private const EXPIRE_MINUTES = 10080;

    protected function buildMailMessage($url): MailMessage
    {
        $expireMinutes = Config::get('auth.verification.expire', self::EXPIRE_MINUTES);
        $expireDays = (int) ($expireMinutes / 1440);

        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->line(Lang::get('Please click the button below to verify your email address.'))
            ->action(Lang::get('Verify Email Address'), $url)
            ->line(Lang::get('This verification link will expire in :days days.', ['days' => $expireDays]))
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    protected function verificationUrl($notifiable): string
    {
        $locale = app()->getLocale();
        $routeName = 'localized.verification.verify.'.$locale;

        $url = URL::temporarySignedRoute(
            $routeName,
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', self::EXPIRE_MINUTES)),
            [
                'locale' => $locale,
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        //        \Log::debug('🔍 Verification URL debug', [
        //            'route_name' => $routeName,
        //            'route_exists' => \Route::has($routeName),
        //            'locale' => $locale,
        //            'user_id' => $notifiable->getKey(),
        //            'generated_url' => $url,
        //        ]);

        return $url;
    }
}
