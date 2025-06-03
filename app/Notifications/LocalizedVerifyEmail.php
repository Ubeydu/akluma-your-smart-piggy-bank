<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class LocalizedVerifyEmail extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        $locale = app()->getLocale();
        $routeName = 'localized.verification.verify.'.$locale;

        $url = URL::temporarySignedRoute(
            $routeName,
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
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
