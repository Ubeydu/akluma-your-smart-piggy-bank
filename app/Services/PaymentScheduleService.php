<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class PaymentScheduleService
{
    /**
     * Generates a complete payment schedule with dates and amounts
     *
     * @param string $targetDate      The date to start generating payments from
     * @param int    $frequency      How many payments to generate
     * @param string $periodType     The type of period (days, weeks, months, years)
     * @param array  $amountDetails  Amount details from calculation service
     * @return array                 Array of scheduled payments with dates and amounts
     */
    // In PaymentScheduleService.php, update the generateSchedule method parameters and initial logic:

    public function generateSchedule(
        string $targetDate,    // Renamed from startDate to make it clear this is the end date
        int $frequency,
        string $periodType,
        array $amountDetails
    ): array {
        $validPeriods = ['days', 'weeks', 'months', 'years'];
        if (!in_array($periodType, $validPeriods)) {
            throw new InvalidArgumentException("Invalid period type: {$periodType}");
        }

        // Start from tomorrow
        $startDate = Carbon::tomorrow();
        $targetDateTime = Carbon::parse($targetDate);

        $schedule = [];
        $amount = $amountDetails['formatted_amount'] . ' ' . $amountDetails['currency'];

        // Calculate the interval between payments by determining the total period
        $totalDays = $startDate->diffInDays($targetDateTime);
        $intervalDays = (int) ceil($totalDays / $frequency);

        $currentDate = $startDate;

        for ($i = 0; $i < $frequency; $i++) {
            $schedule[] = [
                'payment_number' => $i + 1,
                'date' => $currentDate->format('Y-m-d'),
                'amount' => $amount,
                'formatted_date' => $currentDate->format('d.m.Y')
            ];

            // Add the calculated interval
            $currentDate->addDays($intervalDays);
        }

        return $schedule;
    }


}
