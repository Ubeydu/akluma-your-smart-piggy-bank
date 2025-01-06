<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SavingScheduleService
{
    /**
     * Generates a complete payment schedule with dates and amounts
     *
     * @param string $targetDate     The target date in Y-m-d format
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
        Log::debug('Starting generateSchedule with inputs', [
            'target_date' => $targetDate,
            'frequency' => $frequency,
            'period_type' => $periodType
        ]);

        // Validate period type first
        $validPeriods = ['days', 'weeks', 'months', 'years'];
        if (!in_array($periodType, $validPeriods)) {
            throw new InvalidArgumentException("Invalid period type: $periodType");
        }

        $startDate = Carbon::tomorrow()->toDateString(); // Get tomorrow's date in Y-m-d format

        $currentDate = Carbon::createFromFormat('Y-m-d', $startDate);

        Log::debug('Schedule start date created', [
            'start_date' => $startDate,
            'current_date' => $currentDate->toDateString()
        ]);

        $schedule = [];
        $dateFormat = $this->getDateFormatPattern($periodType);

        for ($i = 0; $i < $frequency; $i++) {
            if ($i === 0) {
                Log::debug('First schedule entry created', [
                    'payment_number' => 1,
                    'date' => $currentDate->toDateString(),
                    'formatted_date' => $currentDate->locale(App::getLocale())->isoFormat($dateFormat)
                ]);
            }


            $schedule[] = [
                'payment_number' => $i + 1,
                'date' => $currentDate->toDateString(),
                'formatted_date' => $currentDate->locale(App::getLocale())->isoFormat($dateFormat),
                'amount' => $amountDetails['amount']
            ];

            $this->advanceDate($currentDate, $periodType);
        }

        return $schedule;
    }




    private function getDateFormatPattern(string $periodType): string
    {
        return 'L';
    }

    /**
     * Advances the date based on period type
     */
    private function advanceDate(Carbon $date, string $periodType): void
    {
        $methods = [
            'days' => 'addDay',
            'weeks' => 'addWeek',
            'months' => 'addMonth',
            'years' => 'addYear'
        ];

        $date->{$methods[$periodType]}();
    }
}
