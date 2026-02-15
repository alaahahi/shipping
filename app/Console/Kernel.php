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

        // ============================================
        // المزامنة في البيئة المحلية (Local) - نمط Git: Pull ثم Push
        // ============================================
        if (env('APP_ENV') === 'local') {
            // مزامنة مجمّعة على نمط Git: 1) Pull (MySQL→SQLite) 2) sync_queue 3) Push (SQLite→MySQL)
            $schedule->call(function () {
                $syncService = app(\App\Services\DatabaseSyncService::class);
                $log = \Illuminate\Support\Facades\Log::class;

                // 1. Pull: سحب التحديثات من السيرفر أولاً
                try {
                    $pull = $syncService->syncFromMySQLToSQLite();
                    $log::info('Auto sync Pull (MySQL → SQLite) completed', [
                        'total_synced' => $pull['total_synced'] ?? 0,
                    ]);
                } catch (\Exception $e) {
                    $log::error('Auto sync Pull failed', ['error' => $e->getMessage()]);
                    return; // لا نكمل إذا فشل Pull
                }

                // 2. مزامنة sync_queue (التغييرات التدريجية)
                try {
                    \Illuminate\Support\Facades\Artisan::call('db:sync-queue', ['--clean' => true]);
                } catch (\Exception $e) {
                    $log::warning('sync-queue warning', ['error' => $e->getMessage()]);
                }

                // 3. Push: رفع التعديلات المحلية للسيرفر
                try {
                    $importantTables = ['car', 'car_contract', 'transactions', 'wallets', 'users'];
                    $push = $syncService->syncFromSQLiteToMySQL($importantTables, false, false, false);
                    $log::info('Auto sync Push (SQLite → MySQL) completed', [
                        'total_synced' => $push['total_synced'] ?? 0,
                    ]);
                } catch (\Exception $e) {
                    $log::error('Auto sync Push failed', ['error' => $e->getMessage()]);
                }
            })
                ->name('sync-git-style-local')
                ->everyFiveMinutes()
                ->withoutOverlapping(10) // منع التداخل لمدة 10 دقائق
                ->appendOutputTo(storage_path('logs/sync.log'));
        }

        // ============================================
        // المزامنة على السيرفر (Server/Production)
        // ============================================
        // على السيرفر: المزامنة تعمل مباشرة بدون sync_queue
        // (لا حاجة لجدول sync_queue لأن التعديلات تحدث مباشرة على MySQL)
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
