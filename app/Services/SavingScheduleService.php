<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class SavingScheduleService
{
    /**
     * Generates a complete payment schedule with dates and amounts
     *
     * @param string $targetDate     The target date (will be parsed as UTC)
     * @param int    $frequency      How many payments to generate
     * @param string $periodType     The type of period (days, weeks, months, years)
     * @param array  $amountDetails  Amount details from calculation service
     * @return array                Array of scheduled payments with dates and amounts
     */
    public function generateSchedule(
        string $targetDate,
        int $frequency,
        string $periodType,
        array $amountDetails
    ): array {
        // Validate period type first
        $validPeriods = ['hours', 'days', 'weeks', 'months', 'years'];
        if (!in_array($periodType, $validPeriods)) {
            throw new InvalidArgumentException("Invalid period type: $periodType");
        }

        // Debug logging
        \Log::info('Generate Schedule Debug', [
            'targetDate' => $targetDate,
            'periodType' => $periodType,
            'initialStartDate' => Carbon::parse($targetDate)->utc(),
            'now' => Carbon::now()->utc(),
        ]);

        \Log::info('Target Date Before Parsing', ['targetDate' => $targetDate]);

        $startDate = Carbon::tomorrow(); // Always start schedule the next day from "today"

        // Set initial time based on frequency type (in UTC)
        $this->setInitialTime($startDate, $periodType);

        // Debug logging after initial time set
        \Log::info('After Initial Time Set', [
            'startDate' => $startDate,
            'periodType' => $periodType,
        ]);

        $schedule = [];
        $currentDate = clone $startDate;

        // Get the date format pattern for display purposes
        $dateFormat = $this->getDateFormatPattern($periodType);

        for ($i = 0; $i < $frequency; $i++) {
            // Store both UTC and formatted versions of the date
            $schedule[] = [
                'payment_number' => $i + 1,
                'date' => $periodType === 'hours'
                    ? clone $currentDate                     // Keep full datetime for hourly
                    : (clone $currentDate)->startOfDay(),    // Strip time info for other frequencies
                'amount' => $amountDetails['amount'],
                'formatted_date' => (clone $currentDate)
                    ->setTimezone(config('app.timezone'))
                    ->locale(App::getLocale())
                    ->isoFormat($dateFormat)
            ];

            $this->advanceDate($currentDate, $periodType);
        }

        return $schedule;
    }

    /**
     * Sets the initial time for different frequency types
     */
    private function setInitialTime(Carbon $date, string $periodType): void
    {
        // For hours, set specific time at 10:00
        if ($periodType === 'hours') {
            $date->setTime(10, 0, 0);
        }
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
