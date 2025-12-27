<?php

namespace IntellijApp\License\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use IntellijApp\License\Installer\LicenseInstaller;

class InstallLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تثبيت نظام الترخيص (نشر Config و Migrations)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 تثبيت نظام الترخيص...');
        $this->newLine();

        // التحقق من المتطلبات
        $this->info('🔍 التحقق من المتطلبات...');
        $requirements = LicenseInstaller::checkRequirements();
        
        if (!$requirements['all_met']) {
            $this->error('❌ بعض المتطلبات غير متوفرة:');
            foreach ($requirements['requirements'] as $key => $met) {
                $status = $met ? '✅' : '❌';
                $this->line("   $status $key");
            }
            return Command::FAILURE;
        }
        $this->info('✅ جميع المتطلبات متوفرة');

        // تثبيت Package
        $this->newLine();
        $this->info('📦 تثبيت Package...');
        $result = LicenseInstaller::install();

        if (!$result['success']) {
            $this->error('❌ ' . $result['message']);
            return Command::FAILURE;
        }

        if ($result['config']) {
            $this->info('✅ تم نشر ملف الإعدادات إلى config/license.php');
        }

        if ($result['migrations']) {
            $this->info('✅ تم نشر Migrations');
        }

        $this->newLine();
        $this->info('✅ تم تثبيت نظام الترخيص بنجاح!');
        $this->newLine();
        $this->line('📋 الخطوات التالية:');
        $this->line('   1. قم بتعديل config/license.php حسب احتياجاتك');
        $this->line('      - تخصيص admin_check');
        $this->line('      - تغيير LICENSE_SECRET_KEY في .env');
        $this->line('   2. قم بتشغيل: php artisan migrate');
        $this->line('   3. قم بإنشاء ترخيص: php artisan license:generate');

        return Command::SUCCESS;
    }
}

