<?php

namespace App\Mail;

use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class SavingReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public PiggyBank $piggyBank,
        public ScheduledSaving $scheduledSaving
    ) {
        // Set locale based on user's language preference
        $locale = $this->user->language ?? App::getLocale();
        App::setLocale($locale);

        //        \Log::info('📧 SavingReminderMail constructor called', [
        //            'user_id' => $this->user->id,
        //            'user_language' => $this->user->language,
        //            'piggy_bank_id' => $this->piggyBank->id,
        //            'set_locale' => $locale,
        //            'app_locale' => App::getLocale(),
        //        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        //        \Log::info('✉️ SavingReminderMail envelope() called', [
        //            'user_id' => $this->user->id,
        //            'app_locale' => App::getLocale(),
        //        ]);

        return new Envelope(
            subject: __('saving_reminder_subject', [
                'name' => $this->piggyBank->name,
            ]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        //        \Log::critical('🚨 CONTENT METHOD CALLED - THIS SHOULD APPEAR!', [
        //            'user_id' => $this->user->id,
        //            'piggy_bank_id' => $this->piggyBank->id,
        //        ]);

        // Use the localizedRoute helper to generate a properly localized URL
        $locale = $this->user->language ?? app()->getLocale();

        //        \Log::info('🔗 Email URL Generation Debug', [
        //            'user_id' => $this->user->id,
        //            'user_language' => $this->user->language,
        //            'resolved_locale' => $locale,
        //            'app_locale' => app()->getLocale(),
        //            'piggy_bank_id' => $this->piggyBank->id,
        //        ]);

        $piggyBankUrl = localizedRoute('localized.piggy-banks.show', [
            'piggy_id' => $this->piggyBank->id,
        ], $locale);

        //        \Log::info('🎯 Generated Email URL', [
        //            'url' => $piggyBankUrl,
        //            'locale' => $locale,
        //            'piggy_bank_id' => $this->piggyBank->id,
        //        ]);

        // Format the date
        $formattedDate = Carbon::parse($this->scheduledSaving->saving_date)->format('Y-m-d');

        return new Content(
            markdown: 'emails.saving-reminder',
            with: [
                'piggyBankUrl' => $piggyBankUrl,
                'formattedDate' => $formattedDate,
                'escapedPiggyBankName' => htmlspecialchars_decode(htmlspecialchars($this->piggyBank->name)),
                'user' => $this->user,
                'scheduledSaving' => $this->scheduledSaving,
                'piggyBank' => $this->piggyBank,
            ],
        );
    }
}
