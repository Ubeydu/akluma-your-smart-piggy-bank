<?php

namespace App\Listeners;

use App\Models\PiggyBankDraft;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class ClaimGuestDraftsOnRegistration
{
    /**
     * Handle the event.
     *
     * Claim any guest drafts that match the newly registered user's email.
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        PiggyBankDraft::whereNull('user_id')
            ->where('email', strtolower($user->email))
            ->update(['user_id' => $user->id]);
    }
}
