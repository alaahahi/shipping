<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // التحقق من الترخيص دورياً (كل ساعة)
        if (config('license.enabled')) {
            $schedule->command('license:verify')
                ->hourly()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/license.log'));
        }

        // تعطيل المزامنة على السيرفر - تعمل فقط في البيئة المحلية
        if (env('APP_ENV') === 'server' || env('APP_ENV') === 'production') {
            return;
        }

        // مزامنة من MySQL إلى SQLite كل 5 دقائق (فقط في البيئة المحلية)
        $schedule->command('db:sync --direction=down')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sync.log'));

        // مزامنة من SQLite إلى MySQL كل 10 دقائق (فقط في البيئة المحلية)
        $schedule->command('db:sync --direction=up')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sync.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
