<?php

namespace IntellijApp\License\Console\Commands;

use Illuminate\Console\Command;
use IntellijApp\License\Services\LicenseService;

class SyncLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:sync 
                            {--pull : جلب الترخيص من السيرفر المركزي}
                            {--push : إرسال الترخيص إلى السيرفر المركزي}
                            {--auto : مزامنة تلقائية}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'مزامنة الترخيص مع السيرفر المركزي';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!config('license.sync_enabled')) {
            $this->error('❌ المزامنة غير مفعلة. قم بتفعيلها في config/license.php');
            return Command::FAILURE;
        }

        if (!config('license.sync_server_url')) {
            $this->error('❌ لم يتم تحديد سيرفر المزامنة. قم بإضافته في config/license.php');
            return Command::FAILURE;
        }

        $this->info('🔄 بدء مزامنة الترخيص...');
        $this->newLine();

        if ($this->option('pull')) {
            // جلب من السيرفر المركزي
            $this->info('📥 جلب الترخيص من السيرفر المركزي...');
            $result = LicenseService::pullFromCentralServer();
            
        } elseif ($this->option('push')) {
            // إرسال إلى السيرفر المركزي
            $this->info('📤 إرسال الترخيص إلى السيرفر المركزي...');
            $result = LicenseService::pushToCentralServer();
            
        } else {
            // مزامنة تلقائية
            $this->info('🔄 مزامنة تلقائية...');
            $result = LicenseService::syncWithCentralServer();
        }

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            return Command::SUCCESS;
        } else {
            $this->error('❌ ' . $result['message']);
            return Command::FAILURE;
        }
    }
}

