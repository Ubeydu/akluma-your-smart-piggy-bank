<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use Brick\Money\Money;
use Illuminate\Http\Request;

class PickDateStrategyController extends Controller
{
    /**
     * Step 1: Display the initial form.
     */
    public function step1(Request $request)
    {
//        \Log::info('Session data in step1:', $request->session()->get('pick_date_step1', []));
        return view('create-piggy-bank.pick-date.step-1');
    }

    /**
     * Step 2: Process step 1 data and show the date selection form.
     */
    public function step2(Request $request)
    {
//        \Log::info('Request data:', [
//            'starting_amount_whole' => $request->input('starting_amount_whole'),
//            'starting_amount_cents' => $request->input('starting_amount_cents'),
//            'all_data' => $request->all()
//        ]);


        $request->merge([
            'price_cents' => (int) $request->input('price_cents'),
            'starting_amount_cents' => (int) $request->input('starting_amount_cents'),
        ]);

        $validated = $request->validate([
            'name' => 'required|string',
            'price_whole' => 'required|integer|min:1|max:999999999999999',
            'price_cents' => 'required|integer|min:0|max:99',
            'currency' => 'required|string|size:3',
            'link' => 'nullable|url|max:255',
            'details' => 'nullable|string|max:5000',
            'starting_amount_whole' => 'nullable|integer|min:0|max:999999999999999',
            'starting_amount_cents' => 'nullable|integer|min:0|max:99',
        ]);

//        \Log::info('Validation passed, creating money objects:', [
//            'validated_data' => $validated,
//        ]);

        // Create Money objects
        $price = Money::of($validated['price_whole'] . '.' . str_pad($validated['price_cents'], 2, '0', STR_PAD_LEFT), $validated['currency']);

        $startingAmount = null;

        if (!empty($validated['starting_amount_whole'])) {
            $moneyString = $validated['starting_amount_whole'] . '.' . str_pad($validated['starting_amount_cents'] ?? '00', 2, '0', STR_PAD_LEFT);
//            \Log::info('Attempting to create starting amount:', ['money_string' => $moneyString]);

            try {
                $startingAmount = Money::of($moneyString, $validated['currency']);
//                \Log::info('Starting amount created successfully');
            } catch (\Exception $e) {
//                \Log::error('Error creating starting amount:', ['error' => $e->getMessage()]);
                return redirect()->back()->withErrors(['starting_amount_whole' => 'Invalid starting amount']);
            }
        }

//        \Log::info('Values to be stored in database:', [
//            'name' => $validated['name'],
//            'price_in_cents' => (int)$price->getMinorAmount()->toInt(), // Get amount in minor units (cents)
//            'currency' => $validated['currency'],
//            'link' => $validated['link'],
//            'details' => $validated['details'],
//            'starting_amount_in_cents' => (int) $startingAmount?->getMinorAmount()->toInt(),
//        ]);

        // Store step 1 data in session
        $request->session()->put('pick_date_step1', [
            'name' => $validated['name'],
            'price' => $price,
            'currency' => $validated['currency'],
            'link' => $validated['link'],
            'details' => $validated['details'],
            'starting_amount' => $startingAmount,
        ]);

//        \Log::info('Session data stored:', $request->session()->get('pick_date_step1', []));

        // Store the chosen strategy in the session
        $request->session()->put('chosen_strategy', $validated['strategy']);

        // Redirect to the appropriate Step 3 based on the chosen strategy
        if ($validated['strategy'] === 'pick-date') {
            return redirect()->route('create-piggy-bank.pick-date.step-3');
        } elseif ($validated['strategy'] === 'enter-saving-amount') {
            return redirect()->route('create-piggy-bank.enter-amount.step-3');
        }
    }

    /**
     * Step 3: Save Step 2 data, calculate periodic savings, and proceed to summary.
     */
    public function step3(Request $request)
    {
        \Log::info('Step 3 accessed', ['request' => $request->all()]);

        $step1Data = $request->session()->get('pick_date_step1');
        if (!$step1Data) {
            \Log::warning('Step 1 data missing in session');
            return redirect()->route('create-piggy-bank.pick-date.step1')->with('error', 'Please complete Step 1 first.');
        }

        $validated = $request->validate([
            'date' => 'required|date|after:today',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
        ]);
        \Log::info('Step 3 validation passed', ['validated' => $validated]);

        $price = $step1Data['price'];
        $startingAmount = $step1Data['starting_amount'] ?? 0;
        $remainingAmount = $price - $startingAmount;
        $frequency = $validated['frequency'];
        $targetDate = \Carbon\Carbon::parse($validated['date']);

        $now = \Carbon\Carbon::now();
        $intervals = match ($frequency) {
            'daily' => $now->diffInDays($targetDate),
            'weekly' => $now->diffInWeeks($targetDate),
            'monthly' => $now->diffInMonths($targetDate),
            'yearly' => $now->diffInYears($targetDate),
        };

        if ($intervals <= 0) {
            return redirect()->back()->with('error', 'The selected date must be far enough in the future to calculate periodic savings.');
        }

        $periodicSavingAmount = $remainingAmount / $intervals;

        // Save Step 3 data in the session
        $request->session()->put('pick_date_step3', array_merge($validated, [
            'periodicSavingAmount' => $periodicSavingAmount,
            'remainingAmount' => $remainingAmount,
            'intervals' => $intervals,
        ]));

        \Log::info('Step 3 data saved to session', [
            'step3Data' => $request->session()->get('pick_date_step3'),
            'step1Data' => $step1Data,
        ]);

        // Render summary view
        return view('create-piggy-bank.pick-date.summary', [
            'step1Data' => $step1Data,
            'step3Data' => $request->session()->get('pick_date_step3'),
        ]);
    }

    /**
     * Finalize piggy bank creation and save to the database.
     */
    public function save(Request $request)
    {
        $step1Data = $request->session()->get('pick_date_step1');
        $step3Data = $request->session()->get('pick_date_step3');

        if (!$step1Data || !$step3Data) {
            return redirect()->route('create-piggy-bank.pick-date.step1')->with('error', 'Incomplete data. Please start the process again.');
        }

        // Combine all data and save the piggy bank
        $piggyBankData = array_merge($step1Data, [
            'user_id' => auth()->id(),
            'date' => $step3Data['date'],
            'purchase_date' => $step3Data['date'],
            'balance' => $step1Data['starting_amount'] ?? 0,
        ]);

        PiggyBank::create($piggyBankData);

        // Clear session data after saving
        $request->session()->forget(['pick_date_step1', 'pick_date_step3']);

        return redirect()->route('piggy-banks.index')->with('success', 'Your piggy bank has been created successfully!');
    }
}
