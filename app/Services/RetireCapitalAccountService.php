<?php

namespace App\Services;

use App\Models\AccountingMigrationLog;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retire the shadow "رأس المال" ledger (main@account.com).
 *
 * Soft-deletes its transactions and zeros wallet balances.
 * Does NOT delete the user/wallet row (keeps FK-safe references).
 */
class RetireCapitalAccountService
{
    public const EMAIL = 'main@account.com';

    public const LEGACY_KEY = 'capital_main_account';

    public function __construct(
        protected AccountingCacheService $accounting
    ) {}

    /**
     * @return array{
     *   user_id: int|null,
     *   wallet_id: int|null,
     *   transactions_soft_deleted: int,
     *   balance_dollar_before: float,
     *   balance_dinar_before: float,
     *   dry_run: bool
     * }
     */
    public function retire(int $ownerId, bool $dryRun = false, ?int $migratedBy = null): array
    {
        $this->accounting->loadAccounts($ownerId);

        $user = User::with('wallet')
            ->where('owner_id', $ownerId)
            ->where('email', self::EMAIL)
            ->first();

        if (! $user || ! $user->wallet) {
            return [
                'user_id' => $user?->id,
                'wallet_id' => null,
                'transactions_soft_deleted' => 0,
                'balance_dollar_before' => 0,
                'balance_dinar_before' => 0,
                'dry_run' => $dryRun,
            ];
        }

        $wallet = $user->wallet;
        $balanceDollar = (float) ($wallet->balance ?? 0);
        $balanceDinar = (float) ($wallet->balance_dinar ?? 0);

        $query = Transactions::query()->where('wallet_id', $wallet->id);
        $count = (clone $query)->count();

        if ($dryRun) {
            return [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'transactions_soft_deleted' => $count,
                'balance_dollar_before' => $balanceDollar,
                'balance_dinar_before' => $balanceDinar,
                'dry_run' => true,
            ];
        }

        return DB::transaction(function () use ($user, $wallet, $query, $count, $balanceDollar, $balanceDinar, $ownerId, $migratedBy, $dryRun) {
            $deleted = $query->delete(); // SoftDeletes on Transactions

            Wallet::where('id', $wallet->id)->update([
                'balance' => 0,
                'balance_dinar' => 0,
            ]);

            // Hide from treasury/user pickers if flag exists.
            if (Schema::hasColumn('users', 'show_in_dashboard')) {
                $user->show_in_dashboard = false;
                $user->save();
            }

            if (Schema::hasTable('accounting_migration_logs')) {
                AccountingMigrationLog::create([
                    'owner_id' => $ownerId,
                    'legacy_key' => self::LEGACY_KEY,
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'balance_dollar_before' => $balanceDollar,
                    'balance_dinar_before' => $balanceDinar,
                    'transactions_count' => $deleted,
                    'expenses_count' => 0,
                    'display_name' => $user->name ?: 'رأس المال',
                    'note' => 'Retired capital account (main@account.com): soft-deleted transactions, zeroed balances. No hard deletes.',
                    'migrated_by' => $migratedBy,
                    'dry_run' => false,
                ]);
            }

            $this->accounting->forgetOwnerAccounts($ownerId);

            return [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'transactions_soft_deleted' => $deleted,
                'balance_dollar_before' => $balanceDollar,
                'balance_dinar_before' => $balanceDinar,
                'dry_run' => $dryRun,
            ];
        });
    }
}
