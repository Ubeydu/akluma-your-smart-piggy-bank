<?php

namespace App\Policies;

use App\Models\PiggyBankDraft;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PiggyBankDraftPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view their own drafts list
    }

    /**
     * Determine whether the user can view the model.
     * Allows access if:
     * - Draft belongs to user (user_id match), OR
     * - Draft is a guest draft (user_id null) with matching email (Issue #234)
     */
    public function view(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Allows access if:
     * - Draft belongs to user (user_id match), OR
     * - Draft is a guest draft (user_id null) with matching email (Issue #234)
     */
    public function update(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }

    /**
     * Determine whether the user can delete the model.
     * Allows access if:
     * - Draft belongs to user (user_id match), OR
     * - Draft is a guest draft (user_id null) with matching email (Issue #234)
     */
    public function delete(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return $user->id === $piggyBankDraft->user_id
            || ($piggyBankDraft->user_id === null && $user->email === $piggyBankDraft->email);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PiggyBankDraft $piggyBankDraft): bool
    {
        return false;
    }
}
