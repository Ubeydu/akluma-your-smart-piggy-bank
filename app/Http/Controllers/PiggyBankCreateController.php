<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use Brick\Money\Money;
use Illuminate\Http\Request;

class PiggyBankCreateController extends Controller
{
    /**
     * Step 1: Display the initial form (Screen 1).
     */
    public function step1(Request $request)
    {
//        \Log::info('Session data in step1:', $request->session()->get('pick_date_step1', []));
        return view('create-piggy-bank.common.step-1');
    }

    /**
     * Step 2: Process Screen 1 data and show strategy selection screen (Screen 2).
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

        \Log::info('Values to be stored in database:', [
            'name' => $validated['name'],
            'price_in_cents' => (int)$price->getMinorAmount()->toInt(), // Get amount in minor units (cents)
            'currency' => $validated['currency'],
            'link' => $validated['link'],
            'details' => $validated['details'],
            'starting_amount_in_cents' => (int) $startingAmount?->getMinorAmount()->toInt(),
        ]);

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


        return view('create-piggy-bank.common.step-2');
    }

    public function showStep2()
    {
        // Load any necessary data from session or database if needed
        return view('create-piggy-bank.common.step-2');
    }


//    /**
//     * Step 3: Handle strategy choice and render appropriate view.
//     */
//    public function step3(Request $request)
//    {
//        $strategy = $request->session()->get('chosen_strategy');
//
//        if (!$strategy) {
//            return redirect()->route('create-piggy-bank.step-1')->with('error', 'No strategy selected.');
//        }
//
//        if ($strategy === 'pick-date') {
//            // Handle Pick Date Strategy logic (as previously discussed)
//
//            $step1Data = $request->session()->get('pick_date_step1');
//            if (!$step1Data) {
//                \Log::warning('Step 1 data missing in session');
//                return redirect()->route('create-piggy-bank.step-1')->with('error', 'Please complete Step 1 first.');
//            }
//
//            $validated = $request->validate([
//                'date' => 'required|date|after:today',
//                'frequency' => 'required|in:daily,weekly,monthly,yearly',
//            ]);
//
//            $price = $step1Data['price'];
//            $startingAmount = $step1Data['starting_amount'] ?? 0;
//            $remainingAmount = $price - $startingAmount;
//
//            $frequency = $validated['frequency'];
//            $targetDate = \Carbon\Carbon::parse($validated['date']);
//
//            $now = \Carbon\Carbon::now();
//            $intervals = match ($frequency) {
//                'daily' => $now->diffInDays($targetDate),
//                'weekly' => $now->diffInWeeks($targetDate),
//                'monthly' => $now->diffInMonths($targetDate),
//                'yearly' => $now->diffInYears($targetDate),
//            };
//
//            if ($intervals <= 0) {
//                return redirect()->back()->with('error', 'The selected date must be far enough in the future to calculate periodic savings.');
//            }
//
//            $periodicSavingAmount = $remainingAmount / $intervals;
//
//            $request->session()->put('pick_date_step3', array_merge($validated, [
//                'periodicSavingAmount' => $periodicSavingAmount,
//                'remainingAmount' => $remainingAmount,
//                'intervals' => $intervals,
//            ]));
//
//            return view('create-piggy-bank.pick-date.summary', [
//                'step1Data' => $step1Data,
//                'step3Data' => $request->session()->get('pick_date_step3'),
//            ]);
//
//        } elseif ($strategy === 'enter-saving-amount') {
//            // Handle Enter Saving Amount Strategy logic
//
//            $step1Data = $request->session()->get('pick_date_step1');
//            if (!$step1Data) {
//                \Log::warning('Step 1 data missing in session');
//                return redirect()->route('create-piggy-bank.step-1')->with('error', 'Please complete Step 1 first.');
//            }
//
//            // Example: Validation for `Enter Saving Amount Strategy`
//            $validated = $request->validate([
//                'saving_amount' => 'required|numeric|min:1',
//                'frequency' => 'required|in:daily,weekly,monthly,yearly',
//            ]);
//
//            $price = $step1Data['price'];
//            $startingAmount = $step1Data['starting_amount'] ?? 0;
//            $remainingAmount = $price - $startingAmount;
//            $savingAmount = $validated['saving_amount'];
//
//            // Calculate the number of intervals required
//            $intervals = ceil($remainingAmount / $savingAmount);
//
//            $request->session()->put('enter_saving_step3', array_merge($validated, [
//                'remainingAmount' => $remainingAmount,
//                'intervals' => $intervals,
//            ]));
//
//            return view('create-piggy-bank.enter-saving-amount.summary', [
//                'step1Data' => $step1Data,
//                'step3Data' => $request->session()->get('enter_saving_step3'),
//            ]);
//        }
//
//        return redirect()->route('create-piggy-bank.step-1')->with('error', 'Invalid strategy chosen.');
//    }


    /**
     * Handle POST request for Step 2: Validate and store the chosen strategy.
     */
    public function storeStrategySelection(Request $request)
    {
        // Validate strategy selection
        $validated = $request->validate([
            'strategy' => 'required|in:pick-date,enter-saving-amount',
        ]);

        // Store the chosen strategy in the session
        $request->session()->put('chosen_strategy', $validated['strategy']);

        // Redirect to the appropriate Step 3
        if ($validated['strategy'] === 'pick-date') {
            return redirect()->route('create-piggy-bank.pick-date.step-3');
        } elseif ($validated['strategy'] === 'enter-saving-amount') {
            return redirect()->route('create-piggy-bank.enter-saving-amount.step-3');
        }
    }

    /**
     * Render the view for the chosen strategy's next step (Step 3).
     */
    public function renderStrategyView(Request $request)
    {
        // Get the chosen strategy from the session
        $strategy = $request->session()->get('chosen_strategy');

        if ($strategy === 'pick-date') {
            return view('create-piggy-bank.pick-date.step-3');
        } elseif ($strategy === 'enter-saving-amount') {
            return view('create-piggy-bank.enter-saving-amount.step-3');
        }

        return redirect()->route('create-piggy-bank.step-1')->with('error', 'Invalid strategy chosen.');
    }



    /**
     * Finalize piggy bank creation and save to the database.
     */
    public function save(Request $request)
    {
        // Determine the chosen strategy
        $strategy = $request->session()->get('chosen_strategy');

        if (!$strategy) {
            return redirect()->route('create-piggy-bank.step-1')->with('error', 'No strategy selected.');
        }

        // Fetch shared Step 1 data
        $step1Data = $request->session()->get('pick_date_step1');
        if (!$step1Data) {
            return redirect()->route('create-piggy-bank.step-1')->with('error', 'Incomplete data. Please start the process again.');
        }

        // Strategy-specific Step 3 data
        $step3Data = null;

        if ($strategy === 'pick-date') {
            $step3Data = $request->session()->get('pick_date_step3');
            if (!$step3Data) {
                return redirect()->route('create-piggy-bank.pick-date.step-3')->with('error', 'Step 3 data missing. Please complete the process.');
            }

            // Validate required Pick Date Step 3 fields
            if (empty($step3Data['date'])) {
                return redirect()->route('create-piggy-bank.pick-date.step-3')->with('error', 'Invalid Step 3 data.');
            }
        } elseif ($strategy === 'enter-saving-amount') {
            $step3Data = $request->session()->get('enter_saving_step3');
            if (!$step3Data) {
                return redirect()->route('create-piggy-bank.enter-saving-amount.step-3')->with('error', 'Step 3 data missing. Please complete the process.');
            }

            // Validate required Enter Saving Amount Step 3 fields
            if (empty($step3Data['saving_amount'])) {
                return redirect()->route('create-piggy-bank.enter-saving-amount.step-3')->with('error', 'Invalid Step 3 data.');
            }
        } else {
            return redirect()->route('create-piggy-bank.step-1')->with('error', 'Invalid strategy selected.');
        }

        // Combine shared and strategy-specific data for saving
        $piggyBankData = array_merge($step1Data, [
            'user_id' => auth()->id(),
            'date' => $step3Data['date'] ?? null,
            'purchase_date' => $step3Data['date'] ?? null,
            'balance' => $step1Data['starting_amount'] ?? 0,
        ]);

        PiggyBank::create($piggyBankData);

        // Clear session data for the chosen strategy
        if ($strategy === 'pick-date') {
            $request->session()->forget(['pick_date_step1', 'pick_date_step3']);
        } elseif ($strategy === 'enter-saving-amount') {
            $request->session()->forget(['pick_date_step1', 'enter_saving_step3']);
        }

        return redirect()->route('piggy-banks.index')->with('success', 'Your piggy bank has been created successfully!');
    }
}
