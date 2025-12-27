<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseSyncService;
use Illuminate\Support\Facades\Log;

class SyncUpdates extends Command
{
    protected $signature = 'db:sync-updates 
                            {--table= : جدول محدد للمزامنة}
                            {--all : مزامنة جميع الجداول}';

    protected $description = 'مزامنة التحديثات من SQLite إلى MySQL (يسمح بالتحديثات)';

    protected $syncService;

    public function __construct(DatabaseSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    public function handle()
    {
        // تعطيل المزامنة على السيرفر
        if (env('APP_ENV') === 'server' || env('APP_ENV') === 'production') {
            $this->error("❌ المزامنة معطلة على السيرفر. تعمل فقط في البيئة المحلية.");
            return 1;
        }

        $table = $this->option('table');
        $all = $this->option('all');

        $tablesArray = null;
        if ($table) {
            $tablesArray = [$table];
        } elseif ($all) {
            $tablesArray = null; // جميع الجداول
        } else {
            // جداول مهمة للتحديثات
            $tablesArray = ['car', 'car_contract', 'transactions', 'wallets', 'users'];
        }

        $this->info("🔄 بدء مزامنة التحديثات من SQLite إلى MySQL...");
        $this->newLine();
        $this->warn("⚠️  Safe Mode: OFF - سيتم تحديث السجلات الموجودة");

        try {
            // safe_mode=false للسماح بالتحديثات
            // createBackup=true لحماية البيانات
            $results = $this->syncService->syncFromSQLiteToMySQL(
                $tablesArray,
                false, // safe_mode = false (يسمح بالتحديثات)
                true,  // createBackup = true (نسخة احتياطية)
                false  // forceFullSync = false
            );

            $this->displayResults($results);

            if (isset($results['backup_file'])) {
                $this->info("💾 النسخة الاحتياطية: " . $results['backup_file']);
            }

        } catch (\Exception $e) {
            $this->error("❌ فشلت عملية المزامنة: " . $e->getMessage());
            Log::error('Sync updates command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    protected function displayResults(array $results)
    {
        $this->newLine();
        $this->info("✅ الجداول المزامنة بنجاح:");
        
        foreach ($results['success'] as $table => $count) {
            $this->line("  ✓ {$table}: {$count} سجل");
        }

        if (!empty($results['failed'])) {
            $this->newLine();
            $this->error("❌ الجداول التي فشلت:");
            foreach ($results['failed'] as $table => $error) {
                $this->line("  ✗ {$table}: {$error}");
            }
        }

        $this->newLine();
        $this->info("📊 إجمالي السجلات المزامنة: " . $results['total_synced']);
    }
}

