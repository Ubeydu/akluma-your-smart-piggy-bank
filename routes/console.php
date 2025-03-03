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



Schedule::exec('php artisan app:send-saving-reminders --force --date=2025-03-04')
    ->dailyAt('14:08')
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->description('Send saving reminders to users');


Schedule::command(RetryFailedReminders::class)->dailyAt('09:00')
    ->description('Retry failed saving reminders');

