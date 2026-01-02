<?php

namespace App\Listeners;

use App\Models\PiggyBankDraft;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class ClaimGuestDraftsOnLogin
{
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        PiggyBankDraft::whereNull('user_id')
            ->where('email', strtolower($user->email))
            ->update(['user_id' => $user->id]);
    }
}
