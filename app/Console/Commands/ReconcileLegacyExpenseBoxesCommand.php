<?php

namespace App\Console\Commands;

use App\Services\MigrateLegacyExpenseBoxesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ReconcileLegacyExpenseBoxesCommand extends Command
{
    protected $signature = 'accounting:reconcile-legacy-expense-boxes
                            {--owner=1 : Owner ID}
                            {--dry-run : Preview balance differences without updating wallet balances}';

    protected $description = 'Recalculate legacy box wallet balances from transactions (non-destructive: no deletes)';

    public function handle(MigrateLegacyExpenseBoxesService $service): int
    {
        $ownerId = (int) $this->option('owner');
        $dryRun = (bool) $this->option('dry-run');

        if (! Schema::hasTable('users')) {
            $this->error('Table "users" not found. Use the production database (mysql).');
            $this->line('No data is deleted by this command.');

            return self::FAILURE;
        }

        $this->info($dryRun
            ? 'DRY-RUN — previewing balance differences only.'
            : 'Recalculating wallet balances from transaction sums (transactions are not deleted).');
        $this->newLine();

        $report = $service->reconcileBalances($ownerId, $dryRun);

        $rows = [];
        foreach ($report as $legacyKey => $row) {
            $rows[] = [
                $legacyKey,
                number_format($row['calc_balance_dollar'], 2),
                number_format($row['stored_balance_dollar'], 2),
                $row['diff_dollar'],
                number_format($row['calc_balance_dinar'], 0),
                number_format($row['stored_balance_dinar'], 0),
                $row['diff_dinar'],
                number_format($row['expenses_table_sum'], 2),
                $row['legacy_in_remaining'],
                $row['out_user_count'] ?? ($row['in_user_count'] ?? '-'),
                $row['balance_ok'] ? 'OK' : 'DIFF',
            ];
        }

        $this->table(
            [
                'Box',
                'Calc $',
                'Stored $',
                'Diff $',
                'Calc IQD',
                'Stored IQD',
                'Diff IQD',
                'Expenses sum',
                'Legacy in/inUser',
                'outUser',
                'OK',
            ],
            $rows
        );

        $hasDiff = collect($report)->contains(fn ($r) => ! ($r['balance_ok'] ?? true));
        $hasLegacy = collect($report)->contains(fn ($r) => ($r['legacy_in_remaining'] ?? 0) > 0);

        if ($hasLegacy) {
            $this->warn('Legacy in/inUser transactions remain — run: php artisan accounting:migrate-legacy-expense-boxes --owner='.$ownerId);
        }

        if ($dryRun && $hasDiff) {
            $this->newLine();
            $this->info('To fix wallet balance fields, run without --dry-run');
        }

        if (! $dryRun && ! $hasDiff) {
            $this->newLine();
            $this->info('Wallet balances match the sum of transactions.');
        }

        return self::SUCCESS;
    }
}
