<?php

namespace App\Services;

use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use Brick\Money\Money;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Service for recalculating piggy bank saving schedules
 *
 * Handles the complete workflow of:
 * - Validating recalculation requests
 * - Archiving old pending schedule items
 * - Generating new schedules with updated amounts
 */
class ScheduleRecalculationService
{
    public function __construct(
        private SavingScheduleService $scheduleService
    ) {}

    /**
     * Main entry point for schedule recalculation
     *
     * @param  PiggyBank  $piggyBank  The piggy bank to recalculate
     * @param  float  $newPeriodicAmount  New amount for periodic savings
     * @return bool Success status
     *
     * @throws InvalidArgumentException If validation fails
     * @throws Exception If database transaction fails
     */
    public function recalculateSchedule(PiggyBank $piggyBank, float $newPeriodicAmount): bool
    {
        // 1. Validate the recalculation request
        $this->validateRecalculation($piggyBank, $newPeriodicAmount);

        // 2. Start database transaction
        DB::beginTransaction();

        try {
            // 3. Get current version number
            $currentVersion = $piggyBank->scheduledSavings()
                ->active()
                ->max('recalculation_version') ?? 1;

            $newVersion = $currentVersion + 1;

            // 4. Archive old pending schedule
            $archivedCount = $this->archiveOldPendingSchedule($piggyBank);

            Log::info('Schedule recalculation: archived old pending items', [
                'piggy_bank_id' => $piggyBank->id,
                'archived_count' => $archivedCount,
                'new_version' => $newVersion,
            ]);

            // 5. Generate and save new schedule
            $newSchedule = $this->generateNewSchedule($piggyBank, $newPeriodicAmount, $newVersion);

            Log::info('Schedule recalculation: created new schedule', [
                'piggy_bank_id' => $piggyBank->id,
                'new_items_count' => $newSchedule->count(),
                'new_version' => $newVersion,
            ]);

            // 6. Update uptodate_final_total to reflect the new schedule
            $piggyBank->uptodate_final_total = $piggyBank->calculateUptodateFinalTotal();
            $piggyBank->save();

            Log::info('Schedule recalculation: updated uptodate_final_total', [
                'piggy_bank_id' => $piggyBank->id,
                'uptodate_final_total' => $piggyBank->uptodate_final_total,
            ]);

            // 7. Update remaining_amount in database after schedule recalculation
            $piggyBank->updateRemainingAmount();

            // 8. Commit transaction
            DB::commit();

            return true;

        } catch (Exception $e) {
            // Rollback on any error
            DB::rollBack();

            Log::error('Schedule recalculation failed', [
                'piggy_bank_id' => $piggyBank->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Validates whether recalculation is possible
     *
     * @throws InvalidArgumentException If validation fails
     */
    public function validateRecalculation(PiggyBank $piggyBank, float $newPeriodicAmount): void
    {
        // 1. Check if piggy bank is active
        if ($piggyBank->status !== 'active') {
            throw new InvalidArgumentException(__('Cannot recalculate schedule for inactive piggy bank'));
        }

        // 2. Check if new amount is positive
        if ($newPeriodicAmount <= 0) {
            throw new InvalidArgumentException(__('New periodic amount must be greater than 0'));
        }

        // 3. Check if there are pending scheduled savings
        $pendingCount = $piggyBank->scheduledSavings()
            ->where('status', ScheduledSaving::STATUS_PENDING)
            ->active()
            ->count();

        if ($pendingCount === 0) {
            throw new InvalidArgumentException(__('No pending scheduled savings to recalculate'));
        }

        // 4. Check if new amount is less than remaining amount
        $remainingAmount = $piggyBank->remaining_amount;
        if ($newPeriodicAmount >= $remainingAmount) {
            throw new InvalidArgumentException(__('New periodic amount must be less than remaining amount'));
        }
    }

    /**
     * Archives all pending (unpaid) scheduled savings for a piggy bank
     *
     * @return int Number of items archived
     */
    public function archiveOldPendingSchedule(PiggyBank $piggyBank): int
    {
        // Only archive PENDING items, preserve SAVED items
        $archivedCount = $piggyBank->scheduledSavings()
            ->where('status', ScheduledSaving::STATUS_PENDING)
            ->active()
            ->update([
                'archived' => true,
                'updated_at' => now(),
            ]);

        return $archivedCount;
    }

    /**
     * Calculate parameters needed for generating new schedule
     *
     * @return array{frequency: int, lastSavingNumber: int, periodType: string, newVersion: int, startDate: string}
     */
    private function calculateNewScheduleParameters(PiggyBank $piggyBank, float $newPeriodicAmount): array
    {
        // 1. Calculate how many payments needed
        $remainingAmount = $piggyBank->remaining_amount;
        $frequency = (int) ceil($remainingAmount / $newPeriodicAmount);

        // 2. Get max saving_number across ALL items (keeps numbers unique)
        $lastSavingNumber = $piggyBank->scheduledSavings()
            ->max('saving_number') ?? 0;

        // 3. Get frequency type from piggy bank
        $periodType = $piggyBank->selected_frequency; // 'days', 'weeks', 'months', 'years'

        // 4. Get current version and increment
        $currentVersion = $piggyBank->scheduledSavings()
            ->active()
            ->max('recalculation_version') ?? 1;
        $newVersion = $currentVersion + 1;

        // 5. Get start date (one day after earliest pending item's date)
        $earliestPendingDate = $piggyBank->scheduledSavings()
            ->where('status', ScheduledSaving::STATUS_PENDING)
            ->active()
            ->min('saving_date');

        $startDate = $earliestPendingDate
            ? \Carbon\Carbon::parse($earliestPendingDate)->addDay()->toDateString()
            : \Carbon\Carbon::tomorrow()->toDateString();

        return [
            'frequency' => $frequency,
            'lastSavingNumber' => $lastSavingNumber,
            'periodType' => $periodType,
            'newVersion' => $newVersion,
            'startDate' => $startDate,
        ];
    }

    /**
     * Generates new schedule items with updated periodic amount
     *
     * @param  int  $version  Recalculation version number
     * @return Collection Collection of newly created ScheduledSaving models
     */
    public function generateNewSchedule(PiggyBank $piggyBank, float $newPeriodicAmount, int $version): Collection
    {
        // 1. Get parameters for schedule generation
        $params = $this->calculateNewScheduleParameters($piggyBank, $newPeriodicAmount);

        // 2. Calculate target date based on start date + frequency
        $startDate = Carbon::parse($params['startDate']);
        $targetDate = clone $startDate;

        switch ($params['periodType']) {
            case 'days':
                $targetDate->addDays($params['frequency']);
                break;
            case 'weeks':
                $targetDate->addWeeks($params['frequency']);
                break;
            case 'months':
                $targetDate->addMonths($params['frequency']);
                break;
            case 'years':
                $targetDate->addYears($params['frequency']);
                break;
        }

        // 3. Prepare amount details with Money object
        $amountDetails = [
            'amount' => Money::of($newPeriodicAmount, $piggyBank->currency),
        ];

        // 4. Generate schedule using existing service
        $schedule = $this->scheduleService->generateSchedule(
            $targetDate->toDateString(),
            $params['frequency'],
            $params['periodType'],
            $amountDetails
        );

        // 5. Create new ScheduledSaving records
        $newSavings = collect();

        foreach ($schedule as $payment) {
            $scheduledSaving = $piggyBank->scheduledSavings()->create([
                'saving_number' => $params['lastSavingNumber'] + $payment['payment_number'],
                'amount' => $payment['amount']->getAmount()->toFloat(),
                'saving_date' => $payment['date'],
                'status' => ScheduledSaving::STATUS_PENDING,
                'archived' => false,
                'recalculation_version' => $version,
            ]);

            $newSavings->push($scheduledSaving);
        }

        return $newSavings;
    }
}
