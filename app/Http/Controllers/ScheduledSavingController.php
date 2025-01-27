<?php

namespace App\Http\Controllers;

use App\Models\ScheduledSaving;
use Illuminate\Http\Request;
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
        $validatedData = $request->validate([
            'piggy_bank_id' => 'sometimes|exists:piggy_banks,id',
            'payment_due_date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|min:0',
            'status' => ['sometimes', Rule::in(['saved', 'pending'])],
        ]);

        $periodicSaving->update($validatedData);

        return response()->json($periodicSaving);
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
