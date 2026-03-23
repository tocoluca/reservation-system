<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\DemoExportSeedCommand;
use App\Console\Commands\DemoResetCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::starting(function ($artisan) {
    $artisan->resolveCommands([
        DemoExportSeedCommand::class,
        DemoResetCommand::class,
    ]);
});
