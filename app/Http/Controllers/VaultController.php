<?php

namespace App\Http\Controllers;

use App\Models\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VaultController extends Controller
{
    public function index(): View
    {
        $vaults = auth()->user()->vaults()->get();

        return view('vaults.index', compact('vaults'));
    }

    public function show($vault_id): View
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('view', $vault)) {
            abort(403);
        }

        // Get unconnected piggy banks for the dropdown
        $unconnectedPiggyBanks = auth()->user()->piggyBanks()
            ->whereNull('vault_id')
            ->get();

        $formattedPiggyBanks = $unconnectedPiggyBanks->filter(function ($pb) {
            return ! in_array($pb->status, ['done', 'cancelled']);
        })->map(function ($pb) {
            return [
                'id' => $pb->id,
                'name' => $pb->name,
                'amount' => number_format($pb->actual_final_total_saved, 2),
                'currency' => $pb->currency,
                'display' => $pb->name.' #'.$pb->id.' ('.number_format($pb->actual_final_total_saved, 2).' '.$pb->currency.')',
            ];
        });

        return view('vaults.show', compact('vault', 'unconnectedPiggyBanks', 'formattedPiggyBanks'));
    }

    public function create(): View
    {
        return view('vaults.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'details' => 'nullable|string|max:5000',
        ]);

        $vault = auth()->user()->vaults()->create($validated);

        return redirect()
            ->to(localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]))
            ->with('success', __('vault_create_success_message'));
    }

    public function edit($vault_id): View
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('update', $vault)) {
            abort(403);
        }

        return view('vaults.edit', compact('vault'));
    }

    public function update(Request $request, $vault_id): RedirectResponse
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('update', $vault)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'details' => 'nullable|string|max:5000',
        ]);

        $vault->update($validated);

        return redirect()
            ->to(localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]))
            ->with('success', __('vault_update_success_message'));
    }

    public function destroy($vault_id): RedirectResponse
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('delete', $vault)) {
            abort(403);
        }

        $vault->delete();

        return redirect()
            ->to(localizedRoute('localized.vaults.index'))
            ->with('success', __('vault_delete_success_message'));
    }

    public function connectPiggyBank(Request $request, $vault_id): RedirectResponse
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('update', $vault)) {
            abort(403);
        }

        $validated = $request->validate([
            'piggy_bank_id' => 'required|exists:piggy_banks,id',
        ]);

        // Additional validation: ensure piggy bank belongs to user
        $piggyBank = auth()->user()->piggyBanks()->find($validated['piggy_bank_id']);
        if (! $piggyBank) {
            abort(403, 'Piggy bank does not belong to user');
        }

        // Check if piggy bank is already connected to any vault
        if ($piggyBank->vault_id !== null) {
            if ($piggyBank->vault_id === $vault->id) {
                $errorMessage = __('This piggy bank is already connected to this vault.');
            } else {
                $errorMessage = __('vault_piggy_bank_already_connected_message');
            }

            return redirect()
                ->to(localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]))
                ->with('error', $errorMessage);
        }

        // Connect piggy bank to vault
        $piggyBank->update(['vault_id' => $vault->id]);

        return redirect()
            ->to(localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]))
            ->with('success', __('vault_piggy_bank_connect_success_message'));
    }

    public function disconnectPiggyBank(Request $request, $vault_id): RedirectResponse
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('update', $vault)) {
            abort(403);
        }

        $validated = $request->validate([
            'piggy_bank_id' => 'required|exists:piggy_banks,id',
        ]);

        // Additional validation: ensure piggy bank belongs to user and is connected to this vault
        $piggyBank = auth()->user()->piggyBanks()->where('vault_id', $vault->id)->find($validated['piggy_bank_id']);
        if (! $piggyBank) {
            abort(403, 'Piggy bank does not belong to user or is not connected to this vault');
        }

        // Disconnect piggy bank from vault
        $piggyBank->update(['vault_id' => null]);

        return redirect()
            ->to(localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]))
            ->with('success', __('vault_piggy_bank_disconnect_success_message'));
    }

    public function cancel($vault_id): RedirectResponse
    {
        $vault = Vault::findOrFail($vault_id);

        if (! Gate::allows('update', $vault)) {
            abort(403);
        }

        return redirect()
            ->to(localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]))
            ->with('status', __('Changes cancelled'))
            ->with('warning', __('You cancelled editing your vault. Your changes haven\'t been saved.'));
    }
}
