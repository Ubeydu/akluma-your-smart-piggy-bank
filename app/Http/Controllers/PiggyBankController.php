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
        \Log::info('Piggy Bank Index Page Loaded', [
            'session_has_newPiggyBankId' => session()->has('newPiggyBankId'),
            'newPiggyBankId' => $newPiggyBankId,
            'newPiggyBankCreatedTime' => $newPiggyBankCreatedTime,
            'url' => request()->fullUrl(),
            'session_id' => session()->getId(),
            'time' => now()->toDateTimeString()
        ]);

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

        return redirect()
            ->route('localized.piggy-banks.show', ['locale' => app()->getLocale(), 'piggy_id' => $piggyBank->id])
            ->with('status', __('You updated your piggy bank successfully'))
            ->with('success', __('You updated your piggy bank successfully'));
    }


    public function show($piggy_id): View
    {
        try {
            $piggyBank = PiggyBank::findOrFail($piggy_id);

            if (! Gate::allows('update', $piggyBank)) {
                \Log::info('Gate check failed');
                abort(403);
            }

            if (request()->has('cancelled')) {
                session()->flash('info', __('edit_cancelled_message'));
            }

            return view('piggy-banks.show', [
                'piggyBank' => $piggyBank
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in show method', ['error' => $e->getMessage()]);
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
                'error' => 'Cannot cancel a completed or already cancelled piggy bank.'
            ], 400);
        }

        $piggyBank->update(['status' => 'cancelled']);

        return response()->json([
            'status' => 'cancelled',
            'message' => __('Piggy bank has been cancelled.')
        ]);
    }


}
