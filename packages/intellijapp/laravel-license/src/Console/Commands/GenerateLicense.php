<?php

namespace IntellijApp\License\Console\Commands;

use Illuminate\Console\Command;
use IntellijApp\License\Services\LicenseService;
use Carbon\Carbon;

class GenerateLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate 
                            {--domain= : Domain or IP address}
                            {--type=standard : License type (trial, standard, premium)}
                            {--expires= : Expiry date (Y-m-d) or null for lifetime}
                            {--installations=1 : Maximum number of installations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء مفتاح ترخيص جديد';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔑 إنشاء مفتاح ترخيص جديد...');
        $this->newLine();

        // الحصول على المدخلات
        $domain = $this->option('domain') ?: $this->ask('أدخل Domain أو IP', 'example.com');
        $type = $this->option('type') ?: $this->choice('نوع الترخيص', ['trial', 'standard', 'premium'], 1);
        $expires = $this->option('expires');
        $maxInstallations = (int) ($this->option('installations') ?: 1);

        // إذا لم يتم تحديد تاريخ الانتهاء، نسأل
        if (!$expires) {
            $expiresChoice = $this->choice('مدة الترخيص', ['دائم', 'سنوي', 'شهري', 'تجريبي (30 يوم)', 'مخصص'], 0);
            
            switch ($expiresChoice) {
                case 'دائم':
                    $expires = null;
                    break;
                case 'سنوي':
                    $expires = Carbon::now()->addYear()->format('Y-m-d');
                    break;
                case 'شهري':
                    $expires = Carbon::now()->addMonth()->format('Y-m-d');
                    break;
                case 'تجريبي (30 يوم)':
                    $expires = Carbon::now()->addDays(30)->format('Y-m-d');
                    $type = 'trial';
                    break;
                case 'مخصص':
                    $expires = $this->ask('أدخل تاريخ الانتهاء (Y-m-d)', Carbon::now()->addYear()->format('Y-m-d'));
                    break;
            }
        }

        // الحصول على Fingerprint
        $fingerprint = LicenseService::getServerFingerprint();

        // إنشاء بيانات الترخيص
        $licenseData = [
            'domain' => $domain,
            'fingerprint' => $fingerprint,
            'type' => $type,
            'expires_at' => $expires,
            'max_installations' => $maxInstallations,
            'issued_at' => now()->toDateTimeString(),
        ];

        // تشفير المفتاح
        $licenseKey = LicenseService::encryptLicenseKey($licenseData);

        $this->newLine();
        $this->info('✅ تم إنشاء مفتاح الترخيص بنجاح!');
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('مفتاح الترخيص:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line($licenseKey);
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->table(
            ['المعلومة', 'القيمة'],
            [
                ['Domain', $domain],
                ['Fingerprint', substr($fingerprint, 0, 20) . '...'],
                ['النوع', $type],
                ['ينتهي في', $expires ?? 'دائم'],
                ['عدد التثبيتات', $maxInstallations],
            ]
        );

        $this->newLine();
        $this->warn('⚠️  احفظ هذا المفتاح في مكان آمن! لن تتمكن من رؤيته مرة أخرى.');

        return Command::SUCCESS;
    }
}

