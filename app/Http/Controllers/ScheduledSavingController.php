<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ScheduledSavingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodicSavings = ScheduledSaving::all();

        return response()->json($periodicSavings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'piggy_bank_id' => 'required|exists:piggy_banks,id',
            'payment_due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:paid,unpaid,snoozed',
        ]);

        $periodicSaving = ScheduledSaving::create($validatedData);

        return response()->json($periodicSaving, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ScheduledSaving $periodicSaving)
    {
        return response()->json($periodicSaving);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScheduledSaving $periodicSaving)
    {
        //        Log::info('ScheduledSaving update method called.', [
        //            'saving_id' => $periodicSaving->id,
        //            'request_data' => $request->all()
        //        ]);

        $validatedData = $request->validate([
            'piggy_bank_id' => 'required|exists:piggy_banks,id',
            'status' => ['required', Rule::in(['saved', 'pending'])],
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($periodicSaving, $validatedData) {
                //                Log::info('Inside DB Transaction', ['saving_id' => $periodicSaving->id]);

                $piggyBank = PiggyBank::findOrFail($validatedData['piggy_bank_id']);

                $amount = $validatedData['amount'];

                // Instead of directly adjusting balance, insert a transaction row
                if ($validatedData['status'] === 'saved' && $periodicSaving->status === 'pending') {
                    // Marked as saved: add positive transaction
                    $piggyBank->transactions()->create([
                        'user_id' => $piggyBank->user_id,
                        'type' => 'scheduled_add',
                        'amount' => $amount,
                        'note' => 'Scheduled saving marked as saved',
                        'scheduled_for' => $periodicSaving->saving_date, // optional
                    ]);
                } elseif ($validatedData['status'] === 'pending' && $periodicSaving->status === 'saved') {
                    // Unmarked (was saved, now pending): add negative transaction
                    $piggyBank->transactions()->create([
                        'user_id' => $piggyBank->user_id,
                        'type' => 'scheduled_add',
                        'amount' => -1 * $amount,
                        'note' => 'Scheduled saving unmarked as saved',
                        'scheduled_for' => $periodicSaving->saving_date, // optional
                    ]);
                }

                // Update scheduled saving status
                $periodicSaving->update(['status' => $validatedData['status']]);

                // Update remaining_amount in database after transaction
                $piggyBank->updateRemainingAmount();

                // Automatically update piggy bank status if needed
                if (! in_array($piggyBank->status, ['paused', 'cancelled'])) {
                    // Only mark as done if remaining amount is zero or less
                    $remainingZeroOrLess = $piggyBank->remaining_amount <= 0;
                    $newStatus = $remainingZeroOrLess ? 'done' : 'active';

                    $update = ['status' => $newStatus];
                    if ($newStatus === 'done' && ! $piggyBank->actual_completed_at) {
                        $update['actual_completed_at'] = now();
                    }
                    $piggyBank->update($update);
                }
            });

            $updatedPiggyBank = PiggyBank::find($validatedData['piggy_bank_id']);

            $messageKey = $validatedData['status'] === 'saved'
                ? 'saving_marked_as_saved'
                : 'saving_marked_as_unsaved';

            return response()->json([
                'status' => $validatedData['status'],
                'translated_status' => __(strtolower($validatedData['status'])),
                'new_balance' => $updatedPiggyBank->actual_final_total_saved,
                'remaining_amount' => $updatedPiggyBank->remaining_amount,
                'piggy_bank_status' => $updatedPiggyBank->status,
                'message' => __($messageKey),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating scheduled saving:', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Pause a piggy bank (stopping scheduled savings temporarily)
     */
    public function pausePiggyBank($piggy_id)
    {
        $piggyBank = PiggyBank::findOrFail($piggy_id);

        // Ensure we do not pause a completed or cancelled piggy bank
        if (in_array($piggyBank->status, ['done', 'cancelled'])) {
            return response()->json(['error' => 'Cannot pause a completed or cancelled piggy bank.'], 400);
        }

        $piggyBank->update(['status' => 'paused']);

        return response()->json(['message' => __('piggy_bank_paused_info'), 'status' => 'paused']);
    }

    /**
     * Resume a piggy bank and recalculate the pending savings schedule with cycle preservation
     */
    public function resumePiggyBank($piggy_id)
    {
        // \Log::info("Resume called with test date: " . session('test_date'));

        $piggyBank = PiggyBank::findOrFail($piggy_id);

        $scheduleUpdated = false;

        // \Log::info("Found piggy bank with status: " . $piggyBank->status);

        if ($piggyBank->status !== 'paused') {
            return response()->json(['error' => 'Piggy bank is not paused.'], 400);
        }

        DB::transaction(function () use ($piggyBank, &$scheduleUpdated) {
            // Update status back to active
            $piggyBank->update(['status' => 'active']);

            // Get all active (non-archived) pending savings sorted by saving_number
            $pendingSavings = ScheduledSaving::where('piggy_bank_id', $piggyBank->id)
                ->where('status', 'pending')
                ->active()
                ->orderBy('saving_number', 'asc')
                ->get();

            // If no pending savings, nothing to recalculate
            if ($pendingSavings->isEmpty()) {
                return;
            }

            // Get the test date from session if it exists, otherwise use actual today
            $currentDate = session('test_date')
                ? Carbon::parse(session('test_date'))
                : Carbon::today();

            // Get the first saving
            $firstSaving = $pendingSavings->first();

            // \Log::info("First saving date: {$firstSaving->saving_date}, Current date: {$currentDate}");

            // Special handling for daily frequency
            if ($piggyBank->selected_frequency === 'days') {
                // For daily frequency, compare dates without time
                if ($firstSaving->saving_date->startOfDay()->gt($currentDate->startOfDay())) {
                    // \Log::info("First saving is in the future (daily schedule), skipping schedule update");
                    return;
                }
            } else {
                // For other frequencies, use existing comparison
                if ($firstSaving->saving_date->gt($currentDate)) {
                    // \Log::info("First saving is in the future, skipping schedule update");
                    return;
                }
            }

            //            // Start recalculating from today's date
            //            $newStartDate = Carbon::today();

            // Get the test date from session if it exists, otherwise use actual today
            $newStartDate = session('test_date')
                ? Carbon::parse(session('test_date'))
                : Carbon::today();

            // \Log::info("Using start date: " . $newStartDate->format('Y-m-d'));

            // Mapping for frequency
            $intervalMapping = [
                'days' => 'addDays',
                'weeks' => 'addWeeks',
                'months' => 'addMonths',
                'years' => 'addYears',
            ];

            $intervalFunction = $intervalMapping[$piggyBank->selected_frequency] ?? null;

            if (! $intervalFunction) {
                throw new \Exception('Invalid frequency set for piggy bank.');
            }

            foreach ($pendingSavings as $index => $saving) {
                // \Log::info("Processing saving #{$saving->saving_number} with date {$saving->saving_date}");

                // Start with the test date plus one day
                $workingDate = $newStartDate->copy()->addDay();

                // \Log::info("Processing with frequency: {$piggyBank->selected_frequency}");

                if ($piggyBank->selected_frequency === 'years') {
                    $workingDate->addYears($index);
                } elseif ($piggyBank->selected_frequency === 'months') {
                    $workingDate->addMonths($index);
                } elseif ($piggyBank->selected_frequency === 'weeks') {
                    // For weekly frequency, use addWeeks instead of calculating days
                    $workingDate->addWeeks($index);
                } else { // days
                    $workingDate->addDays($index);
                }

                // \Log::info("Working date calculated for saving #{$saving->saving_number}: {$workingDate}");

                // Update with the new date
                $saving->update(['saving_date' => $workingDate]);
                // \Log::info("Saved new date: {$workingDate}");
            }

            $scheduleUpdated = true;

        });

        // \Log::info("All savings processed");
        return response()->json(['message' => __('piggy_bank_resumed_schedule_not_updated_info'),
            'status' => 'active',
            'scheduleUpdated' => $scheduleUpdated,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScheduledSaving $periodicSaving)
    {
        $periodicSaving->delete();

        return response()->json(null, 204);
    }

    public function getSchedulePartial(Request $request, $piggy_id)
    {
        //        \Log::info('Schedule partial request', [
        //            'piggy_id' => $piggy_id,
        //            'is_ajax' => $request->ajax(),
        //            'is_xhr' => $request->header('X-Requested-With') === 'XMLHttpRequest',
        //            'auth_check' => auth()->check(),
        //            'auth_id' => auth()->id(),
        //            'session_id' => session()->getId(),
        //        ]);

        // Existing code to fetch and return the partial view
        $piggyBank = PiggyBank::findOrFail($piggy_id);

        // Paginate the scheduled savings with correct path
        // Filter out archived items, then sort: pending items first (actionable), then saved items (history), chronologically within each group
        $scheduledSavings = $piggyBank->scheduledSavings()
            ->active()
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('saving_date', 'asc')
            ->paginate(50)
            ->setPath(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]));

        return view('partials.schedule', compact('piggyBank', 'scheduledSavings'));
    }
}
