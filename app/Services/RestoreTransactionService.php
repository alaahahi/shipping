<?php

namespace App\Services;

use App\Models\Expenses;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Restore soft-deleted accounting transactions as a linked pair
 * and resync main-box balance from the ledger (no double wallet math).
 */
class RestoreTransactionService
{
    public function __construct(
        protected CashBoxLedgerService $cashBoxLedger
    ) {
    }

    public function restore(int $transactionId, int $ownerId): Transactions
    {
        return DB::transaction(function () use ($transactionId, $ownerId) {
            $original = Transactions::onlyTrashed()
                ->with(['wallet.user'])
                ->find($transactionId);

            if (! $original) {
                throw new RuntimeException('Deleted transaction not found.');
            }

            $walletUser = $original->wallet?->user;
            if (! $walletUser || (int) $walletUser->owner_id !== (int) $ownerId) {
                throw new RuntimeException('Not authorized to restore this transaction.');
            }

            if (in_array($original->type, ['inUserAmanah', 'outUserAmanah'], true)) {
                $original->restore();
                $this->audit($original, 'amanah');

                return $original->fresh();
            }

            [$root, $children] = $this->resolvePair($original);

            $mainBoxWalletIds = $this->mainBoxWalletIds($ownerId);
            $affectedWalletIds = collect([$root->wallet_id])
                ->merge($children->pluck('wallet_id'))
                ->filter()
                ->unique()
                ->values();

            // Legacy reverse for non-cash wallets BEFORE restore (same signed amount as delete used)
            foreach ($affectedWalletIds as $walletId) {
                if ($mainBoxWalletIds->contains((int) $walletId)) {
                    continue;
                }
                $this->reapplyNonCashWallet($root, $children, (int) $walletId);
            }

            foreach ($children as $child) {
                if ($child->trashed()) {
                    $child->restore();
                }
                Expenses::withTrashed()
                    ->where('transaction_id', $child->id)
                    ->restore();
            }

            if ($root->trashed()) {
                $root->restore();
            }
            Expenses::withTrashed()
                ->where('transaction_id', $root->id)
                ->restore();

            // صندوق: من الدفتر فقط
            foreach ($mainBoxWalletIds as $boxWalletId) {
                if ($affectedWalletIds->contains($boxWalletId)) {
                    $wallet = Wallet::with('user')->find($boxWalletId);
                    if ($wallet?->user) {
                        $this->cashBoxLedger->alignWalletCacheIfMainBox($wallet->user);
                    }
                }
            }

            $this->audit($root, 'payment', $children->count());

            return $root->fresh();
        });
    }

    /**
     * @return array{0: Transactions, 1: \Illuminate\Support\Collection<int, Transactions>}
     */
    protected function resolvePair(Transactions $original): array
    {
        $boxTypes = ['inUserBox', 'outUserBox'];

        if ((int) ($original->parent_id ?? 0) > 0
            && in_array($original->type, ['inUser', 'outUser'], true)
        ) {
            $parent = Transactions::withTrashed()->find((int) $original->parent_id);
            if ($parent && in_array($parent->type, $boxTypes, true)) {
                $children = Transactions::withTrashed()->where('parent_id', $parent->id)->get();

                return [$parent, $children];
            }
        }

        if (in_array($original->type, $boxTypes, true)) {
            $children = Transactions::withTrashed()->where('parent_id', $original->id)->get();

            return [$original, $children];
        }

        $children = Transactions::withTrashed()->where('parent_id', $original->id)->get();

        return [$original, $children];
    }

    protected function reapplyNonCashWallet(Transactions $root, $children, int $walletId): void
    {
        $wallet = Wallet::find($walletId);
        if (! $wallet) {
            return;
        }

        $rows = collect([$root])->merge($children)->where('wallet_id', $walletId);

        foreach ($rows as $row) {
            if (in_array($row->type, ['inUser', 'outUser', 'inUserAmanah', 'outUserAmanah'], true)) {
                continue;
            }

            if ($row->currency === '$') {
                $wallet->increment('balance', $row->amount);
            }
            if ($row->currency === 'IQD') {
                $wallet->increment('balance_dinar', $row->amount);
            }
        }
    }

    protected function mainBoxWalletIds(int $ownerId)
    {
        return User::query()
            ->where('owner_id', $ownerId)
            ->where('email', 'mainBox@account.com')
            ->with('wallet')
            ->get()
            ->pluck('wallet.id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    protected function audit(Transactions $transaction, string $kind, int $childrenRestored = 0): void
    {
        Log::info('Accounting transaction restored', [
            'transaction_id' => $transaction->id,
            'kind' => $kind,
            'wallet_id' => $transaction->wallet_id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'type' => $transaction->type,
            'children_restored' => $childrenRestored,
            'restored_by' => Auth::id(),
            'owner_id' => Auth::user()?->owner_id,
        ]);
    }
}
