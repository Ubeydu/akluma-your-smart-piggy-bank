<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class LocalizedVerifyEmail extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        $locale = app()->getLocale();

        return URL::temporarySignedRoute(
            'localized.verification.verify.' . $locale,
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'locale' => app()->getLocale(),
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
