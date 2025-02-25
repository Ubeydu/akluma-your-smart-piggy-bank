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
        // Set locale based on user's preferred language
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
        // Use a hardcoded development URL for testing
        $baseUrl = 'http://127.0.0.1:8000';
        $piggyBankUrl = $baseUrl . '/piggy-banks/' . $this->piggyBank->id;

        // Format the date
        $formattedDate = Carbon::parse($this->scheduledSaving->saving_date)->format('Y-m-d');

        return new Content(
            markdown: 'emails.saving-reminder',
            with: [
                'piggyBankUrl' => $piggyBankUrl,
                'formattedDate' => $formattedDate,
                'escapedPiggyBankName' => htmlspecialchars_decode(htmlspecialchars($this->piggyBank->name)),
            ],
        );
    }


}
