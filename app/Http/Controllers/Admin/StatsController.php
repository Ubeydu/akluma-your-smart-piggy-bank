<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PiggyBank;
use App\Models\ScheduledSaving;
use App\Models\User;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'suspended_users' => User::whereNotNull('suspended_at')->count(),
            'total_piggy_banks' => PiggyBank::count(),
            'active_piggy_banks' => PiggyBank::where('status', 'active')->count(),
            'total_scheduled_savings' => ScheduledSaving::count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.stats', compact('stats'));
    }
}
