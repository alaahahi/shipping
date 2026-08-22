<?php

namespace App\Console\Commands;

use App\Services\CashBoxLedgerService;
use Illuminate\Console\Command;

class ResyncMainBoxFromLedger extends Command
{
    protected $signature = 'accounting:resync-main-box
                            {--owner= : owner_id محدد (اختياري)}
                            {--dry-run : عرض الانحراف دون تعديل}';

    protected $description = 'مزامنة رصيد الصندوق (mainBox) من دفتر القيود transactions دون المساس بباقي الحسابات';

    public function handle(CashBoxLedgerService $ledger): int
    {
        $owner = $this->option('owner');
        $ownerId = $owner !== null && $owner !== '' ? (int) $owner : null;
        $dryRun = (bool) $this->option('dry-run');

        $results = $ledger->resyncMainBox($ownerId, $dryRun);

        if ($results === []) {
            $this->warn('لم يُعثر على حساب mainBox@account.com');

            return self::FAILURE;
        }

        foreach ($results as $row) {
            if (isset($row['error'])) {
                $this->error("owner={$row['owner_id']} — {$row['error']}");
                continue;
            }

            $status = $dryRun
                ? 'DRY-RUN'
                : ($row['synced'] ? 'SYNCED' : 'OK');

            $this->line(sprintf(
                '[%s] owner=%s wallet=%s ledger$=%s cached$=%s drift$=%s | ledgerIQD=%s cachedIQD=%s driftIQD=%s',
                $status,
                $row['owner_id'],
                $row['wallet_id'],
                $row['ledger_balance'],
                $row['cached_balance'],
                $row['drift'],
                $row['ledger_balance_dinar'],
                $row['cached_balance_dinar'],
                $row['drift_dinar']
            ));
        }

        $this->info($dryRun ? 'انتهى الفحص (بدون تعديل).' : 'انتهت المزامنة.');

        return self::SUCCESS;
    }
}
