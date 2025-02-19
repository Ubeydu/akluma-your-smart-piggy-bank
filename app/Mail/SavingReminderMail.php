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
        // Set locale based on user's preferred language
        // (You might need to adjust this based on how you store user preferences)
        $locale = $this->user->preferred_language ?? App::getLocale();
        App::setLocale($locale);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('saving_reminder_subject', [
                'name' => $this->piggyBank->name
            ]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.saving-reminder',
            with: [
                'piggyBankUrl' => route('piggy-banks.show', $this->piggyBank->id),
            ],
        );
    }
}
