<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use App\Services\PaymentScheduleService;
use App\Services\PickDateCalculationService;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PiggyBankCreateController extends Controller
{
    protected PickDateCalculationService $pickDateCalculationService;

    public function __construct(PickDateCalculationService $pickDateCalculationService)
    {
        $this->pickDateCalculationService = $pickDateCalculationService;
    }


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

        \Log::info('Current Application Locale:', [
            'app_locale' => app()->getLocale(),
            'fallback_locale' => config('app.fallback_locale'),
            'session_locale' => session('locale')
        ]);

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



        \Log::info('Session data stored:', $request->session()->get('pick_date_step1', []));


        return view('create-piggy-bank.common.step-2');
    }

    public function clearForm(Request $request)
    {
        // clear the session data for step 1
        $request->session()->forget('pick_date_step1');

        return response()->json(['success' => true]);
    }

    public function showStep2()
    {
        // Load any necessary data from session or database if needed
        return view('create-piggy-bank.common.step-2');
    }


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

        return redirect()->back()->withErrors(['strategy' => 'Invalid strategy selected.']);
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
     * Calculate frequency options for the pick-date strategy.
     */
    public function calculateFrequencyOptions(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date|after:today',
        ]);

        $step1Data = $request->session()->get('pick_date_step1');
        if (!$step1Data) {
            return response()->json(['error' => 'Missing step 1 data'], 400);
        }

        // Log the values for debugging
        Log::info('Calculating frequency options', [
            'price' => $step1Data['price'] ?? 'Not set',
            'starting_amount' => $step1Data['starting_amount'] ?? 'Not set',
            'purchase_date' => $request->purchase_date
        ]);

        $calculations = $this->pickDateCalculationService->calculateAllFrequencyOptions(
            $step1Data['price'],
            $step1Data['starting_amount'],
            $request->purchase_date
        );

        // Store calculations in session for next step
        $request->session()->put('pick_date_step3', [
            'date' => $request->purchase_date,
            'calculations' => $calculations
        ]);

        return response()->json($calculations);
    }

    /**
     * Store the selected frequency option.
     */
    public function storeSelectedFrequency(Request $request)
    {
        $request->validate([
            'frequency_type' => 'required|in:minutes,hours,days,weeks,months,years',
        ]);

        $step3Data = $request->session()->get('pick_date_step3');
        if (!$step3Data) {
            return response()->json(['error' => 'Missing calculation data'], 400);
        }

        // Update the step3 data with selected frequency
        $step3Data['selected_frequency'] = $request->frequency_type;
        $request->session()->put('pick_date_step3', $step3Data);

        return response()->json(['success' => true]);
    }

//    public function showSummary(Request $request)
//    {
//        // Get all relevant session data
//        $summary = [
//            'pick_date_step1' => $request->session()->get('pick_date_step1'),
//            'pick_date_step2' => $request->session()->get('pick_date_step2'),
//            'pick_date_step3' => $request->session()->get('pick_date_step3')
//        ];
//
//        $request->session()->put('debug_summary', $summary);
//
//        if ($request->isMethod('post')) {
//            // If it's a POST request, store the data and redirect to GET route
//            return redirect()->route('create-piggy-bank.pick-date.summary');
//        }
//
//        // Now, let's generate the payment schedule before returning the view
//        // First, we get the necessary data from the summary
//        $selectedFrequency = $summary['pick_date_step3']['selected_frequency'];
//        $calculations = $summary['pick_date_step3']['calculations'][$selectedFrequency];
//
//        // Create an instance of our new PaymentScheduleService
//        $scheduleService = new PaymentScheduleService();
//
//        // Generate the payment schedule using the service
//        // Note how we pass in all the required parameters from our existing data
//        $paymentSchedule = $scheduleService->generateSchedule(
//            $summary['pick_date_step3']['date'],  // The target date from step 3
//            $calculations['frequency'],            // How many payments are needed
//            $selectedFrequency,                    // The period type (days, weeks, etc.)
//            $calculations['amount']                // The amount details for each payment
//        );
//
//        // Return the view with both the summary and the payment schedule
//        return view('create-piggy-bank.pick-date.summary', [
//            'summary' => $summary,
//            'paymentSchedule' => $paymentSchedule  // Add this new data to the view
//        ]);
//    }

    public function showSummary(Request $request)
    {
        // Get all relevant session data - keeping this part unchanged
        $summary = [
            'pick_date_step1' => $request->session()->get('pick_date_step1'),
            'pick_date_step2' => $request->session()->get('pick_date_step2'),
            'pick_date_step3' => $request->session()->get('pick_date_step3')
        ];

        $request->session()->put('debug_summary', $summary);

        // Handle POST request - keeping this part unchanged
        if ($request->isMethod('post')) {
            return redirect()->route('create-piggy-bank.pick-date.summary');
        }

        // Get the necessary data for generating payment schedule
        $selectedFrequency = $summary['pick_date_step3']['selected_frequency'];
        $calculations = $summary['pick_date_step3']['calculations'][$selectedFrequency];

        // Generate payment schedule
        $scheduleService = new PaymentScheduleService();
        $paymentSchedule = $scheduleService->generateSchedule(
            $summary['pick_date_step3']['date'],
            $calculations['frequency'],
            $selectedFrequency,
            $calculations['amount']
        );

        // Parse dates for comparison
        $targetDate = Carbon::parse($summary['pick_date_step3']['date']);
        $finalPaymentDate = Carbon::parse(end($paymentSchedule)['date']);
        $today = Carbon::today();

        // Initialize variables for date storage and user messaging
        $savingCompletionDate = $finalPaymentDate;
        $dateMessage = null;

        // Validate dates and set appropriate message
        if ($targetDate->isPast() || $finalPaymentDate->isPast()) {
            $savingCompletionDate = Carbon::tomorrow();
            $dateMessage = __('Due to a calculation error, we\'ve adjusted your saving plan to start from tomorrow.');
        } else {
            if ($finalPaymentDate->equalTo($targetDate)) {
                $savingCompletionDate = $finalPaymentDate;
            }
            elseif ($finalPaymentDate->lt($targetDate)) {
                $savingCompletionDate = $finalPaymentDate;
                $dateMessage = __('Good news! You will reach your saving goal earlier than planned, on :date', [
                    'date' => $finalPaymentDate->format('d.m.Y')
                ]);
            }
            else {
                $savingCompletionDate = $finalPaymentDate;
                $dateMessage = __('Note: Your saving plan will complete on :date', [
                    'date' => $finalPaymentDate->format('d.m.Y')
                ]);
            }
        }

        // Return view with all necessary data
        return view('create-piggy-bank.pick-date.summary', [
            'summary' => $summary,
            'paymentSchedule' => $paymentSchedule,
            'dateMessage' => $dateMessage,
            'savingCompletionDate' => $savingCompletionDate->format('Y-m-d')
        ]);
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


    /**
     * Handle cancellation from any step of the piggy bank creation process.
     * This method needs to clear both common step data and strategy-specific data.
     */
    public function cancelCreation(Request $request)
    {
        try {
            // Clear common step data
            $request->session()->forget('pick_date_step1');

            // Clear strategy selection
            $request->session()->forget('chosen_strategy');

            // Clear strategy-specific data
            $request->session()->forget(['pick_date_step3', 'enter_saving_step3']);

            // Add an informative flash message
            return redirect()
                ->route('piggy-banks.index')
                ->with('warning', __('You cancelled creating your piggy bank.'));

        } catch (\Exception $e) {
            Log::error('Error during piggy bank creation cancellation:', [
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', __('There was an error cancelling the process. Please try again.'));
        }
    }


}
