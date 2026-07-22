<?php

namespace App\Console\Commands;

use App\Services\MigrateLegacyExpenseBoxesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyExpenseBoxesCommand extends Command
{
    protected $signature = 'accounting:migrate-legacy-expense-boxes
                            {--owner=1 : Owner ID to migrate}
                            {--dry-run : Preview changes without updating users}';

    protected $description = 'Promote legacy GenExpenses accounts (Dubai, Iran, Border, COC) to dashboard treasury boxes without deleting data';

    public function handle(MigrateLegacyExpenseBoxesService $service): int
    {
        $ownerId = (int) $this->option('owner');
        $dryRun = (bool) $this->option('dry-run');

        if (! Schema::hasTable('users')) {
            $this->error('جدول users غير موجود — شغّل migrations أو استخدم قاعدة البيانات الصحيحة.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('وضع المعاينة (dry-run) — لن يُجرَ أي تعديل على المستخدمين.');
        } else {
            $this->warn('سيتم تفعيل القاسات وتحديث الأسماء. لن يُحذف أي سجل مالي.');
        }

        $this->newLine();
        $this->info("المالك (owner_id): {$ownerId}");
        $this->newLine();

        $results = $service->migrate($ownerId, $dryRun, null);

        $rows = [];
        foreach ($results as $row) {
            $rows[] = [
                $row['legacy_key'] ?? '-',
                $row['status'] ?? '-',
                $row['user_id'] ?? '-',
                $row['wallet_id'] ?? '-',
                $row['old_name'] ?? '-',
                $row['new_name'] ?? '-',
                $row['balance_dollar'] ?? '-',
                $row['balance_dinar'] ?? '-',
                $row['transactions_count'] ?? '-',
                $row['expenses_count'] ?? '-',
                $row['message'] ?? '',
            ];
        }

        $this->table(
            [
                'المفتاح',
                'الحالة',
                'user_id',
                'wallet_id',
                'الاسم القديم',
                'الاسم الجديد',
                'رصيد $',
                'رصيد د.ع',
                'معاملات',
                'مصاريف',
                'ملاحظة',
            ],
            $rows
        );

        $failed = collect($results)->contains(fn ($r) => in_array($r['status'] ?? '', ['missing', 'missing_wallet'], true));

        if ($failed) {
            $this->error('بعض الحسابات غير موجودة — راجع الجدول أعلاه.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('لتطبيق الترحيل شغّل الأمر بدون --dry-run:');
            $this->line('  php artisan accounting:migrate-legacy-expense-boxes --owner='.$ownerId);
        } else {
            $this->newLine();
            $this->info('تم الترحيل. تأكد من إخفاء الأزرار القديمة: SHOW_ACCOUNTING_EXTRA_BUTTONS=false');
        }

        return self::SUCCESS;
    }
}
