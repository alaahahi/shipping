<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseSyncService;
use App\Services\SyncQueueService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SyncQueue extends Command
{
    protected $signature = 'db:sync-queue 
                            {--clean : حذف السجلات المزامنة (أقدم من 24 ساعة)}
                            {--retry : إعادة محاولة السجلات الفاشلة}';

    protected $description = 'مزامنة التغييرات من sync_queue إلى MySQL';

    protected $syncService;
    protected $queueService;

    public function __construct(DatabaseSyncService $syncService, SyncQueueService $queueService)
    {
        parent::__construct();
        $this->syncService = $syncService;
        $this->queueService = $queueService;
    }

    public function handle()
    {
        // هذا الأمر يعمل فقط في البيئة المحلية (Local)
        // على السيرفر: المزامنة تعمل مباشرة بدون sync_queue
        if (env('APP_ENV') === 'server' || env('APP_ENV') === 'production') {
            $this->warn("⚠️  هذا الأمر يعمل فقط في البيئة المحلية.");
            $this->info("💡 على السيرفر: المزامنة تعمل مباشرة بدون sync_queue.");
            return 0; // لا نعتبره خطأ، فقط معلومات
        }

        // التحقق من وجود جدول sync_queue
        if (!Schema::hasTable('sync_queue')) {
            $this->warn("⚠️  جدول sync_queue غير موجود.");
            $this->info("💡 نفّذ: php artisan migrate --path=database/migrations/2025_12_08_150000_create_sync_queue_table.php");
            return 0;
        }

        // إعادة محاولة السجلات الفاشلة
        if ($this->option('retry')) {
            $retried = $this->queueService->retryFailed(3);
            $this->info("🔄 تم إعادة تفعيل {$retried} سجل فاشل.");
            $this->newLine();
        }

        $this->info("🔄 بدء مزامنة التغييرات من sync_queue إلى MySQL...");
        $this->newLine();

        try {
            $results = $this->syncService->syncFromQueue();

            $this->displayResults($results);

            // تنظيف السجلات المزامنة
            if ($this->option('clean')) {
                $cleaned = $this->queueService->cleanSyncedRecords(24);
                $this->newLine();
                $this->info("🧹 تم حذف {$cleaned} سجل مزامن (أقدم من 24 ساعة).");
            }

        } catch (\Exception $e) {
            $this->error("❌ فشلت عملية المزامنة: " . $e->getMessage());
            Log::error('Sync queue command failed', [
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
        
        if ($results['queue_processed'] > 0) {
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
            $this->info("📋 إجمالي السجلات المعالجة: " . $results['queue_processed']);
        } else {
            $this->info("ℹ️  لا توجد تغييرات معلقة للمزامنة.");
        }
    }
}

