<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use Illuminate\Http\Request;
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

                // Ensure `current_balance` is never NULL
                if (is_null($piggyBank->current_balance)) {
                    $piggyBank->current_balance = 0;
                }

//                Log::info('PiggyBank found', [
//                    'piggy_bank_id' => $piggyBank->id,
//                    'current_balance' => $piggyBank->current_balance
//                ]);

                $amount = $validatedData['amount'];

                // Adjust balance safely
                if ($validatedData['status'] === 'saved' && $periodicSaving->status === 'pending') {
                    $piggyBank->current_balance += $amount;
                } elseif ($validatedData['status'] === 'pending' && $periodicSaving->status === 'saved') {
                    $piggyBank->current_balance -= $amount;
                }

                // Save the updated balance
                $piggyBank->save();

                // Update scheduled saving status
                $periodicSaving->update(['status' => $validatedData['status']]);

                // Fetch updated remaining amount
                $updatedRemainingAmount = $piggyBank->remaining_amount;

                // Automatically update piggy bank status if needed
                if (!in_array($piggyBank->status, ['paused', 'cancelled'])) {
                    $newStatus = $updatedRemainingAmount == 0 ? 'done' : 'active';
                    $piggyBank->update(['status' => $newStatus]);
//                    Log::info('PiggyBank status updated', ['new_status' => $newStatus]);
                }
            });

            $updatedPiggyBank = PiggyBank::find($validatedData['piggy_bank_id']);

            return response()->json([
                'status' => $validatedData['status'],
                'translated_status' => __(strtolower($validatedData['status'])),
                'new_balance' => $updatedPiggyBank->current_balance,
                'remaining_amount' => $updatedPiggyBank->remaining_amount,
                'piggy_bank_status' => $updatedPiggyBank->status, // Send updated piggy bank status
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating scheduled saving:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScheduledSaving $periodicSaving)
    {
        $periodicSaving->delete();

        return response()->json(null, 204);
    }
}
