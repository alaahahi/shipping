<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheOptimizationService;
use Illuminate\Support\Facades\Artisan;

class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:optimize 
                            {--cache : تخزين مؤقت للتكوين}
                            {--clear : مسح كل الـ Cache}
                            {--warmup : تحميل البيانات المهمة مسبقاً}
                            {--info : عرض معلومات الأداء}
                            {--benchmark : اختبار أداء الـ Cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحسين أداء التطبيق وإدارة الـ Cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء تحسين الأداء...');
        $this->newLine();

        // إذا لم يتم تحديد أي خيار، نفذ الكل
        $noOptions = !$this->option('cache') 
                    && !$this->option('clear') 
                    && !$this->option('warmup') 
                    && !$this->option('info')
                    && !$this->option('benchmark');

        if ($this->option('clear') || $noOptions) {
            $this->clearAllCaches();
        }

        if ($this->option('cache') || $noOptions) {
            $this->cacheConfigurations();
        }

        if ($this->option('warmup')) {
            $this->warmupCache();
        }

        if ($this->option('info')) {
            $this->showInfo();
        }

        if ($this->option('benchmark')) {
            $this->runBenchmark();
        }

        $this->newLine();
        $this->info('✅ تم تحسين الأداء بنجاح!');
        
        return Command::SUCCESS;
    }

    /**
     * مسح كل أنواع الـ Cache
     */
    protected function clearAllCaches()
    {
        $this->info('🗑️  مسح الـ Cache...');

        // Application Cache
        $this->call('cache:clear');
        $this->line('  ✓ Application Cache');

        // Config Cache
        $this->call('config:clear');
        $this->line('  ✓ Config Cache');

        // Route Cache
        $this->call('route:clear');
        $this->line('  ✓ Route Cache');

        // View Cache
        $this->call('view:clear');
        $this->line('  ✓ View Cache');

        // Compiled Classes
        $this->call('clear-compiled');
        $this->line('  ✓ Compiled Classes');

        $this->newLine();
    }

    /**
     * تخزين التكوينات مؤقتاً
     */
    protected function cacheConfigurations()
    {
        $this->info('📦 تخزين التكوينات...');

        // Config Cache
        $this->call('config:cache');
        $this->line('  ✓ Config Cached');

        // Route Cache
        $this->call('route:cache');
        $this->line('  ✓ Routes Cached');

        // View Cache
        $this->call('view:cache');
        $this->line('  ✓ Views Cached');

        // Optimize
        $this->call('optimize');
        $this->line('  ✓ Application Optimized');

        $this->newLine();
    }

    /**
     * تحميل البيانات المهمة مسبقاً
     */
    protected function warmupCache()
    {
        $this->info('🔥 Warming up Cache...');

        $cacheItems = [
            // يمكن إضافة البيانات المهمة هنا
            // مثال:
            // [
            //     'key' => 'users:active',
            //     'callback' => fn() => User::where('active', 1)->get(),
            //     'ttl' => 3600,
            //     'tags' => ['users']
            // ],
        ];

        if (empty($cacheItems)) {
            $this->line('  ⚠️  لا توجد بيانات للتحميل المسبق');
            $this->line('  💡 يمكنك إضافة بيانات في warmupCache() method');
        } else {
            $warmed = CacheOptimizationService::warmUp($cacheItems);
            $this->line("  ✓ تم تحميل {$warmed} عنصر");
        }

        $this->newLine();
    }

    /**
     * عرض معلومات الأداء
     */
    protected function showInfo()
    {
        $this->info('📊 معلومات الأداء:');
        $this->newLine();

        $info = CacheOptimizationService::getInfo();

        $this->table(
            ['المعلومة', 'القيمة'],
            [
                ['Cache Driver', $info['driver']],
                ['الحالة', $info['enabled'] ? '✅ مفعّل' : '❌ معطّل'],
            ]
        );

        if (isset($info['redis'])) {
            $this->newLine();
            $this->info('Redis Info:');
            $this->table(
                ['المعلومة', 'القيمة'],
                [
                    ['النسخة', $info['redis']['version']],
                    ['الذاكرة المستخدمة', $info['redis']['used_memory']],
                    ['العملاء المتصلين', $info['redis']['connected_clients']],
                    ['عدد المفاتيح', $info['redis']['keys']],
                ]
            );
        }

        // Queue Info
        $this->newLine();
        $this->info('Queue Info:');
        $this->table(
            ['المعلومة', 'القيمة'],
            [
                ['Driver', config('queue.default')],
                ['Max Tries', config('performance.queue.max_tries')],
                ['Timeout', config('performance.queue.timeout') . ' ثانية'],
            ]
        );

        $this->newLine();
    }

    /**
     * اختبار أداء الـ Cache
     */
    protected function runBenchmark()
    {
        $this->info('⏱️  تشغيل اختبار الأداء...');
        $this->line('  (1000 عملية كتابة و 1000 عملية قراءة)');
        $this->newLine();

        $results = CacheOptimizationService::benchmark(1000);

        $this->table(
            ['العملية', 'الوقت (ms)', 'العمليات/ثانية'],
            [
                ['الكتابة', $results['write_time'], number_format($results['operations_per_second']['write'])],
                ['القراءة', $results['read_time'], number_format($results['operations_per_second']['read'])],
            ]
        );

        // تقييم الأداء
        $avgOps = ($results['operations_per_second']['write'] + $results['operations_per_second']['read']) / 2;
        
        $this->newLine();
        if ($avgOps > 10000) {
            $this->info('🚀 الأداء ممتاز! (أكثر من 10,000 عملية/ثانية)');
        } elseif ($avgOps > 5000) {
            $this->info('✅ الأداء جيد جداً (5,000-10,000 عملية/ثانية)');
        } elseif ($avgOps > 1000) {
            $this->warn('⚠️  الأداء مقبول (1,000-5,000 عملية/ثانية)');
            $this->line('💡 نصيحة: استخدم Redis لتحسين الأداء');
        } else {
            $this->error('❌ الأداء ضعيف (أقل من 1,000 عملية/ثانية)');
            $this->line('💡 نصيحة: استخدم Redis بدلاً من File Cache');
        }

        $this->newLine();
    }
}

