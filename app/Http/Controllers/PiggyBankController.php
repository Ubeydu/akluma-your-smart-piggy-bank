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
            ->route('piggy-banks.index')
            ->with('status', __('Changes cancelled'))
            ->with('warning', __('You cancelled editing your piggy bank details.'));
    }

}
