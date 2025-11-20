<?php

namespace IntellijApp\License\Console\Commands;

use Illuminate\Console\Command;
use IntellijApp\License\Services\LicenseService;
use IntellijApp\License\Models\License;

class VerifyLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'التحقق من صلاحية الترخيص';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 التحقق من الترخيص...');
        $this->newLine();

        if (!config('license.enabled')) {
            $this->warn('⚠️  نظام الترخيص معطل');
            return Command::SUCCESS;
        }

        $license = LicenseService::getCurrentLicense();

        if (!$license) {
            $this->error('❌ الترخيص غير مفعل');
            $this->newLine();
            $this->line('💡 قم بتفعيل الترخيص باستخدام:');
            $this->line('   - زيارة صفحة التفعيل في المتصفح');
            $this->line('   - أو استخدام API: POST /api/license/activate');
            return Command::FAILURE;
        }

        // تحديث آخر تحقق
        $license->update(['last_verified_at' => now()]);

        $isValid = LicenseService::verify();

        if ($isValid) {
            $this->info('✅ الترخيص صالح ومفعل');
        } else {
            $this->error('❌ الترخيص غير صالح أو منتهي الصلاحية');
        }

        $this->newLine();
        $this->table(
            ['المعلومة', 'القيمة'],
            [
                ['Domain', $license->domain],
                ['Fingerprint', substr($license->fingerprint ?? '', 0, 20) . '...'],
                ['النوع', $license->type],
                ['الحالة', $license->is_active ? '✅ مفعل' : '❌ معطل'],
                ['مفعل منذ', $license->activated_at?->format('Y-m-d H:i:s') ?? 'غير محدد'],
                ['ينتهي في', $license->expires_at ? $license->expires_at->format('Y-m-d H:i:s') : 'دائم'],
                ['الأيام المتبقية', $license->days_remaining ?? '∞'],
                ['آخر تحقق', $license->last_verified_at?->format('Y-m-d H:i:s') ?? 'لم يتم التحقق'],
            ]
        );

        return $isValid ? Command::SUCCESS : Command::FAILURE;
    }
}

