<?php

namespace App\Services;

use App\Models\Expenses;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Soft-delete accounting movements as a linked pair (صندوق + قاصة)
 * and resync main-box balance from the ledger — never double-apply wallet math.
 */
class DeleteTransactionService
{
    public function __construct(
        protected CashBoxLedgerService $cashBoxLedger
    ) {
    }

    /**
     * @return array{deleted_ids: int[], main_box_resynced: bool}
     */
    public function delete(int $transactionId, int $ownerId): array
    {
        return DB::transaction(function () use ($transactionId, $ownerId) {
            $original = Transactions::with(['TransactionsImages', 'wallet.user'])->find($transactionId);

            if (! $original) {
                throw new RuntimeException('Transaction not found.');
            }

            $walletUser = $original->wallet?->user;
            if (! $walletUser || (int) $walletUser->owner_id !== (int) $ownerId) {
                throw new RuntimeException('Not authorized to delete this transaction.');
            }

            if (in_array($original->type, ['inUserAmanah', 'outUserAmanah'], true)) {
                $this->deleteImages($original);
                $original->delete();

                return ['deleted_ids' => [$original->id], 'main_box_resynced' => false];
            }

            [$root, $children] = $this->resolvePair($original);

            $affectedWalletIds = collect([$root->wallet_id])
                ->merge($children->pluck('wallet_id'))
                ->filter()
                ->unique()
                ->values();

            $mainBoxWalletIds = $this->mainBoxWalletIds($ownerId);

            // 1) Soft-delete children then root (ledger excludes them)
            foreach ($children as $child) {
                Expenses::where('transaction_id', $child->id)->delete();
                $child->delete();
            }

            $this->deleteImages($root);
            Expenses::where('transaction_id', $root->id)->delete();

            // Legacy side-effects for non-box wallets (client debt / expense caches)
            foreach ($affectedWalletIds as $walletId) {
                if ($mainBoxWalletIds->contains((int) $walletId)) {
                    continue;
                }
                $this->reverseNonCashWallet($root, $children, (int) $walletId);
            }

            if (! $root->trashed()) {
                $root->delete();
            }

            // 2) صندوق: الرصيد من الدفتر فقط — لا decrement يدوي
            $resynced = false;
            foreach ($mainBoxWalletIds as $boxWalletId) {
                if ($affectedWalletIds->contains($boxWalletId)) {
                    $wallet = Wallet::with('user')->find($boxWalletId);
                    if ($wallet?->user) {
                        $this->cashBoxLedger->alignWalletCacheIfMainBox($wallet->user);
                        $resynced = true;
                    }
                }
            }

            Log::info('Accounting transaction deleted', [
                'root_id' => $root->id,
                'children' => $children->pluck('id')->all(),
                'main_box_resynced' => $resynced,
                'deleted_by' => Auth::id(),
                'owner_id' => $ownerId,
            ]);

            return [
                'deleted_ids' => collect([$root->id])->merge($children->pluck('id'))->all(),
                'main_box_resynced' => $resynced,
            ];
        });
    }

    /**
     * Resolve parent box move + children so delete never orphans one side.
     *
     * @return array{0: Transactions, 1: \Illuminate\Support\Collection<int, Transactions>}
     */
    protected function resolvePair(Transactions $original): array
    {
        $boxTypes = ['inUserBox', 'outUserBox'];

        // Deleted from قاصة child → climb to parent box move
        if ((int) ($original->parent_id ?? 0) > 0
            && in_array($original->type, ['inUser', 'outUser'], true)
        ) {
            $parent = Transactions::find((int) $original->parent_id);
            if ($parent && in_array($parent->type, $boxTypes, true)) {
                $children = Transactions::where('parent_id', $parent->id)->get();

                return [$parent, $children];
            }
        }

        if (in_array($original->type, $boxTypes, true)) {
            $children = Transactions::where('parent_id', $original->id)->get();

            return [$original, $children];
        }

        $children = Transactions::where('parent_id', $original->id)->get();

        return [$original, $children];
    }

    /**
     * For wallets that still rely on wallets.balance (clients / expense shadows),
     * reverse only rows that lived on that wallet — never mainBox.
     */
    protected function reverseNonCashWallet(Transactions $root, $children, int $walletId): void
    {
        $wallet = Wallet::find($walletId);
        if (! $wallet) {
            return;
        }

        $rows = collect([$root])->merge($children)->where('wallet_id', $walletId);

        foreach ($rows as $row) {
            // قاصة inUser/outUser لا تحدّث balance عند الإنشاء — لا نعكسها
            if (in_array($row->type, ['inUser', 'outUser', 'inUserAmanah', 'outUserAmanah'], true)) {
                continue;
            }

            if ($row->currency === '$') {
                $wallet->decrement('balance', $row->amount);
            }
            if ($row->currency === 'IQD') {
                $wallet->decrement('balance_dinar', $row->amount);
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

    protected function deleteImages(Transactions $transaction): void
    {
        $transaction->loadMissing('TransactionsImages');
        foreach ($transaction->TransactionsImages as $transactionsImage) {
            File::delete(public_path('uploads/' . $transactionsImage->name));
            File::delete(public_path('uploadsResized/' . $transactionsImage->name));
            $transactionsImage->delete();
        }
    }
}
