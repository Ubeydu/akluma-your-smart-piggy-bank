<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class PaymentScheduleService
{
    /**
     * Generates a complete payment schedule with dates and amounts
     *
     * @param string $targetDate    The date to start generating payments from
     * @param int    $frequency     How many payments to generate
     * @param string $periodType    The type of period (days, weeks, months, years)
     * @param array  $amountDetails Amount details from calculation service
     * @return array               Array of scheduled payments with dates and amounts
     */
    public function generateSchedule(
        string $targetDate,
        int $frequency,
        string $periodType,
        array $amountDetails
    ): array {
        $validPeriods = ['hours', 'days', 'weeks', 'months', 'years'];
        if (!in_array($periodType, $validPeriods)) {
            throw new InvalidArgumentException("Invalid period type: $periodType");
        }

        // Start from tomorrow and set the locale for date formatting
        $startDate = Carbon::tomorrow()->locale(App::getLocale());

        // Set initial time based on frequency type
        $this->setInitialTime($startDate, $periodType);

        $schedule = [];
        $currentDate = clone $startDate;

        // Get the date format pattern based on frequency type
        $dateFormat = $this->getDateFormatPattern($periodType);

        for ($i = 0; $i < $frequency; $i++) {
            $schedule[] = [
                'payment_number' => $i + 1,
                'date' => $currentDate->format('Y-m-d H:i:s'),  // Store standardized format for processing
                'amount' => $amountDetails['amount'],   // Use pre-formatted value
                'formatted_date' => $currentDate->isoFormat($dateFormat)  // Use locale-aware formatting
            ];

            $this->advanceDate($currentDate, $periodType);
        }

        return $schedule;
    }

    /**
     * Sets the initial time of day based on frequency type
     */
    private function setInitialTime(Carbon $date, string $periodType): void
    {
        $time = $periodType === 'hours' ? 11 : 10;
        $date->setTime($time, 0, 0);
    }

    /**
     * Gets the appropriate date format pattern based on frequency type
     */
    private function getDateFormatPattern(string $periodType): string
    {
        // Use ISO format patterns for locale-aware date formatting
        // L = locale's date format
        // LT = locale's time format
        return $periodType === 'hours' ? 'L LT' : 'L';
    }

    /**
     * Advances the date based on period type
     */
    private function advanceDate(Carbon $date, string $periodType): void
    {
        $methods = [
            'hours' => 'addHour',
            'days' => 'addDay',
            'weeks' => 'addWeek',
            'months' => 'addMonth',
            'years' => 'addYear'
        ];

        $date->{$methods[$periodType]}();
    }
}
