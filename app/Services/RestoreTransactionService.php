<?php

namespace App\Services;

use App\Models\Expenses;
use App\Models\Transactions;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Restore soft-deleted accounting transactions and reverse delete-side wallet adjustments.
 * Never hard-deletes rows.
 */
class RestoreTransactionService
{
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

            $children = Transactions::onlyTrashed()
                ->where('parent_id', $original->id)
                ->get();

            $this->reverseWalletEffects($original, $children);

            foreach ($children as $child) {
                $child->restore();
                Expenses::withTrashed()
                    ->where('transaction_id', $child->id)
                    ->restore();
            }

            $original->restore();
            Expenses::withTrashed()
                ->where('transaction_id', $original->id)
                ->restore();

            $this->audit($original, 'payment', $children->count());

            return $original->fresh();
        });
    }

    /**
     * Reverse the wallet mutations performed by AccountingController::delTransactions.
     *
     * @param  \Illuminate\Support\Collection<int, Transactions>  $children
     */
    protected function reverseWalletEffects(Transactions $original, $children): void
    {
        $wallet = Wallet::find($original->wallet_id);
        if (! $wallet) {
            return;
        }

        $isBoxMove = in_array($original->type, ['inUserBox', 'outUserBox'], true);

        if ($original->currency === '$') {
            $wallet->increment('balance', $original->amount);
            if (! $isBoxMove) {
                foreach ($children as $child) {
                    $this->reverseChildWallet($child);
                }
            }
        }

        if ($original->currency === 'IQD') {
            $wallet->increment('balance_dinar', $original->amount);
            if (! $isBoxMove) {
                foreach ($children as $child) {
                    $this->reverseChildWallet($child);
                }
            }
        }
    }

    protected function reverseChildWallet(Transactions $child): void
    {
        $childWallet = Wallet::find($child->wallet_id);
        if (! $childWallet) {
            return;
        }

        if ($child->currency === '$') {
            $childWallet->increment('balance', $child->amount);
        }
        if ($child->currency === 'IQD') {
            $childWallet->increment('balance_dinar', $child->amount);
        }
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
