<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount([
                'piggyBanks',
                'piggyBanks as active_piggy_banks_count' => fn ($q) => $q->where('status', 'active'),
                'vaults',
                'piggyBanks as connected_piggy_banks_count' => fn ($q) => $q->whereNotNull('vault_id')->where('status', 'active'),
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $userCounts = User::query()->selectRaw(
            'COUNT(*) as total, COUNT(CASE WHEN suspended_at IS NULL THEN 1 END) as active'
        )->first();

        return view('admin.users.index', [
            'users' => $users,
            'totalUsers' => $userCounts->total,
            'activeUsers' => $userCounts->active,
        ]);
    }

    public function show(User $user): View
    {
        $user->loadCount([
            'piggyBanks',
            'piggyBanks as active_piggy_banks_count' => fn ($q) => $q->where('status', 'active'),
            'vaults',
            'piggyBanks as connected_piggy_banks_count' => fn ($q) => $q->whereNotNull('vault_id')->where('status', 'active'),
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $user->suspended_at = now();
        $user->save();

        return back()->with('success', "User {$user->name} has been suspended.");
    }

    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot unsuspend your own account.');
        }

        $user->suspended_at = null;
        $user->save();

        return back()->with('success', "User {$user->name} has been unsuspended.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} has been deleted.");
    }
}
