<?php

namespace App\Services;

use App\Models\PiggyBank;
use App\Models\UserDashboardStat;

class DashboardStatsService
{
    public function calculateLeftToSave($userId): array
    {
        // Get all active piggy banks for user
        $piggyBanks = PiggyBank::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $currencyBreakdown = [];
        $currencyCounts = [];

        foreach ($piggyBanks as $piggyBank) {
            $leftToSave = $piggyBank->remaining_amount;

            if ($leftToSave > 0) {
                $currency = $piggyBank->currency;
                $currencyBreakdown[$currency] = ($currencyBreakdown[$currency] ?? 0) + $leftToSave;
                $currencyCounts[$currency] = ($currencyCounts[$currency] ?? 0) + 1;
            }
        }

        // Store the stat
        UserDashboardStat::updateOrCreate(
            [
                'user_id' => $userId,
                'stat_type' => 'left_to_save',
                'period' => 'current',
            ],
            [
                'currency_breakdown' => $currencyBreakdown,
                'calculated_at' => now(),
            ]
        );

        return [
            'amounts' => $currencyBreakdown,
            'counts' => $currencyCounts
        ];
    }

    public function calculateProgressPercentages($userId): array
    {
        // Get all active piggy banks for user
        $piggyBanks = PiggyBank::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $currencyData = [];

        foreach ($piggyBanks as $piggyBank) {
            $currency = $piggyBank->currency;
            $totalSaved = $piggyBank->actual_final_total_saved;
            $totalGoal = $piggyBank->final_total;

            if (! isset($currencyData[$currency])) {
                $currencyData[$currency] = ['saved' => 0, 'goal' => 0];
            }

            $currencyData[$currency]['saved'] += $totalSaved;
            $currencyData[$currency]['goal'] += $totalGoal;
        }

        $percentages = [];
        foreach ($currencyData as $currency => $data) {
            $percentage = $data['goal'] > 0
                ? round(($data['saved'] / $data['goal']) * 100, 1)
                : 0;

            $percentages[$currency] = max(0, min(100, $percentage));
        }

        return $percentages;
    }
}
