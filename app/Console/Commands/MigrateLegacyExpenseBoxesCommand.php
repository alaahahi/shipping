<?php

namespace App\Console\Commands;

use App\Services\MigrateLegacyExpenseBoxesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyExpenseBoxesCommand extends Command
{
    protected $signature = 'accounting:migrate-legacy-expense-boxes
                            {--owner=1 : Owner ID to migrate}
                            {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Migrate legacy GenExpenses boxes to dashboard treasuries (non-destructive: no deletes)';

    public function handle(MigrateLegacyExpenseBoxesService $service): int
    {
        $ownerId = (int) $this->option('owner');
        $dryRun = (bool) $this->option('dry-run');

        if (! Schema::hasTable('users')) {
            $this->error('Table "users" not found. On local dev try LOCAL_NO_REMOTE=false, or run on the server.');
            $this->line('No data is deleted by this command.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('DRY-RUN mode — no database changes will be made.');
        } else {
            $this->warn('This will update display flags and transaction types (in → inUser).');
            $this->line('Safety: no rows are deleted from transactions, expenses, wallets, or users.');
        }

        $this->newLine();
        $this->info("Owner ID: {$ownerId}");
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
                $row['legacy_transactions_pending'] ?? '-',
                $row['transactions_migrated'] ?? '-',
                isset($row['calc_balance_dollar']) ? number_format($row['calc_balance_dollar'], 2) : '-',
                isset($row['stored_balance_dollar']) ? number_format($row['stored_balance_dollar'], 2) : '-',
                $row['diff_dollar'] ?? '-',
                isset($row['expenses_table_sum']) ? number_format($row['expenses_table_sum'], 2) : '-',
                isset($row['balance_ok']) ? ($row['balance_ok'] ? 'OK' : 'DIFF') : '-',
                $row['message'] ?? '',
            ];
        }

        $this->table(
            [
                'Key',
                'Status',
                'user_id',
                'wallet_id',
                'Old name',
                'New name',
                'Balance $',
                'Balance IQD',
                'Transactions',
                'Expenses',
                'Legacy in',
                'Converted',
                'Calc $',
                'Stored $',
                'Diff $',
                'Expenses sum',
                'Balanced',
                'Note',
            ],
            $rows
        );

        $failed = collect($results)->contains(fn ($r) => in_array($r['status'] ?? '', ['missing', 'missing_wallet'], true));

        if ($failed) {
            $this->error('Some system accounts were not found — see the table above.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('To apply the migration, run without --dry-run:');
            $this->line('  php artisan accounting:migrate-legacy-expense-boxes --owner='.$ownerId);
        } else {
            $this->newLine();
            $this->info('Migration complete. Hide legacy UI buttons: SHOW_ACCOUNTING_EXTRA_BUTTONS=false');
            $this->line('All historical transactions and expenses records were preserved.');
        }

        return self::SUCCESS;
    }
}
