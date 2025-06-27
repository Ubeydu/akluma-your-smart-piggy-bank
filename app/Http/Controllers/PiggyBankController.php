<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PiggyBankController extends Controller
{
    public function index()
    {
        $piggyBanks = auth()->user()->piggyBanks()
            ->latest()
            ->get();

        // Get the values before clearing
        $newPiggyBankId = session('newPiggyBankId');
        $newPiggyBankCreatedTime = session('newPiggyBankCreatedTime');

        // Debug output to laravel.log
        // \Log::info('Piggy Bank Index Page Loaded', [
        //     'session_has_newPiggyBankId' => session()->has('newPiggyBankId'),
        //     'newPiggyBankId' => $newPiggyBankId,
        //     'newPiggyBankCreatedTime' => $newPiggyBankCreatedTime,
        //     'url' => request()->fullUrl(),
        //     'session_id' => session()->getId(),
        //     'time' => now()->toDateTimeString()
        // ]);

        // Clear them after getting the values
        session()->forget(['newPiggyBankId', 'newPiggyBankCreatedTime']);

        return view('piggy-banks.index', compact('piggyBanks', 'newPiggyBankId', 'newPiggyBankCreatedTime'));
    }

    public function update(Request $request, $piggy_id)
    {
        $piggyBank = PiggyBank::findOrFail($piggy_id);

        if (! Gate::allows('update', $piggyBank)) {
            abort(403);
        }

        // Validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        // Update only allowed fields
        $piggyBank->update([
            'name' => $validated['name'],
            'details' => $validated['details'],
        ]);

        // Get current locale
        $locale = app()->getLocale();

        return redirect()
            ->route('localized.piggy-banks.show.'.$locale, ['locale' => $locale, 'piggy_id' => $piggyBank->id])
            ->with('status', __('You updated your piggy bank successfully'))
            ->with('success', __('You updated your piggy bank successfully'));
    }

    public function show($piggy_id): View
    {
        // \Log::info('🏦 PiggyBankController::show called', [
        //     'piggy_id' => $piggy_id,
        //     'url' => request()->fullUrl(),
        //     'user_id' => auth()->id(),
        //     'is_authenticated' => auth()->check(),
        //     'app_locale' => app()->getLocale(),
        //     'session_locale' => session('locale'),
        // ]);

        try {
            $piggyBank = PiggyBank::findOrFail($piggy_id);

            // \Log::info('🏦 PiggyBank found', [
            //     'piggy_bank_id' => $piggyBank->id,
            //     'owner_id' => $piggyBank->user_id,
            //     'current_user_id' => auth()->id(),
            // ]);

            if (! Gate::allows('update', $piggyBank)) {
                // \Log::warning('🚫 Gate check failed for piggy bank access', [
                //     'piggy_bank_id' => $piggyBank->id,
                //     'owner_id' => $piggyBank->user_id,
                //     'current_user_id' => auth()->id(),
                // ]);
                abort(403);
            }

            // \Log::info('✅ Gate check passed, rendering view');

            if (request()->has('cancelled')) {
                session()->flash('info', __('edit_cancelled_message'));
            }

            return view('piggy-banks.show', [
                'piggyBank' => $piggyBank,
            ]);
        } catch (\Exception $e) {
            // \Log::error('❌ Error in PiggyBankController::show', [
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            throw $e;
        }
    }

    public function cancel($piggy_id)
    {
        $piggyBank = PiggyBank::findOrFail($piggy_id);

        if (! Gate::allows('update', $piggyBank)) {
            abort(403);
        }

        return redirect()
            ->route('localized.piggy-banks.show', ['locale' => app()->getLocale(), 'piggy_id' => $piggyBank->id])
            ->with('status', __('Changes cancelled'))
            ->with('warning', __('You cancelled editing your piggy bank details.'));
    }

    public function updateStatusToCancelled($piggy_id)
    {
        $piggyBank = PiggyBank::findOrFail($piggy_id);

        if (! Gate::allows('update', $piggyBank)) {
            abort(403);
        }

        // Check if piggy bank can be cancelled
        if (in_array($piggyBank->status, ['done', 'cancelled'])) {
            return response()->json([
                'error' => 'Cannot cancel a completed or already cancelled piggy bank.',
            ], 400);
        }

        $piggyBank->update(['status' => 'cancelled']);

        return response()->json([
            'status' => 'cancelled',
            'message' => __('Piggy bank has been cancelled.'),
        ]);
    }

    public function addOrRemoveMoney(Request $request, $piggy_id)
    {
        $piggyBank = PiggyBank::findOrFail($piggy_id);

        if (! Gate::allows('update', $piggyBank)) {
            abort(403);
        }

        // Validation: Only allow positive amounts, and only allow add/remove types
        $validated = $request->validate([
            'type' => ['required', 'in:manual_add,manual_withdraw'],
            'amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d{1,10}(\.\d{1,2})?$/'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // Negative for withdraw, positive for add
        $signedAmount = $validated['type'] === 'manual_add'
            ? $validated['amount']
            : -$validated['amount'];

        $piggyBank->transactions()->create([
            'user_id' => $piggyBank->user_id,
            'type' => $validated['type'],
            'amount' => $signedAmount,
            'note' => $validated['note'] ?? null,
        ]);

        // Fetch fresh balance/remaining values
        $piggyBank->refresh();

        // Apply the new done/active logic: only mark as done if remaining amount is zero or less
        $remainingZeroOrLess = $piggyBank->remaining_amount <= 0;

        $newStatus = $remainingZeroOrLess ? 'done' : 'active';

        if ($piggyBank->status !== $newStatus) {
            $update = ['status' => $newStatus];
            if ($newStatus === 'done' && ! $piggyBank->actual_completed_at) {
                $update['actual_completed_at'] = now();
            }
            $piggyBank->update($update);
            \Log::info('PiggyBank updated', $update + ['id' => $piggyBank->id]);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $newStatus === 'done'
                    ? __('You have successfully completed your savings goal.')
                    : ($signedAmount > 0
                        ? __('You successfully added money to your piggy bank')
                        : __('You successfully took out some money from your piggy bank')),
                'piggy_bank_status' => $newStatus,
                'translated_status' => __(strtolower($newStatus)),
            ]);
        }

        return redirect(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]))
            ->with('success', $newStatus === 'done'
                ? __('You have successfully completed your savings goal.')
                : __('Money successfully added or removed!'));
    }

    /**
     * Return just the financial summary partial for AJAX reloads.
     */
    public function getFinancialSummary($piggy_id)
    {
        $piggyBank = \App\Models\PiggyBank::findOrFail($piggy_id);

        return view('partials.piggy-bank-financial-summary', compact('piggyBank'))->render();
    }
}
