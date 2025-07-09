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

        return view('vaults.show', compact('vault'));
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
            ->route('localized.vaults.show', ['locale' => app()->getLocale(), 'vault_id' => $vault->id])
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
            ->route('localized.vaults.show', ['locale' => app()->getLocale(), 'vault_id' => $vault->id])
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
            ->route('localized.vaults.index', ['locale' => app()->getLocale()])
            ->with('success', __('vault_delete_success_message'));
    }
}
