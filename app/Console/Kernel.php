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
        // المزامنة في البيئة المحلية (Local)
        // ============================================
        if (env('APP_ENV') === 'local') {
            // مزامنة من MySQL إلى SQLite كل 5 دقائق (فقط في البيئة المحلية)
            $schedule->call(function () {
                try {
                    $syncService = app(\App\Services\DatabaseSyncService::class);
                    $results = $syncService->syncFromMySQLToSQLite();
                    
                    \Illuminate\Support\Facades\Log::info('Auto sync MySQL → SQLite completed (Local)', [
                        'total_synced' => $results['total_synced'] ?? 0,
                        'success_count' => count($results['success'] ?? []),
                        'failed_count' => count($results['failed'] ?? [])
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Auto sync MySQL → SQLite failed (Local)', [
                        'error' => $e->getMessage()
                    ]);
                }
            })
                ->name('sync-mysql-to-sqlite-local')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/sync.log'));

            // مزامنة من sync_queue إلى MySQL كل 5 دقائق (فقط في البيئة المحلية)
            // تعمل في الخلفية تلقائياً - مزامنة التغييرات المحلية فقط
            $schedule->command('db:sync-queue --clean')
                ->name('sync-queue-to-mysql-local')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/sync.log'));

            // مزامنة احتياطية من SQLite إلى MySQL كل 10 دقائق (فقط في البيئة المحلية)
            $schedule->call(function () {
                try {
                    $syncService = app(\App\Services\DatabaseSyncService::class);
                    $importantTables = ['car', 'car_contract', 'transactions', 'wallets', 'users'];
                    $results = $syncService->syncFromSQLiteToMySQL($importantTables, false, false, false);
                    
                    \Illuminate\Support\Facades\Log::info('Auto sync SQLite → MySQL completed (Local Backup)', [
                        'total_synced' => $results['total_synced'] ?? 0,
                        'success_count' => count($results['success'] ?? []),
                        'failed_count' => count($results['failed'] ?? []),
                        'tables' => $importantTables
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Auto sync SQLite → MySQL failed (Local Backup)', [
                        'error' => $e->getMessage()
                    ]);
                }
            })
                ->name('sync-sqlite-to-mysql-local-backup')
                ->everyTenMinutes()
                ->withoutOverlapping()
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
