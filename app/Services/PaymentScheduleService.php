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

    public function generateSchedule(
        string $targetDate,    // Renamed from startDate to make it clear this is the end date
        int $frequency,
        string $periodType,
        array $amountDetails
    ): array {
        $validPeriods = ['hours', 'days', 'weeks', 'months', 'years'];
        if (!in_array($periodType, $validPeriods)) {
            throw new InvalidArgumentException("Invalid period type: $periodType");
        }

        // Start from tomorrow
        $startDate = Carbon::tomorrow();

        if ($periodType === 'hours') {
            $startDate->setTime(11, 0, 0);  // Set start time to 11:00 AM
        }

        $schedule = [];
        $amount = $amountDetails['formatted_amount'] . ' ' . $amountDetails['currency'];

        $currentDate = $startDate;

        for ($i = 0; $i < $frequency; $i++) {
            if ($periodType === 'hours') {
                $schedule[] = [
                    'payment_number' => $i + 1,
                    'date' => $currentDate->format('Y-m-d H:i:s'),
                    'amount' => $amount,
                    'formatted_date' => $currentDate->format('d.m.Y H:i')  // Show hours and minutes for hourly frequency
                ];
            } else {
                // For all other frequencies, set time to 10:00
                $currentDate->setTime(10, 0, 0);
                $schedule[] = [
                    'payment_number' => $i + 1,
                    'date' => $currentDate->format('Y-m-d H:i:s'),
                    'amount' => $amount,
                    'formatted_date' => $currentDate->format('d.m.Y')  // Keep just the date for daily and longer frequencies
                ];
            }

//            // Add the calculated interval
//            $currentDate->addDays($intervalDays);

            switch ($periodType) {
                case 'hours':
                    $currentDate->addHour();
                    break;
                case 'weeks':
                    $currentDate->addWeek();
                    break;
                case 'days':
                    $currentDate->addDay();
                    break;
                case 'months':
                    $currentDate->addMonth();
                    break;
                case 'years':
                    $currentDate->addYear();
                    break;
            }

        }

        return $schedule;
    }


}
