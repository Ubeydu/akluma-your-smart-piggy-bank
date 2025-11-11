<?php

namespace App\Observers;

use App\Models\ScheduledSaving;

class ScheduledSavingObserver
{
    /**
     * Tracked fields that should update last_modified_at
     */
    private const TRACKED_FIELDS = ['saving_date', 'amount', 'status'];

    /**
     * Handle the ScheduledSaving "creating" event.
     * Set last_modified_at on creation.
     */
    public function creating(ScheduledSaving $scheduledSaving): void
    {
        $scheduledSaving->last_modified_at = now();
    }

    /**
     * Handle the ScheduledSaving "updating" event.
     * Update last_modified_at only if tracked fields changed.
     */
    public function updating(ScheduledSaving $scheduledSaving): void
    {
        // Get the dirty (changed) attributes
        $dirty = $scheduledSaving->getDirty();

        // Check if any of our tracked fields are being modified
        $trackedFieldsChanged = !empty(array_intersect(array_keys($dirty), self::TRACKED_FIELDS));

        if ($trackedFieldsChanged) {
            $scheduledSaving->last_modified_at = now();
        }
    }
}
