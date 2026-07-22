<?php

namespace App\Services;

use App\Models\AccountingMigrationLog;
use App\Models\Expenses;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Non-destructive migration for legacy GenExpenses system accounts.
 *
 * This service NEVER deletes or soft-deletes:
 * - transactions
 * - expenses
 * - wallets
 * - users
 *
 * It only updates: user display name/flag, transaction type labels, wallet balance cache fields.
 */
class MigrateLegacyExpenseBoxesService
{
    public const LEGACY_BOXES = [
        'dubai' => [
            'cache_key' => 'dubai',
            'display_name' => 'قاسة دبي',
            'expenses_type_id' => 2,
            'description_pattern' => '/مصاريف\s+دبي/ui',
        ],
        'iran' => [
            'cache_key' => 'iran',
            'display_name' => 'قاسة إيران',
            'expenses_type_id' => 3,
            'description_pattern' => '/مصاريف\s+(ايران|إيران)/ui',
        ],
        'border' => [
            'cache_key' => 'border',
            'display_name' => 'قاسة الحدود',
            'expenses_type_id' => 4,
            'description_pattern' => '/مصاريف\s+الحدود/ui',
        ],
        'shipping_coc' => [
            'cache_key' => 'shipping_coc',
            'display_name' => 'قاسة COC',
            'expenses_type_id' => 5,
            'description_pattern' => '/مصاريف\s+شهادة\s*coc/ui',
        ],
    ];

    /** @var list<string> */
    public const LEGACY_EMAILS = ['dubai', 'iran', 'border', 'shipping-coc'];

    public static function isLegacyExpenseEmail(?string $email): bool
    {
        return in_array($email ?? '', self::LEGACY_EMAILS, true);
    }

    public function __construct(
        protected AccountingCacheService $accounting
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function migrate(int $ownerId, bool $dryRun = false, ?int $migratedBy = null): array
    {
        User::ensureOptionalColumns();
        $this->accounting->loadAccounts($ownerId);

        $mainBox = $this->accounting->mainBox();
        $mainBoxWalletId = $mainBox?->wallet?->id;

        $results = [];
        $migrationNote = 'Migrated from legacy GenExpenses to dashboard treasury box. No records were deleted.';

        foreach (self::LEGACY_BOXES as $legacyKey => $config) {
            $results[] = $this->migrateBox(
                $ownerId,
                $legacyKey,
                $config,
                $mainBoxWalletId,
                $dryRun,
                $migratedBy,
                $migrationNote
            );
        }

        if (! $dryRun) {
            $this->accounting->refresh();
        }

        $reconcileResults = $this->reconcileBalances($ownerId, $dryRun);
        foreach ($results as $index => $result) {
            if (isset($reconcileResults[$result['legacy_key'] ?? ''])) {
                $results[$index] = array_merge($result, $reconcileResults[$result['legacy_key']]);
            }
        }

        return $results;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function reconcileBalances(int $ownerId, bool $dryRun = false): array
    {
        $this->accounting->loadAccounts($ownerId);
        $report = [];

        foreach (self::LEGACY_BOXES as $legacyKey => $config) {
            $account = $this->accounting->getAccount($config['cache_key']);
            if (! $account) {
                continue;
            }

            $account->loadMissing('wallet');
            if (! $account->wallet) {
                continue;
            }

            $walletId = $account->wallet->id;

            $calcDollar = (float) Transactions::query()
                ->where('wallet_id', $walletId)
                ->whereNull('deleted_at')
                ->where('currency', '$')
                ->where('type', 'outUser')
                ->sum('amount');

            $calcDinar = (float) Transactions::query()
                ->where('wallet_id', $walletId)
                ->whereNull('deleted_at')
                ->where('currency', 'IQD')
                ->where('type', 'outUser')
                ->sum('amount');

            $storedDollar = (float) ($account->wallet->balance ?? 0);
            $storedDinar = (float) ($account->wallet->balance_dinar ?? 0);

            $expensesSum = (float) Expenses::query()
                ->where('user_id', $account->id)
                ->where('expenses_type_id', $config['expenses_type_id'])
                ->sum('amount');

            $legacyInCount = Transactions::query()
                ->where('wallet_id', $walletId)
                ->whereIn('type', ['in', 'inUser'])
                ->whereNull('deleted_at')
                ->count();

            $inUserCount = Transactions::query()
                ->where('wallet_id', $walletId)
                ->where('type', 'outUser')
                ->whereNull('deleted_at')
                ->count();

            // Legacy expense treasuries use zero stored balance; totals come from outUser rows.
            $diffDollar = round($storedDollar, 2);
            $diffDinar = round($storedDinar, 2);
            $balanced = abs($diffDollar) < 0.01 && abs($diffDinar) < 0.01;

            if (! $dryRun && ! $balanced) {
                $account->wallet->update([
                    'balance' => 0,
                    'balance_dinar' => 0,
                ]);
                $storedDollar = 0;
                $storedDinar = 0;
                $diffDollar = 0;
                $diffDinar = 0;
                $balanced = true;
            }

            $report[$legacyKey] = [
                'calc_balance_dollar' => $calcDollar,
                'calc_balance_dinar' => $calcDinar,
                'stored_balance_dollar' => $storedDollar,
                'stored_balance_dinar' => $storedDinar,
                'diff_dollar' => $diffDollar,
                'diff_dinar' => $diffDinar,
                'balance_ok' => $balanced,
                'expenses_table_sum' => $expensesSum,
                'legacy_in_remaining' => $legacyInCount,
                'out_user_count' => $inUserCount,
            ];
        }

        return $report;
    }

    /**
     * @param  array{cache_key: string, display_name: string, expenses_type_id: int, description_pattern: string}  $config
     * @return array<string, mixed>
     */
    protected function migrateBox(
        int $ownerId,
        string $legacyKey,
        array $config,
        ?int $mainBoxWalletId,
        bool $dryRun,
        ?int $migratedBy,
        string $migrationNote
    ): array {
        $account = $this->accounting->getAccount($config['cache_key']);

        if (! $account) {
            return [
                'legacy_key' => $legacyKey,
                'status' => 'missing',
                'message' => 'System account not found for this owner.',
            ];
        }

        $account->loadMissing('wallet');

        if (! $account->wallet) {
            return [
                'legacy_key' => $legacyKey,
                'status' => 'missing_wallet',
                'user_id' => $account->id,
                'message' => 'User exists but has no wallet.',
            ];
        }

        $walletId = $account->wallet->id;
        $balanceDollar = (float) ($account->wallet->balance ?? 0);
        $balanceDinar = (float) ($account->wallet->balance_dinar ?? 0);

        $transactionsCount = Transactions::query()
            ->where('wallet_id', $walletId)
            ->whereNull('deleted_at')
            ->count();

        $expensesCount = Expenses::query()
            ->where('user_id', $account->id)
            ->where('expenses_type_id', $config['expenses_type_id'])
            ->count();

        $legacyTransactionIds = $this->findLegacyGenExpenseTransactionIds($walletId);

        $userFlagsNeedUpdate = ! (
            (bool) $account->show_in_dashboard
            && $account->name === $config['display_name']
        );

        $transactionsNeedUpdate = $legacyTransactionIds->isNotEmpty();

        $result = [
            'legacy_key' => $legacyKey,
            'status' => $this->resolveStatus($userFlagsNeedUpdate, $transactionsNeedUpdate, $dryRun),
            'user_id' => $account->id,
            'wallet_id' => $walletId,
            'email' => $account->email,
            'old_name' => $account->name,
            'new_name' => $config['display_name'],
            'show_in_dashboard_before' => (bool) $account->show_in_dashboard,
            'balance_dollar' => $balanceDollar,
            'balance_dinar' => $balanceDinar,
            'transactions_count' => $transactionsCount,
            'expenses_count' => $expensesCount,
            'legacy_transactions_pending' => $legacyTransactionIds->count(),
            'transactions_migrated' => 0,
            'parents_migrated' => 0,
            'message' => $this->resolveMessage($userFlagsNeedUpdate, $transactionsNeedUpdate, $legacyTransactionIds->count(), $dryRun),
        ];

        if ($dryRun) {
            return $result;
        }

        if (! $userFlagsNeedUpdate && ! $transactionsNeedUpdate) {
            return $result;
        }

        DB::transaction(function () use (
            $account,
            $config,
            $legacyTransactionIds,
            $mainBoxWalletId,
            $userFlagsNeedUpdate,
            &$result
        ) {
            if ($userFlagsNeedUpdate) {
                $account->update([
                    'name' => $config['display_name'],
                    'show_in_dashboard' => true,
                ]);
            }

            if ($legacyTransactionIds->isNotEmpty()) {
                $migrationStats = $this->migrateGenExpenseTransactions(
                    $legacyTransactionIds,
                    $account->id,
                    $mainBoxWalletId
                );
                $result['transactions_migrated'] = $migrationStats['transactions_migrated'];
                $result['parents_migrated'] = $migrationStats['parents_migrated'];
            }

            $this->zeroLegacyWalletBalance($account);
        });

        $this->writeLog($ownerId, $legacyKey, $result, $migrationNote, $migratedBy, false);

        return $result;
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function findLegacyGenExpenseTransactionIds(int $walletId)
    {
        // Convert legacy GenExpenses deposits (in / inUser) to treasury withdrawals (outUser).
        return Transactions::query()
            ->where('wallet_id', $walletId)
            ->whereIn('type', ['in', 'inUser'])
            ->whereNull('deleted_at')
            ->pluck('id')
            ->values();
    }

    protected function zeroLegacyWalletBalance(User $account): void
    {
        if (! $account->wallet) {
            return;
        }

        $account->wallet->update([
            'balance' => 0,
            'balance_dinar' => 0,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $transactionIds
     * @return array{transactions_migrated: int, parents_migrated: int}
     */
    protected function migrateGenExpenseTransactions(
        $transactionIds,
        int $accountUserId,
        ?int $mainBoxWalletId
    ): array {
        $transactionsMigrated = 0;
        $parentsMigrated = 0;

        foreach ($transactionIds as $transactionId) {
            $child = Transactions::query()
                ->where('id', $transactionId)
                ->whereIn('type', ['in', 'inUser'])
                ->whereNull('deleted_at')
                ->first();

            if (! $child) {
                continue;
            }

            $details = is_array($child->details) ? $child->details : [];
            $details['legacy_gen_expense_migrated'] = true;
            $details['legacy_expense_withdrawal'] = true;

            $child->type = 'outUser';
            $child->morphed_id = $accountUserId;
            $child->morphed_type = User::class;
            $child->details = $details;
            $child->save();
            $transactionsMigrated++;

            if (! $child->parent_id || ! $mainBoxWalletId) {
                continue;
            }

            $parent = Transactions::query()
                ->where('id', $child->parent_id)
                ->where('wallet_id', $mainBoxWalletId)
                ->whereIn('type', ['out', 'outUserBox'])
                ->whereNull('deleted_at')
                ->first();

            if (! $parent || $parent->type === 'outUserBox') {
                continue;
            }

            $parentDetails = is_array($parent->details) ? $parent->details : [];
            $parentDetails['legacy_gen_expense_migrated'] = true;

            $parent->type = 'outUserBox';
            $parent->morphed_id = $accountUserId;
            $parent->morphed_type = User::class;
            $parent->details = $parentDetails;
            $parent->save();
            $parentsMigrated++;
        }

        return [
            'transactions_migrated' => $transactionsMigrated,
            'parents_migrated' => $parentsMigrated,
        ];
    }

    protected function resolveStatus(bool $userFlagsNeedUpdate, bool $transactionsNeedUpdate, bool $dryRun): string
    {
        if (! $userFlagsNeedUpdate && ! $transactionsNeedUpdate) {
            return 'skipped';
        }

        return $dryRun ? 'dry_run' : 'migrated';
    }

    protected function resolveMessage(
        bool $userFlagsNeedUpdate,
        bool $transactionsNeedUpdate,
        int $pendingCount,
        bool $dryRun
    ): string {
        if (! $userFlagsNeedUpdate && ! $transactionsNeedUpdate) {
            return 'Already migrated — nothing to change.';
        }

        $parts = [];
        if ($userFlagsNeedUpdate) {
            $parts[] = $dryRun ? 'Will enable dashboard display' : 'Dashboard display enabled';
        }
        if ($transactionsNeedUpdate) {
            $parts[] = $dryRun
                ? "Will convert {$pendingCount} payment(s) to outUser / withdrawal (no deletes)"
                : "Converted {$pendingCount} payment(s) to withdrawal model (no deletes)";
        }

        return implode(' — ', $parts);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function writeLog(
        int $ownerId,
        string $legacyKey,
        array $result,
        string $migrationNote,
        ?int $migratedBy,
        bool $dryRun
    ): void {
        if (! Schema::hasTable('accounting_migration_logs')) {
            return;
        }

        AccountingMigrationLog::create([
            'owner_id' => $ownerId,
            'legacy_key' => $legacyKey,
            'user_id' => $result['user_id'] ?? null,
            'wallet_id' => $result['wallet_id'] ?? null,
            'balance_dollar_before' => $result['balance_dollar'] ?? 0,
            'balance_dinar_before' => $result['balance_dinar'] ?? 0,
            'transactions_count' => $result['transactions_count'] ?? 0,
            'expenses_count' => $result['expenses_count'] ?? 0,
            'display_name' => $result['new_name'] ?? null,
            'note' => trim($migrationNote.' | payments converted: '.($result['transactions_migrated'] ?? 0)),
            'migrated_by' => $migratedBy,
            'dry_run' => $dryRun,
        ]);
    }
}
