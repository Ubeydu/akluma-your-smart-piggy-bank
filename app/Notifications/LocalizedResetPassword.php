<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class LocalizedResetPassword extends ResetPasswordNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        parent::__construct($token);
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable): string
    {
        $locale = $notifiable->language ?? app()->getLocale();
        $routeName = 'localized.password.reset.'.$locale;

//        \Log::debug('🔍 Password reset route debug', [
//            'route_name' => $routeName,
//            'route_exists' => \Route::has($routeName),
//            'locale' => $locale,
//        ]);

        return url(route($routeName, [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'locale' => $locale,
        ], false));
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $previousLocale = app()->getLocale();
        $locale = $notifiable->language ?? app()->getLocale();
        app()->setLocale($locale);

        $mail = (new MailMessage)
            ->subject(Lang::get('Reset Password Notification'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
            ->action(Lang::get('Reset Password'), $this->resetUrl($notifiable))
            ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));

        app()->setLocale($previousLocale);

        return $mail;
    }
}
