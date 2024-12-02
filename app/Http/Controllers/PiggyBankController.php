<?php

namespace App\Http\Controllers;

use Brick\Money\Money;
use Illuminate\Http\Request;

class PiggyBankController extends Controller
{
    public function storeStep1(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'price_whole' => 'required|integer|min:1|max:999999999999999',
            'price_cents' => 'required|integer|min:0|max:99',
            'currency' => 'required|string|size:3',
            'link' => 'nullable|url|max:255',
            'details' => 'nullable|string|max:5000',
            'starting_amount_whole' => 'nullable|integer|min:0|max:999999999999999',
            'starting_amount_cents' => 'nullable|integer|min:0|max:99',
        ]);

        // Create Money objects
        $price = Money::of($validatedData['price_whole'] . '.' . str_pad($validatedData['price_cents'], 2, '0', STR_PAD_LEFT), $validatedData['currency']);
        $startingAmount = Money::of($validatedData['starting_amount_whole'] . '.' . str_pad($validatedData['starting_amount_cents'], 2, '0', STR_PAD_LEFT), $validatedData['currency']);

        // Store step 1 data in session
        $request->session()->put('piggy_bank_step1', [
            'name' => $validatedData['name'],
            'price' => $price,
            'currency' => $validatedData['currency'],
            'link' => $validatedData['link'],
            'details' => $validatedData['details'],
            'starting_amount' => $startingAmount,
        ]);

        return response()->json(['message' => 'Step 1 completed']);
    }

    public function storeStep2(Request $request)
    {
        $validatedData = $request->validate([
            // Add validation rules for step 2 fields
            'step2_field1' => 'required|string',
            'step2_field2' => 'required|integer',
            // ... other step 2 validations
        ]);

        // Store step 2 data in session
        $request->session()->put('piggy_bank_step2', $validatedData);

        return response()->json(['message' => 'Step 2 completed']);
    }

    public function storeStep3(Request $request)
    {
        $validatedData = $request->validate([
            // Add validation rules for step 3 fields
            'step3_field1' => 'required|string',
            'step3_field2' => 'required|integer',
            // ... other step 3 validations
        ]);

        // Retrieve data from all steps
        $step1Data = $request->session()->get('piggy_bank_step1');
        $step2Data = $request->session()->get('piggy_bank_step2');

        // Combine all data
        $piggyBankData = array_merge(
            $step1Data,
            $step2Data,
            $validatedData
        );

        // Create the piggy bank
        $piggyBank = $request->user()->piggyBanks()->create([
            'name' => $piggyBankData['name'],
            'price' => $piggyBankData['price'],
            'currency' => $piggyBankData['currency'],
            'link' => $piggyBankData['link'],
            'details' => $piggyBankData['details'],
            'starting_amount' => $piggyBankData['starting_amount'],
            'image' => $piggyBankData['image'] ?? null,  // if image is optional
        ]);

        // Clear session data
        $request->session()->forget(['piggy_bank_step1', 'piggy_bank_step2']);

        return response()->json($piggyBank);
    }

    // Method to retrieve current step data (useful for going back/forth)
    public function getCurrentStepData(Request $request, int $step)
    {
        $sessionKey = "piggy_bank_step{$step}";
        return response()->json($request->session()->get($sessionKey));
    }

    // Other methods remain the same...
}
