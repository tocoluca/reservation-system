<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\DemoExportSeedCommand;
use App\Console\Commands\DemoResetCommand;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reservations:complete-past')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('customers:reflect-visits')
    ->everyMinute()
    ->withoutOverlapping();


Schedule::command('mail:send-revisit-reminders')
    ->dailyAt('12:10')
    ->withoutOverlapping();

Schedule::command('mail:send-reservation-reminders')
    ->dailyAt('12:10')
    ->withoutOverlapping();

Schedule::command('demo:reset')
    ->dailyAt('03:00')
    ->withoutOverlapping();

Schedule::command('mail:send-review-requests')
    ->everyMinute()
    ->withoutOverlapping();

