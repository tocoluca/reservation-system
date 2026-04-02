<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DemoResetCommand;
use App\Console\Commands\DemoExportSeedCommand;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        DemoResetCommand::class,
        DemoExportSeedCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('demo:reset')
            ->dailyAt('03:00')
            ->withoutOverlapping();

        $schedule->command('mail:send-reservation-reminders')
            ->everyMinute()
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}