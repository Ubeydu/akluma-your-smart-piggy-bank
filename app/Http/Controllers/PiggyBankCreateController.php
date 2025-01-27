<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use App\Services\LinkPreviewService;
use App\Services\SavingScheduleService;
use App\Services\PickDateCalculationService;
use Brick\Math\Exception\MathException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PiggyBankCreateController extends Controller
{
    private LinkPreviewService $linkPreviewService;
    private PickDateCalculationService $pickDateCalculationService;

    public function __construct(
        PickDateCalculationService $pickDateCalculationService,
        LinkPreviewService $linkPreviewService
    ) {
        // Store both services in their respective properties
        $this->pickDateCalculationService = $pickDateCalculationService;
        $this->linkPreviewService = $linkPreviewService;
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
     * @throws MathException
     * @throws UnknownCurrencyException
     */
    public function step2(Request $request)
    {
//        \Log::info('Request data:', [
//            'starting_amount_whole' => $request->input('starting_amount_whole'),
//            'starting_amount_cents' => $request->input('starting_amount_cents'),
//            'all_data' => $request->all()
//        ]);

//        \Log::info('Current Application Locale:', [
//            'app_locale' => app()->getLocale(),
//            'fallback_locale' => config('app.fallback_locale'),
//            'session_locale' => session('locale')
//        ]);

        $request->merge([
            'price_cents' => (int) $request->input('price_cents'),
            'starting_amount_cents' => (int) $request->input('starting_amount_cents'),
        ]);

        $validated = $request->validate([
            'name' => 'required|string',
            'price_whole' => 'required|integer|min:1|max:9999999999',
            'price_cents' => [
                Rule::requiredIf(fn() => CurrencyHelper::hasDecimalPlaces($request->input('currency'))),
                'integer',
                'min:0',
                'max:99'
            ],
            'currency' => 'required|string|size:3',
            'link' => 'nullable|url|max:255',
            'details' => 'nullable|string|max:5000',
            'starting_amount_whole' => 'nullable|integer|min:0|max:9999999999',
            'starting_amount_cents' => [
                Rule::requiredIf(fn() =>
                    CurrencyHelper::hasDecimalPlaces($request->input('currency')) &&
                    !empty($request->input('starting_amount_whole'))
                ),
                'nullable',
                'integer',
                'min:0',
                'max:99'
            ],
        ]);

//        \Log::info('Validated Request Data', [
//            'validated_data' => $validated,
//        ]);

//        \Log::info('Link Preview Input', [
//            'link' => $validated['link'] ?? 'No link provided',
//        ]);


        // Inside step2 method, replace the preview array creation sections with:
        if (!empty($validated['link'])) {
            try {
                $preview = $this->linkPreviewService->getPreviewData($validated['link']);

//                \Log::info('Link preview fetched:', [
//                    'url' => $validated['link'],
//                    'preview_data' => $preview,
//                ]);

            } catch (Exception $e) {
//                \Log::warning('Failed to fetch link preview:', [
//                    'url' => $validated['link'],
//                    'error' => $e->getMessage(),
//                ]);

                $preview = [
                    'title' => null,
                    'description' => null,
                    'image' => null,
                    'url' => $validated['link']
                ];
            }
        } else {
            $preview = [
                'title' => null,
                'description' => null,
                'image' => null,
                'url' => null
            ];
        }

        if ($preview && $preview['image'] !== null && !filter_var($preview['image'], FILTER_VALIDATE_URL)) {
            $preview['image'] = url($preview['image']); // Only convert to full URL if it's not null and not already a URL

//            \Log::info('Preview Image URL', [
//                'original_image' => $preview['image'] ?? 'No image',
//                'full_url' => $preview['image'] ?? 'No URL generated'
//            ]);
        }

        Log::info('Money Input Values:', [
            'price_whole' => $validated['price_whole'],
            'currency' => $validated['currency'],
            'price_whole_type' => gettype($validated['price_whole'])
        ]);

//        $price = Money::of($validated['price_whole'], $validated['currency']);


        if (CurrencyHelper::hasDecimalPlaces($validated['currency'])) {
            $priceString = $validated['price_whole'] . '.' .
                str_pad($validated['price_cents'], 2, '0', STR_PAD_LEFT);
            $price = Money::of($priceString, $validated['currency']);
        } else {
            $price = Money::of($validated['price_whole'], $validated['currency']);
        }


//        Log::info('Money Result:', [
//            'price' => $price->getAmount(),
//            'currency' => $price->getCurrency()
//        ]);


        \Log::info('Money Object Details', [
            'price_whole' => $validated['price_whole'],
            'price_cents' => $validated['price_cents'],
            'currency' => $validated['currency'],
            'money_object' => [
                'amount' => $price->getAmount()->__toString(),
                'minor_amount' => $price->getMinorAmount()->toInt(),
                'formatted' => $price->formatTo(App::getLocale())
            ]
        ]);


        $startingAmount = null;

        if (!empty($validated['starting_amount_whole']) || !empty($validated['starting_amount_cents'])) {
            $startingAmountString = ($validated['starting_amount_whole'] ?? '0') . '.' .
                (CurrencyHelper::hasDecimalPlaces($validated['currency'])
                    ? str_pad($validated['starting_amount_cents'] ?? '00', 2, '0', STR_PAD_LEFT)
                    : '00');

            try {
                $startingAmount = Money::of($startingAmountString, $validated['currency']);
            } catch (Exception $e) {
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
//
//        \Log::info('Preview Data Before Session Storage', [
//            'preview_full_details' => $preview,
//            'preview_image_url' => $preview['image'] ?? 'No image URL',
//            'preview_original_link' => $preview['url'] ?? 'No original link'
//        ]);

        // Store step 1 data in session
        $request->session()->put('pick_date_step1', [
            'name' => $validated['name'],
            'price' => $price,
            'currency' => $validated['currency'],
            'link' => $validated['link'],
            'details' => $validated['details'],
            'starting_amount' => $startingAmount,
            'preview' => $preview ?? null,
        ]);



//        \Log::info('Session data stored:', $request->session()->get('pick_date_step1', []));


        return view('create-piggy-bank.common.step-2');
    }

    /**
     * Fetch preview data for a URL via AJAX request.
     * This method handles the dynamic link preview functionality for step 1 of piggy bank creation.
     * When users enter a URL, it attempts to fetch preview data including an image.
     * If anything fails, it gracefully falls back to default values.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchLinkPreview(Request $request): JsonResponse
    {
        // First, validate the incoming URL
        $validated = $request->validate([
            'url' => 'required|url|max:1000'
        ]);

        try {
            // Attempt to fetch the preview data using our LinkPreviewService
            $preview = $this->linkPreviewService->getPreviewData($validated['url']);

            // If the preview fetch failed completely, set up a default response
            if (!$preview) {
                $preview = [
                    'title' => null,
                    'description' => null,
                    'image' => '/images/default_piggy_bank.png',
                    'url' => $validated['url']
                ];
            }

            // Ensure the image URL is an absolute URL, not a relative path
            if ($preview['image'] && !filter_var($preview['image'], FILTER_VALIDATE_URL)) {
                $preview['image'] = url($preview['image']);
            }

            // Return successful response with preview data
            return response()->json([
                'success' => true,
                'preview' => $preview
            ]);

        } catch (Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Error fetching link preview:', [
                'url' => $validated['url'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a fallback response with default values
            return response()->json([
                'success' => false,
                'preview' => [
                    'title' => null,
                    'description' => null,
                    'image' => url('/images/default_piggy_bank.png'),
                    'url' => $validated['url']
                ]
            ]);
        }
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

//        Log::debug('Starting calculateFrequencyOptions with input', [
//            'purchase_date' => $request->purchase_date,
//        ]);

        $request->validate([
            'purchase_date' => 'required|date|after:today',
        ]);

//        Log::debug('Validation passed for purchase_date');

        $step1Data = $request->session()->get('pick_date_step1');
        if (!$step1Data) {
            return response()->json(['error' => 'Missing step 1 data'], 400);
        }

        $purchaseDate = Carbon::createFromFormat('Y-m-d', $request->purchase_date);

//        Log::debug('Created Carbon date object', [
//            'input_date' => $request->purchase_date,
//            'parsed_date' => $purchaseDate->toDateString()
//        ]);


        $calculations = $this->pickDateCalculationService->calculateAllFrequencyOptions(
            $step1Data['price'],
            $step1Data['starting_amount'],
            $purchaseDate->toDateString()  // This will output YYYY-MM-DD
        );

        $request->session()->put('pick_date_step3', [
            'date' => $purchaseDate->toDateString(),
            'calculations' => $calculations
        ]);


        session()->flash('success', __('Saving options have been calculated for :date', [
            'date' => $purchaseDate->locale(app()->getLocale())->isoFormat('LL')
        ]));

        return response()->json($calculations);
    }

    /**
     * Store the selected frequency option.
     */
    public function storeSelectedFrequency(Request $request)
    {
        $request->validate([
            'frequency_type' => 'required|in:days,weeks,months,years',
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


    public function showSummary(Request $request)
    {
        // Add detailed logging at the start
//        Log::info('ShowSummary Method - Session Debug', [
//            'full_session_data' => $request->session()->all(),
//            'pick_date_step3' => $request->session()->get('pick_date_step3')
//        ]);


        // Get all relevant session data - keeping this part unchanged
        $summary = [
            'pick_date_step1' => $request->session()->get('pick_date_step1'),
            'pick_date_step2' => $request->session()->get('pick_date_step2'),
            'pick_date_step3' => $request->session()->get('pick_date_step3')
        ];


//        // Log the summary details with more context
//        Log::info('ShowSummary Method - Date Details', [
//            'step3_date_raw' => $summary['pick_date_step3']['date'] ?? 'Not Set',
//            'step3_date_type' => gettype($summary['pick_date_step3']['date'] ?? null),
//            'step3_date_class' => get_class($summary['pick_date_step3']['date'] ?? null)
//        ]);

//        $request->session()->put('debug_summary', $summary);
//
//        // Add this after the debug_summary line for testing
//        $imageDebug = [
//            'raw_preview_data' => $summary['pick_date_step1']['preview'] ?? 'no preview data exists',
//            'session_dump' => [
//                'pick_date_step1' => $request->session()->get('pick_date_step1'),
//            ]
//        ];
//        $request->session()->put('image_debug', $imageDebug);

//        dd(session()->get('image_debug'));

        // Handle POST request - keeping this part unchanged
        if ($request->isMethod('post')) {
            return redirect()->route('create-piggy-bank.pick-date.summary');
        }

        // Get the necessary data for generating payment schedule
        $selectedFrequency = $summary['pick_date_step3']['selected_frequency'];
        $calculations = $summary['pick_date_step3']['calculations'][$selectedFrequency];

        // Generate payment schedule
        $scheduleService = new SavingScheduleService();

        $paymentSchedule = $scheduleService->generateSchedule(
            $summary['pick_date_step3']['date'],
            $calculations['frequency'],
            $selectedFrequency,
            $calculations['amount']
        );

        $request->session()->put('payment_schedule', $paymentSchedule);

        $targetDate = ($summary['pick_date_step3']['date'] instanceof Carbon)
            ? $summary['pick_date_step3']['date']->toDateString()
            : $summary['pick_date_step3']['date'];
        $targetDate = Carbon::createFromFormat('Y-m-d', $targetDate);


        $finalPaymentDate = Carbon::createFromFormat('Y-m-d', $paymentSchedule[count($paymentSchedule) - 1]['date']);
//        $firstPaymentDate = Carbon::createFromFormat('Y-m-d', $paymentSchedule[0]['date']);


//        // Store dates in session
//        $request->session()->put('final_payment_date', $finalPaymentDate->toDateString());
//        $request->session()->put('first_payment_date', $firstPaymentDate->toDateString());



        // Initialize variables for user messaging
        $dateMessage = null;

        // For comparisons, use UTC dates directly
        if ($targetDate->isPast() || $finalPaymentDate->isPast()) {
            $savingCompletionDate = Carbon::tomorrow()->utc();
            $dateMessage = __('Due to a calculation error, we\'ve adjusted your saving plan to start from tomorrow.');
        } else {
            if ($finalPaymentDate->equalTo($targetDate)) {
                $savingCompletionDate = $finalPaymentDate;
            }
            elseif ($finalPaymentDate->lt($targetDate)) {
                $savingCompletionDate = $finalPaymentDate;
                // Convert to local timezone for display
                $localizedDate = $finalPaymentDate
                    ->copy()
                    ->setTimezone(config('app.timezone'))
                    ->locale(App::getLocale());

                $dateMessage = __('Good news! You will reach your saving goal earlier than planned, on :date', [
                    'date' => $localizedDate->isoFormat('LL')
                ]);
            }
            else {
                $savingCompletionDate = $finalPaymentDate;
                // Convert to local timezone for display
                $localizedDate = $finalPaymentDate
                    ->copy()
                    ->setTimezone(config('app.timezone'))
                    ->locale(App::getLocale());

                $dateMessage = __('Note: Your saving plan will complete on :date', [
                    'date' => $localizedDate->isoFormat('LL')
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
     * Store piggy bank data from session to database.
     * @throws Exception
     */
    public function storePiggyBank(Request $request)
    {
        if (!$request->session()->has('pick_date_step3')) {
            return redirect()
                ->route('piggy-banks.index')
                ->with('warning', __('You already created a piggy bank with this information. So, we sent you to your piggy banks list to prevent creating a duplicate one.'));
        }


        DB::beginTransaction();

        try {
            $step1Data = $request->session()->get('pick_date_step1');
            $step3Data = $request->session()->get('pick_date_step3');
            $selectedFrequency = $step3Data['selected_frequency'];
            $calculations = $step3Data['calculations'][$selectedFrequency];
            $paymentSchedule = $request->session()->get('payment_schedule');

            $piggyBank = new PiggyBank();
            $piggyBank->user_id = auth()->id();
            $piggyBank->name = $step1Data['name'];


            $piggyBank->price = $step1Data['price']->getAmount()->toFloat();
            $piggyBank->starting_amount = $step1Data['starting_amount']?->getAmount()->toFloat();
            $piggyBank->current_balance = $step1Data['starting_amount']?->getAmount()->toFloat();
            $piggyBank->target_amount = $calculations['target_amount']['amount']->getAmount()->toFloat();
            $piggyBank->extra_savings = $calculations['extra_savings']['amount']->getAmount()->toFloat();
            $piggyBank->total_savings = $calculations['total_savings']['amount']->getAmount()->toFloat();

            // Basic fields remain the same
            $piggyBank->link = $step1Data['link'];
            $piggyBank->details = $step1Data['details'];
            $piggyBank->chosen_strategy = $request->session()->get('chosen_strategy');
            $piggyBank->selected_frequency = $selectedFrequency;
            $piggyBank->currency = $step1Data['currency'];

            // Preview data remains the same
            $preview = $step1Data['preview'] ?? [];
            $piggyBank->preview_title = $preview['title'] ?? null;
            $piggyBank->preview_description = $preview['description'] ?? null;
            $piggyBank->preview_image = $preview['image'] ?? 'images/default_piggy_bank.png';
            $piggyBank->preview_url = $preview['url'] ?? null;

            $piggyBank->save();

            // Update scheduled savings to use the same approach
            foreach ($paymentSchedule as $payment) {
                $scheduledSaving = new ScheduledSaving();
                $scheduledSaving->piggy_bank_id = $piggyBank->id;
                $scheduledSaving->saving_number = $payment['payment_number'];
                $scheduledSaving->amount = $payment['amount']->getAmount()->toFloat();
                $scheduledSaving->saving_date = $payment['date'];
                $scheduledSaving->save();
            }

            DB::commit();

            $request->session()->forget([
                'pick_date_step1',
                'pick_date_step3',
                'chosen_strategy',
                'payment_schedule',
                'final_payment_date'
            ]);

            // First store it in regular session
            $request->session()->put('newPiggyBankId', $piggyBank->id);

            // Log for debugging
            Log::info('Redirecting after piggy bank creation:', [
                'piggy_bank_id' => $piggyBank->id,
                'session_data' => session()->all()
            ]);

            return redirect()
                ->route('piggy-banks.index')
                ->with('success', __('Your piggy bank has been created successfully.'));

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error saving piggy bank:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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

        } catch (Exception $e) {
            Log::error('Error during piggy bank creation cancellation:', [
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', __('There was an error cancelling the process. Please try again.'));
        }
    }

}
