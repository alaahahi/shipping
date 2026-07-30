<?php

namespace App\Console\Commands;

use App\Services\RetireCapitalAccountService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class RetireCapitalAccountCommand extends Command
{
    protected $signature = 'accounting:retire-capital-account
                            {--owner=1 : Owner ID}
                            {--dry-run : Preview soft-delete count without changing data}';

    protected $description = 'Soft-delete رأس المال (main@account.com) journal entries and zero balances';

    public function handle(RetireCapitalAccountService $service): int
    {
        $ownerId = (int) $this->option('owner');
        $dryRun = (bool) $this->option('dry-run');

        if (! Schema::hasTable('users') || ! Schema::hasTable('transactions')) {
            $this->error('Required tables not found. Use the production database (mysql).');

            return self::FAILURE;
        }

        $this->info($dryRun
            ? 'DRY-RUN — counting capital account transactions only.'
            : 'Soft-deleting capital account transactions and zeroing balances.');
        $this->newLine();

        $result = $service->retire($ownerId, $dryRun);

        $this->table(
            ['Field', 'Value'],
            [
                ['user_id', $result['user_id'] ?? '—'],
                ['wallet_id', $result['wallet_id'] ?? '—'],
                ['transactions', $result['transactions_soft_deleted']],
                ['balance_dollar_before', number_format($result['balance_dollar_before'], 2)],
                ['balance_dinar_before', number_format($result['balance_dinar_before'], 2)],
                ['dry_run', $result['dry_run'] ? 'yes' : 'no'],
            ]
        );

        if (! $result['wallet_id']) {
            $this->warn('Capital account (main@account.com) not found for this owner.');

            return self::SUCCESS;
        }

        $this->info($dryRun
            ? 'Dry-run complete. Re-run without --dry-run to apply.'
            : 'Capital account retired. Transactions remain recoverable via soft deletes.');

        return self::SUCCESS;
    }
}
