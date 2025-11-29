<?php

namespace App\Http\Controllers;

use App\Models\PiggyBankDraft;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PiggyBankDraftController extends Controller
{
    /**
     * Display list of user's drafts
     * GET /{locale}/draft-piggy-banks
     */
    public function index()
    {
        $drafts = PiggyBankDraft::forUser(Auth::id(), Auth::user()->email)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('draft-piggy-banks.index', compact('drafts'));
    }

    /**
     * Show draft details (read-only view)
     * GET /{locale}/draft-piggy-banks/{draft}
     */
    public function show(PiggyBankDraft $draft)
    {
        // Authorization check
        if (! Gate::allows('view', $draft)) {
            abort(403);
        }

        // Deserialize data for display
        $currency = $draft->currency;
        $step1Data = PiggyBankDraft::deserializeToSession($draft->step1_data, $currency);
        $step3Data = PiggyBankDraft::deserializeToSession($draft->step3_data, $currency);
        $paymentSchedule = PiggyBankDraft::deserializeToSession($draft->payment_schedule, $currency);

        // Prepare data for view (similar to summary pages)
        $summary = [
            'pick_date_step1' => $step1Data,
            $draft->strategy === 'pick-date' ? 'pick_date_step3' : 'enter_saving_amount_step3' => $step3Data,
        ];

        // Calculate financial summary values based on strategy
        $selectedFrequency = $step3Data['selected_frequency'];

        if ($draft->strategy === 'pick-date') {
            // Pick Date strategy
            $targetAmount = $step3Data['calculations'][$selectedFrequency]['target_amount']['amount'] ?? null;
            $extraSavings = $step3Data['calculations'][$selectedFrequency]['extra_savings']['amount'] ?? null;
            $totalSavings = $step3Data['calculations'][$selectedFrequency]['total_savings']['amount'] ?? null;
        } else {
            // Enter Saving Amount strategy
            $startingAmount = $step1Data['starting_amount'] ?? null;
            if ($startingAmount && ! $startingAmount->isZero()) {
                $price = $step1Data['price'];
                $targetAmount = $price->minus($startingAmount);
            } else {
                $targetAmount = $step1Data['price'];
            }

            // Calculate extra savings
            $totalSavingsData = $step3Data['target_dates'][$selectedFrequency]['total_amount']['amount'] ?? null;
            $totalSavingsAmount = null;

            if ($totalSavingsData instanceof Money) {
                $totalSavingsAmount = $totalSavingsData;
            } elseif ($totalSavingsData && is_array($totalSavingsData)) {
                $totalSavingsAmount = Money::of($totalSavingsData['amount'], $totalSavingsData['currency']);
            }

            $extraSavings = $totalSavingsAmount && $targetAmount ? $totalSavingsAmount->minus($targetAmount) : null;
            $totalSavings = $totalSavingsAmount;
        }

        // Calculate planned final total
        $startingAmount = $step1Data['starting_amount'] ?? null;
        $plannedFinalTotal = null;
        if ($startingAmount && $totalSavings) {
            $plannedFinalTotal = $startingAmount->plus($totalSavings);
        } elseif ($totalSavings) {
            $plannedFinalTotal = $totalSavings;
        }

        // Calculate date message for early completion
        $dateMessage = null;

        if (! empty($paymentSchedule)) {
            // Get target date based on strategy
            if ($draft->strategy === 'pick-date') {
                $targetDate = $step3Data['date'];
                if (! $targetDate instanceof Carbon) {
                    $targetDate = Carbon::parse($targetDate);
                }
            } else {
                $targetDateString = $step3Data['target_dates'][$selectedFrequency]['target_date'] ?? null;
                $targetDate = $targetDateString ? Carbon::parse($targetDateString) : null;
            }

            // Get final payment date from schedule
            $lastPayment = end($paymentSchedule);
            $finalPaymentDate = Carbon::parse($lastPayment['date']);

            // Compare and generate message if completing early
            if ($targetDate && $finalPaymentDate->lt($targetDate)) {
                $localizedDate = $finalPaymentDate
                    ->copy()
                    ->setTimezone(config('app.timezone'))
                    ->locale(App::getLocale());

                $dateMessage = __('Good news! You will reach your saving goal earlier than planned, on :date', [
                    'date' => $localizedDate->isoFormat('LL'),
                ]);
            }
        }

        // Check if user has active creation session
        $hasActiveSession = session()->has('chosen_strategy')
            || session()->has('pick_date_step1')
            || session()->has('enter_saving_amount_step3');

        return view('draft-piggy-banks.show', compact(
            'draft',
            'summary',
            'paymentSchedule',
            'hasActiveSession',
            'targetAmount',
            'extraSavings',
            'totalSavings',
            'plannedFinalTotal',
            'dateMessage'
        ));
    }

    /**
     * Store a new draft from summary page
     * POST /{locale}/draft-piggy-banks/store
     */
    public function store(Request $request)
    {
        // Check if there's a piggy bank creation in progress
        $strategy = session('chosen_strategy');

        if (! $strategy) {
            return redirect(localizedRoute('localized.create-piggy-bank.step-1'))
                ->with('error', __('No piggy bank creation in progress.'));
        }

        // Get session data
        $step1Data = session('pick_date_step1');

        if ($strategy === 'pick-date') {
            $step3Data = session('pick_date_step3');
        } else {
            $step3Data = session('enter_saving_amount_step3');
        }

        $paymentSchedule = session('payment_schedule');

        // Validate required data exists
        if (! $step1Data || ! $step3Data || ! $paymentSchedule) {
            return redirect(localizedRoute('localized.create-piggy-bank.step-1'))
                ->with('error', __('Missing required data to save draft.'));
        }

        // Get frequency - must exist, no assumptions
        $frequency = $step3Data['selected_frequency'] ?? null;

        if (! $frequency) {
            return redirect(localizedRoute('localized.create-piggy-bank.step-1'))
                ->with('error', __('Missing frequency data. Please start over.'));
        }

        // Serialize data (convert Money objects to storable format)
        $currency = $step1Data['currency'];
        $serializedStep1 = PiggyBankDraft::serializeSessionData($step1Data, $currency);
        $serializedStep3 = PiggyBankDraft::serializeSessionData($step3Data, $currency);
        $serializedSchedule = PiggyBankDraft::serializeSessionData($paymentSchedule, $currency);

        // Create draft
        PiggyBankDraft::create([
            'user_id' => Auth::id(),
            'name' => $step1Data['name'],
            'currency' => $currency,
            'strategy' => $strategy,
            'frequency' => $frequency,
            'step1_data' => $serializedStep1,
            'step3_data' => $serializedStep3,
            'payment_schedule' => $serializedSchedule,
            'price' => $step1Data['price']->getAmount()->toFloat(),
            'preview_image' => $step1Data['preview']['image'] ??
                'images/piggy_banks/default_piggy_bank.png',
        ]);

        // Clear ALL session data
        $request->session()->forget([
            'pick_date_step1',
            'pick_date_step3',
            'enter_saving_amount_step3',
            'chosen_strategy',
            'payment_schedule',
            'final_payment_date',
        ]);

        return redirect(localizedRoute('localized.draft-piggy-banks.index'))
            ->with('success', __('Draft saved successfully!'));
    }

    /**
     * Resume a draft (restore session and redirect to summary)
     * POST /{locale}/draft-piggy-banks/{draft}/resume
     */
    public function resume(Request $request, PiggyBankDraft $draft)
    {
        // Authorization check
        if (! Gate::allows('update', $draft)) {
            abort(403);
        }

        // Clear any existing session data first
        $request->session()->forget([
            'pick_date_step1',
            'pick_date_step3',
            'enter_saving_amount_step3',
            'chosen_strategy',
            'payment_schedule',
            'final_payment_date',
        ]);

        // Deserialize data back to session format
        $currency = $draft->currency;
        $step1Data = PiggyBankDraft::deserializeToSession($draft->step1_data, $currency);
        $step3Data = PiggyBankDraft::deserializeToSession($draft->step3_data, $currency);
        $paymentSchedule = PiggyBankDraft::deserializeToSession($draft->payment_schedule, $currency);

        // Restore session data
        $request->session()->put('pick_date_step1', $step1Data);
        $request->session()->put('chosen_strategy', $draft->strategy);
        $request->session()->put('payment_schedule', $paymentSchedule);

        if ($draft->strategy === 'pick-date') {
            $request->session()->put('pick_date_step3', $step3Data);
            $summaryRoute = 'localized.create-piggy-bank.pick-date.show-summary';
        } else {
            $request->session()->put('enter_saving_amount_step3', $step3Data);
            $summaryRoute = 'localized.create-piggy-bank.enter-saving-amount.show-summary';
        }

        // Redirect to appropriate summary page
        return redirect(localizedRoute($summaryRoute));
    }

    /**
     * Delete a draft
     * DELETE /{locale}/draft-piggy-banks/{draft}
     */
    public function destroy(PiggyBankDraft $draft)
    {
        // Authorization check
        if (! Gate::allows('delete', $draft)) {
            abort(403);
        }

        $draft->delete();

        return redirect(localizedRoute('localized.draft-piggy-banks.index'))
            ->with('success', __('Draft deleted successfully!'));
    }
}
