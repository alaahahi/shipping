<?php

namespace App\Services;

use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * رصيد الصندوق (القاصة النقدية) من دفتر الحركات (transactions)
 * وليس من قيمة wallets.balance المخزّنة — لتفادي الانحراف.
 *
 * لا يمس حسابات العملاء / الشركات / الأمانة: فقط أنواع حركات الصندوق.
 */
class CashBoxLedgerService
{
    /**
     * أنواع القيود التي تحرّك رصيد الصندوق فعلياً عبر increase/decrease/debtWallet.
     */
    public const CASH_TYPES = [
        'in',
        'out',
        'debt',
        'inUserBox',
        'outUserBox',
    ];

    /**
     * رصيد دفتر الأستاذ لعملة واحدة: مجموع amount الموقّع (الموجب إيداع / السالب سحب).
     */
    public function ledgerBalance(int $walletId, string $currency = '$'): float
    {
        $total = Transactions::query()
            ->where('wallet_id', $walletId)
            ->where('currency', $currency)
            ->whereIn('type', self::CASH_TYPES)
            ->sum('amount');

        return round((float) $total, 2);
    }

    /**
     * رصيد الدفتر حتى حركة معيّنة (شاملة) — للتوثيق ونقاط المطابقة.
     */
    public function ledgerBalanceUpTo(int $walletId, string $currency, int $upToTransactionId): float
    {
        $total = Transactions::query()
            ->where('wallet_id', $walletId)
            ->where('currency', $currency)
            ->whereIn('type', self::CASH_TYPES)
            ->where('id', '<=', $upToTransactionId)
            ->sum('amount');

        return round((float) $total, 2);
    }

    /**
     * أرصدة الدولار والدينار + مقارنة بالكاش المخزّن.
     *
     * @return array{
     *   ledger_balance: float,
     *   ledger_balance_dinar: float,
     *   cached_balance: float,
     *   cached_balance_dinar: float,
     *   drift: float,
     *   drift_dinar: float
     * }
     */
    public function snapshot(Wallet $wallet): array
    {
        $ledger = $this->ledgerBalance((int) $wallet->id, '$');
        $ledgerDinar = $this->ledgerBalance((int) $wallet->id, 'IQD');
        $cached = round((float) ($wallet->balance ?? 0), 2);
        $cachedDinar = round((float) ($wallet->balance_dinar ?? 0), 2);

        return [
            'ledger_balance' => $ledger,
            'ledger_balance_dinar' => $ledgerDinar,
            'cached_balance' => $cached,
            'cached_balance_dinar' => $cachedDinar,
            'drift' => round($cached - $ledger, 2),
            'drift_dinar' => round($cachedDinar - $ledgerDinar, 2),
        ];
    }

    /**
     * مزامنة wallets.balance من دفتر القيود — للصندوق الرئيسي فقط.
     */
    public function resyncMainBox(?int $ownerId = null, bool $dryRun = false): array
    {
        $query = User::with('wallet')->where('email', 'mainBox@account.com');
        if ($ownerId !== null) {
            $query->where('owner_id', $ownerId);
        }

        $results = [];

        foreach ($query->get() as $box) {
            if (!$box->wallet) {
                $results[] = [
                    'owner_id' => $box->owner_id,
                    'user_id' => $box->id,
                    'error' => 'no_wallet',
                ];
                continue;
            }

            $wallet = $box->wallet;
            $snap = $this->snapshot($wallet);
            $changed = abs($snap['drift']) > 0.009 || abs($snap['drift_dinar']) > 0.009;

            if ($changed && !$dryRun) {
                DB::transaction(function () use ($wallet, $snap) {
                    $wallet->balance = $snap['ledger_balance'];
                    $wallet->balance_dinar = $snap['ledger_balance_dinar'];
                    $wallet->save();
                });

                Log::info('CashBoxLedger: mainBox balance resynced from ledger', [
                    'owner_id' => $box->owner_id,
                    'wallet_id' => $wallet->id,
                    'before' => $snap['cached_balance'],
                    'after' => $snap['ledger_balance'],
                    'before_dinar' => $snap['cached_balance_dinar'],
                    'after_dinar' => $snap['ledger_balance_dinar'],
                ]);
            }

            $results[] = array_merge([
                'owner_id' => $box->owner_id,
                'user_id' => $box->id,
                'wallet_id' => $wallet->id,
                'synced' => $changed && !$dryRun,
                'dry_run' => $dryRun,
            ], $snap);
        }

        return $results;
    }

    /**
     * عند فتح صفحة المحاسبة: أعِد مزامنة كاش الصندوق من الدفتر إن وُجد انحراف.
     */
    public function alignWalletCacheIfMainBox(User $user): ?array
    {
        if (($user->email ?? '') !== 'mainBox@account.com' || !$user->wallet) {
            return null;
        }

        $snap = $this->snapshot($user->wallet);
        if (abs($snap['drift']) > 0.009 || abs($snap['drift_dinar']) > 0.009) {
            $user->wallet->balance = $snap['ledger_balance'];
            $user->wallet->balance_dinar = $snap['ledger_balance_dinar'];
            $user->wallet->save();
            $user->setRelation('wallet', $user->wallet->fresh());
            $snap = $this->snapshot($user->wallet);
            $snap['auto_synced'] = true;
        } else {
            $snap['auto_synced'] = false;
        }

        return $snap;
    }
}
