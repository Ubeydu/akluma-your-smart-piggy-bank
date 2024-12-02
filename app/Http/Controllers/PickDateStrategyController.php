<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use Illuminate\Http\Request;

class PickDateStrategyController extends Controller
{
    /**
     * Step 1: Gather basic piggy bank details.
     */
    public function step1(Request $request)
    {
        $currencies = config('app.currencies');
        $languageCurrencyMap = config('app.language_currency_map');
        $locale = app()->getLocale();
        $defaultCurrency = $languageCurrencyMap[$locale] ?? 'TRY';

        return view('create-piggy-bank.pick-date.step-1', compact('currencies', 'defaultCurrency'));
    }

    /**
     * Step 2: Save Step 1 data and proceed to date selection.
     */
    public function step2(Request $request)
    {

        dd($request->all());
//        $validated = $request->validate([
//            'name' => 'required|string|max:255',
//            'price' => 'required|numeric|min:0',
//            'currency' => 'required|string|in:' . implode(',', array_keys(config('app.currencies'))),
//            'link' => 'nullable|url|max:255',
//            'details' => 'nullable|string|max:5000',
//            'starting_amount' => 'nullable|numeric|min:0',
//        ]);
//
//        $request->session()->put('pick_date_step1', $validated);
//
//        return view('create_piggy_bank.pick_date.step2');
    }

    /**
     * Step 3: Save Step 2 data, calculate periodic savings, and proceed to summary.
     */
    public function step3(Request $request)
    {
        $step1Data = $request->session()->get('pick_date_step1');
        if (!$step1Data) {
            return redirect()->route('create-piggy-bank.pick-date.step1')->with('error', 'Please complete Step 1 first.');
        }

        $validated = $request->validate([
            'date' => 'required|date|after:today',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
        ]);

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

        // Render summary view
        return view('create_piggy_bank.pick_date.summary', [
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
