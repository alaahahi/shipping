<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseSyncService;
use Illuminate\Support\Facades\Log;

class SyncFromServer extends Command
{
    protected $signature = 'db:sync-from-server 
                            {--table= : جدول محدد للمزامنة}
                            {--all : مزامنة جميع الجداول}';

    protected $description = 'مزامنة البيانات من MySQL (السيرفر) إلى SQLite (المحلي)';

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
            // جداول مهمة للمزامنة
            $tablesArray = ['car', 'car_contract', 'transactions', 'wallets', 'users'];
        }

        $this->info("🔄 بدء مزامنة البيانات من MySQL (السيرفر) إلى SQLite (المحلي)...");
        $this->newLine();

        try {
            // forceFullSync=true لضمان مزامنة جميع السجلات بما فيها التحديثات
            // هذا يضمن تحديث السجلات الموجودة أيضاً
            $results = $this->syncService->syncFromMySQLToSQLite($tablesArray, true);

            $this->displayResults($results);
            
            if ($results['total_synced'] == 0 && !empty($results['success'])) {
                $this->warn("⚠️  لا توجد سجلات جديدة أو محدثة. قد تكون البيانات متطابقة بالفعل.");
                $this->info("💡 نصيحة: إذا كنت تريد فرض المزامنة الكاملة، استخدم: php artisan db:sync --direction=down --force");
            }

        } catch (\Exception $e) {
            $this->error("❌ فشلت عملية المزامنة: " . $e->getMessage());
            Log::error('Sync from server command failed', [
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

