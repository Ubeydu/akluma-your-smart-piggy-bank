<?php

namespace App\Http\Controllers;

use App\Models\PiggyBank;
use App\Services\DashboardStatsService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardService = new DashboardStatsService;

        // Calculate fresh stats
        $leftToSave = $dashboardService->calculateLeftToSave(auth()->id());
        $progressPercentages = $dashboardService->calculateProgressPercentages(auth()->id());

        // Get piggy bank status counts for the authenticated user
        $statusCounts = PiggyBank::where('user_id', auth()->id())
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Make sure all statuses have a value (default 0)
        $statusCounts = array_merge([
            'active' => 0,
            'paused' => 0,
            'done' => 0,
            'cancelled' => 0,
        ], $statusCounts);

        // Get recent activity (last 3 scheduled savings)
        $recentActivity = DB::table('scheduled_savings')
            ->join('piggy_banks', 'scheduled_savings.piggy_bank_id', '=', 'piggy_banks.id')
            ->where('piggy_banks.user_id', auth()->id())
            ->where('scheduled_savings.status', 'saved')
            ->orderBy('scheduled_savings.saving_date', 'desc')
            ->select('piggy_banks.name', 'scheduled_savings.amount', 'scheduled_savings.saving_date', 'scheduled_savings.status')
            ->limit(3)
            ->get();

        // Get upcoming payments
        $upcomingPayments = DB::table('scheduled_savings')
            ->join('piggy_banks', 'scheduled_savings.piggy_bank_id', '=', 'piggy_banks.id')
            ->where('piggy_banks.user_id', auth()->id())
            ->where('scheduled_savings.status', 'pending')
            ->where('piggy_banks.status', 'active')
            ->orderBy('scheduled_savings.saving_date', 'asc')
            ->select('piggy_banks.name', 'scheduled_savings.amount', 'scheduled_savings.saving_date')
            ->limit(3)
            ->get();

        return view('dashboard', compact('statusCounts', 'recentActivity', 'upcomingPayments', 'leftToSave', 'progressPercentages'));
    }
}
