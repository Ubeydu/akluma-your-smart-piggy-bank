<?php

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


// Register the saving reminders command to run daily at midnight UTC
Schedule::command('app:send-saving-reminders')
    ->dailyAt('00:00')
    ->description('Send saving reminders to users');

// Retry failed reminders at noon UTC
Schedule::command('app:retry-failed-reminders')
    ->dailyAt('12:00')
    ->description('Retry failed saving reminders');

