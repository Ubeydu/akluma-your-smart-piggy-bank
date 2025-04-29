<?php

use App\Console\Commands\RetryFailedReminders;
use App\Console\Commands\SendSavingReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('logs:clear', function () {
    $logFile = storage_path('logs/laravel.log'); // Specify the file path

    if (file_exists($logFile)) {
        file_put_contents($logFile, ''); // Clear the file content
        $this->comment('The content of storage/logs/laravel.log has been cleared!');
    } else {
        $this->error('The file storage/logs/laravel.log does not exist.');
    }
})->describe('Clear the content of storage/logs/laravel.log');


// Scheduling
Schedule::command(SendSavingReminders::class)
    ->everyFiveMinutes()
    ->before(function () {
        Log::info('🕒 schedule:run is executing (SendSavingReminders)');
    })
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->description('Send saving reminders to users (every five minutes)');

Schedule::command(RetryFailedReminders::class)
    ->everyTenMinutes()
    ->before(function () {
        Log::info('🕒 schedule:run is executing (RetryFailedReminders)');
    })
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->description('Retry failed saving reminders (every ten minutes)');

