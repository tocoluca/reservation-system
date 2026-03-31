<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\DemoExportSeedCommand;
use App\Console\Commands\DemoResetCommand;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('mail:send-revisit-reminders')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('customers:reflect-visits')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('mail:send-reservation-reminders')
    ->everyMinute()
    ->withoutOverlapping();