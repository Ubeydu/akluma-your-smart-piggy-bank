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

        // Get the value before clearing
        $newPiggyBankId = session('newPiggyBankId');

        // Clear it after getting the value
        session()->forget('newPiggyBankId');

        return view('piggy-banks.index', compact('piggyBanks', 'newPiggyBankId'));
    }


    public function update(Request $request, PiggyBank $piggyBank)
    {
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
            ->route('piggy-banks.show', $piggyBank)
            ->with('status', __('You updated your piggy bank successfully'))
            ->with('success', __('You updated your piggy bank successfully'));
    }


    public function show(PiggyBank $piggyBank): View
    {
        if (! Gate::allows('update', $piggyBank)) {
            abort(403);
        }

        if (request()->has('cancelled')) {
            session()->flash('info', __('edit_cancelled_message'));
        }

        return view('piggy-banks.show', [
            'piggyBank' => $piggyBank
        ]);
    }


    public function cancel(PiggyBank $piggyBank)
    {
        if (! Gate::allows('update', $piggyBank)) {
            abort(403);
        }

        return redirect()
            ->route('piggy-banks.show', $piggyBank)
            ->with('status', __('Changes cancelled'))
            ->with('warning', __('You cancelled editing your piggy bank details.'));
    }

    public function pause(PiggyBank $piggyBank)
    {
        try {
            $piggyBank->update(['status' => 'paused']);

            return response()->json([
                'status' => 'paused',
                'message' => __('Piggy bank has been paused successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to pause piggy bank.')
            ], 500);
        }
    }

    public function resume(PiggyBank $piggyBank)
    {
        try {
            $piggyBank->update(['status' => 'active']);

            return response()->json([
                'status' => 'active',
                'message' => __('Piggy bank has been activated successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to activate piggy bank.')
            ], 500);
        }
    }


    public function updateStatusToCancelled(PiggyBank $piggyBank)
    {
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
