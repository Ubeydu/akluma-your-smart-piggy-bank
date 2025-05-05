<?php

namespace App\Mail;

use App\Models\ScheduledSaving;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminRetryAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $saving;
    public $attemptCount;

    /**
     * Create a new message instance.
     */
    public function __construct(ScheduledSaving $saving, int $attemptCount)
    {
        $this->saving = $saving;
        $this->attemptCount = $attemptCount;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject("🚨 {$this->attemptCount} failed email attempts for saving ID {$this->saving->id}")
            ->markdown('emails.admin.retry-alert');
    }
}
