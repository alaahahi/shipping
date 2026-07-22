<?php

namespace App\Services;

use App\Models\AccountingMigrationLog;
use App\Models\Expenses;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyExpenseBoxesService
{
    public const LEGACY_BOXES = [
        'dubai' => [
            'cache_key' => 'dubai',
            'display_name' => 'قاسة دبي',
            'expenses_type_id' => 2,
        ],
        'iran' => [
            'cache_key' => 'iran',
            'display_name' => 'قاسة إيران',
            'expenses_type_id' => 3,
        ],
        'border' => [
            'cache_key' => 'border',
            'display_name' => 'قاسة الحدود',
            'expenses_type_id' => 4,
        ],
        'shipping_coc' => [
            'cache_key' => 'shipping_coc',
            'display_name' => 'قاسة COC',
            'expenses_type_id' => 5,
        ],
    ];

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

        $results = [];
        $migrationNote = 'تم ترحيل هذا الحساب من نظام المصاريف القديم (GenExpenses) إلى قاسة لوحة المحاسبة. لم يُحذف أي سجل.';

        foreach (self::LEGACY_BOXES as $legacyKey => $config) {
            $results[] = $this->migrateBox(
                $ownerId,
                $legacyKey,
                $config,
                $dryRun,
                $migratedBy,
                $migrationNote
            );
        }

        if (! $dryRun) {
            $this->accounting->refresh();
        }

        return $results;
    }

    /**
     * @param  array{cache_key: string, display_name: string, expenses_type_id: int}  $config
     * @return array<string, mixed>
     */
    protected function migrateBox(
        int $ownerId,
        string $legacyKey,
        array $config,
        bool $dryRun,
        ?int $migratedBy,
        string $migrationNote
    ): array {
        $account = $this->accounting->getAccount($config['cache_key']);

        if (! $account) {
            return [
                'legacy_key' => $legacyKey,
                'status' => 'missing',
                'message' => 'حساب النظام غير موجود لهذا المالك.',
            ];
        }

        $account->loadMissing('wallet');

        if (! $account->wallet) {
            return [
                'legacy_key' => $legacyKey,
                'status' => 'missing_wallet',
                'user_id' => $account->id,
                'message' => 'المستخدم موجود لكن بدون محفظة.',
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

        $alreadyMigrated = (bool) $account->show_in_dashboard
            && $account->name === $config['display_name'];

        $result = [
            'legacy_key' => $legacyKey,
            'status' => $alreadyMigrated ? 'skipped' : ($dryRun ? 'dry_run' : 'migrated'),
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
            'message' => $alreadyMigrated
                ? 'القاسة مفعّلة مسبقاً — لم يُجرَ تعديل.'
                : ($dryRun ? 'جاهز للترحيل (معاينة فقط).' : 'تم الترحيل بنجاح.'),
        ];

        if ($alreadyMigrated) {
            return $result;
        }

        if ($dryRun) {
            return $result;
        }

        DB::transaction(function () use ($account, $config, $migrationNote) {
            $account->update([
                'name' => $config['display_name'],
                'show_in_dashboard' => true,
            ]);
        });

        $this->writeLog($ownerId, $legacyKey, $result, $migrationNote, $migratedBy, false);

        return $result;
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
            'note' => $migrationNote,
            'migrated_by' => $migratedBy,
            'dry_run' => $dryRun,
        ]);
    }
}
