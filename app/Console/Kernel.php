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
        // المزامنة التلقائية في البيئة المحلية (Local)
        // تعمل كل 5 دقائق عند توفر الإنترنت - لا تتوقف أبداً
        // ============================================
        if (env('APP_ENV') === 'local') {
            $schedule->call(function () {
                $log = \Illuminate\Support\Facades\Log::class;
                $statusFile = storage_path('app/sync_auto_status.json');
                $excludedForPush = ['migrations', 'sync_metadata', 'sync_queue', 'sync_jobs', 'personal_access_tokens', 'licenses', 'jobs', 'job_batches', 'failed_jobs', 'sessions', 'cache', 'cache_locks'];

                $writeStatus = function ($ok, $pullSynced = 0, $pushSynced = 0, $error = null) use ($statusFile) {
                    @file_put_contents($statusFile, json_encode([
                        'last_run' => now()->toIso8601String(),
                        'ok' => $ok,
                        'pull_synced' => $pullSynced,
                        'push_synced' => $pushSynced,
                        'error' => $error,
                        'running' => false,
                    ], JSON_UNESCAPED_UNICODE));
                };

                $pullSynced = 0;
                $pushSynced = 0;
                $lastError = null;

                // التحقق من توفر الإنترنت/السيرفر قبل المزامنة
                $onlineReachable = false;
                $onlineUrl = rtrim(env('ONLINE_URL', ''), '/');
                if (!empty($onlineUrl)) {
                    try {
                        $parsed = parse_url($onlineUrl);
                        $host = $parsed['host'] ?? $parsed['path'] ?? null;
                        $port = $parsed['port'] ?? (isset($parsed['scheme']) && $parsed['scheme'] === 'https' ? 443 : 80);
                        if ($host && @fsockopen($host, $port, $errno, $errstr, 3)) {
                            $onlineReachable = true;
                        }
                    } catch (\Throwable $e) {
                        // لا إنترنت - نُخرج بدون تسجيل خطأ (طبيعي)
                    }
                } else {
                    // لا ONLINE_URL - نعتبر الاتصال متوفراً (MySQL محلي مباشر)
                    $onlineReachable = true;
                }

                if (!$onlineReachable) {
                    $writeStatus(null, 0, 0, null);
                    return;
                }

                @file_put_contents($statusFile, json_encode([
                    'last_run' => now()->toIso8601String(),
                    'ok' => null,
                    'pull_synced' => 0,
                    'push_synced' => 0,
                    'error' => null,
                    'running' => true,
                ], JSON_UNESCAPED_UNICODE));

                $syncService = app(\App\Services\DatabaseSyncService::class);

                // 1. Pull: سحب التحديثات (لا تتوقف على فشل خطوة واحدة)
                try {
                    if (env('LOCAL_NO_REMOTE', false)) {
                        $controller = app(\App\Http\Controllers\SyncMonitorController::class);
                        $pull = $controller->syncFromOnlineServer(null, false);
                    } else {
                        $pull = $syncService->syncFromMySQLToSQLite(null, false);
                    }
                    $pullSynced = $pull['total_synced'] ?? 0;
                    $log::info('Auto sync Pull completed', ['total_synced' => $pullSynced]);
                } catch (\Exception $e) {
                    $lastError = 'Pull: ' . $e->getMessage();
                    $log::warning('Auto sync Pull failed (continuing)', ['error' => $e->getMessage()]);
                }

                // 2. مزامنة sync_queue
                try {
                    \Illuminate\Support\Facades\Artisan::call('db:sync-queue', ['--clean' => true]);
                } catch (\Exception $e) {
                    $log::debug('sync-queue skip', ['error' => $e->getMessage()]);
                }

                // 3. Push: رفع التعديلات المحلية
                try {
                    $sqliteDb = DB::connection('sync_sqlite');
                    $rows = $sqliteDb->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                    $sqliteTables = array_map(fn ($r) => $r->name, $rows);
                    $importantTables = ['car', 'car_contract', 'car_contract_images', 'transactions', 'transactions_contract', 'wallets', 'users'];
                    $tablesToPush = array_values(array_diff(
                        array_intersect($importantTables, $sqliteTables),
                        $excludedForPush
                    ));

                    if (!empty($tablesToPush)) {
                        $push = $syncService->syncFromSQLiteToMySQL($tablesToPush, true, false, false);
                        $pushSynced = $push['total_synced'] ?? 0;
                        $log::info('Auto sync Push completed', ['total_synced' => $pushSynced, 'tables' => $tablesToPush]);
                    }
                } catch (\Exception $e) {
                    $lastError = ($lastError ? $lastError . ' | ' : '') . 'Push: ' . $e->getMessage();
                    $log::warning('Auto sync Push failed', ['error' => $e->getMessage()]);
                }

                $writeStatus(empty($lastError), $pullSynced, $pushSynced, $lastError);
            })
                ->name('sync-git-style-local')
                ->everyFiveMinutes()
                ->withoutOverlapping(8)
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
