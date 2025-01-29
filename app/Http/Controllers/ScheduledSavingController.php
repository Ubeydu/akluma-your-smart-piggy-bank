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
        Log::info('ScheduledSaving update method called.', [
            'saving_id' => $periodicSaving->id,
            'request_data' => $request->all()
        ]);

        $validatedData = $request->validate([
            'piggy_bank_id' => 'required|exists:piggy_banks,id',
            'status' => ['required', Rule::in(['saved', 'pending'])],
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($periodicSaving, $validatedData) {
                Log::info('Inside DB Transaction', ['saving_id' => $periodicSaving->id]);

                $piggyBank = PiggyBank::findOrFail($validatedData['piggy_bank_id']);
                Log::info('PiggyBank found', ['piggy_bank_id' => $piggyBank->id, 'current_balance' => $piggyBank->current_balance]);

                $amount = $validatedData['amount'];

                if ($validatedData['status'] === 'saved' && $periodicSaving->status === 'pending') {
                    $piggyBank->increment('current_balance', $amount);
                    Log::info('Added to balance', ['new_balance' => $piggyBank->current_balance]);
                } elseif ($validatedData['status'] === 'pending' && $periodicSaving->status === 'saved') {
                    $piggyBank->decrement('current_balance', $amount);
                    Log::info('Subtracted from balance', ['new_balance' => $piggyBank->current_balance]);
                }

                $periodicSaving->update(['status' => $validatedData['status']]);
                Log::info('Updated ScheduledSaving status', ['new_status' => $validatedData['status']]);
            });

            $updatedBalance = PiggyBank::find($validatedData['piggy_bank_id'])->current_balance;
            Log::info('ScheduledSaving update successful.', [
                'saving_id' => $periodicSaving->id,
                'new_status' => $validatedData['status'],
                'new_balance' => $updatedBalance
            ]);

            return response()->json([
                'status' => $validatedData['status'],
                'translated_status' => __(strtolower($validatedData['status'])),
                'new_balance' => $updatedBalance,
                'remaining_amount' => PiggyBank::find($validatedData['piggy_bank_id'])->remaining_amount,
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
