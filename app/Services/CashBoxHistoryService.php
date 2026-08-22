<?php

namespace App\Services;

use App\Models\CashBoxVerification;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * تاريخ رصيد الصندوق (mainBox) من دفتر الحركات + توثيق مطابقة الكاش الفعلي.
 * لا يمس محافظ العملاء / الشركات.
 */
class CashBoxHistoryService
{
    public function __construct(
        protected CashBoxLedgerService $cashBoxLedger
    ) {
    }

    /**
     * @return array{
     *   transactions: array<int, array<string, mixed>>,
     *   pagination: array<string, mixed>,
     *   current_ledger: array<string, mixed>,
     *   latest_verification: ?array<string, mixed>,
     *   verified_until_transaction_id: ?int,
     *   can_verify: bool,
     *   main_box: array<string, mixed>
     * }
     */
    public function history(int $ownerId, int $page = 1, int $perPage = 50, ?int $beforeId = null): array
    {
        [$box, $wallet] = $this->resolveMainBox($ownerId);
        $snap = $this->cashBoxLedger->snapshot($wallet);

        $latest = $this->latestVerification((int) $ownerId, (int) $wallet->id);
        $cutoffId = $latest?->transaction_id;

        $base = Transactions::query()
            ->where('wallet_id', $wallet->id)
            ->whereIn('type', CashBoxLedgerService::CASH_TYPES);

        $perPage = max(1, min(100, $perPage));
        $page = max(1, $page);

        $query = (clone $base)->orderByDesc('id');

        // Cursor mode (Load more): rows older than the last visible id.
        if ($beforeId !== null && $beforeId > 0) {
            $query->where('id', '<', $beforeId);
            $batch = $query->limit($perPage + 1)->get();
            $hasMore = $batch->count() > $perPage;
            $items = $batch->take($perPage)->values();
            $total = (clone $base)->count();
            $currentPage = $page;
            $lastPage = max(1, (int) ceil($total / $perPage));
        } else {
            $total = (clone $base)->count();
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = min($page, $lastPage);
            $items = (clone $base)
                ->orderByDesc('id')
                ->forPage($currentPage, $perPage)
                ->get();
            $hasMore = $currentPage < $lastPage;
        }

        // Eager-load morphs safely (invalid morph classes must not break pagination).
        try {
            $items->loadMissing(['morphed']);
        } catch (\Throwable) {
            // keep rows without morphed names
        }

        $rows = [];

        if ($items->isNotEmpty()) {
            $maxId = (int) $items->max('id');

            $sumDollarAfter = (float) (clone $base)
                ->where('currency', '$')
                ->where('id', '>', $maxId)
                ->sum('amount');
            $sumDinarAfter = (float) (clone $base)
                ->where('currency', 'IQD')
                ->where('id', '>', $maxId)
                ->sum('amount');

            $runningDollar = round((float) $snap['ledger_balance'] - $sumDollarAfter, 2);
            $runningDinar = round((float) $snap['ledger_balance_dinar'] - $sumDinarAfter, 2);

            foreach ($items as $tx) {
                $amount = (float) $tx->amount;
                $isIn = $amount >= 0;
                $morphedName = null;
                try {
                    $morphedName = $tx->morphed->name ?? null;
                } catch (\Throwable) {
                    $morphedName = null;
                }

                $rows[] = [
                    'id' => (int) $tx->id,
                    'created_at' => $tx->created_at,
                    'created' => $tx->created,
                    'description' => $tx->description,
                    'type' => $tx->type,
                    'currency' => $tx->currency,
                    'amount' => $amount,
                    'amount_abs' => abs($amount),
                    'direction' => $isIn ? 'in' : 'out',
                    'morphed_name' => $morphedName,
                    'running_balance' => $runningDollar,
                    'running_balance_dinar' => $runningDinar,
                    'is_trusted' => $cutoffId !== null && (int) $tx->id <= (int) $cutoffId,
                    'is_verification_point' => $cutoffId !== null && (int) $tx->id === (int) $cutoffId,
                ];

                if (($tx->currency ?? '$') === 'IQD') {
                    $runningDinar = round($runningDinar - $amount, 2);
                } else {
                    $runningDollar = round($runningDollar - $amount, 2);
                }
            }
        }

        return [
            'transactions' => $rows,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => (bool) $hasMore,
            ],
            'current_ledger' => [
                'ledger_balance' => $snap['ledger_balance'],
                'ledger_balance_dinar' => $snap['ledger_balance_dinar'],
                'cached_balance' => $snap['cached_balance'],
                'cached_balance_dinar' => $snap['cached_balance_dinar'],
                'drift' => $snap['drift'],
                'drift_dinar' => $snap['drift_dinar'],
            ],
            'latest_verification' => $latest ? $this->serializeVerification($latest) : null,
            'verified_until_transaction_id' => $cutoffId ? (int) $cutoffId : null,
            'can_verify' => $this->userCanVerify(Auth::user()),
            'main_box' => [
                'user_id' => (int) $box->id,
                'wallet_id' => (int) $wallet->id,
                'name' => $box->name,
            ],
        ];
    }

    /**
     * توثيق مطابقة حركة مع الكاش الفعلي — كل الحركات ذات id <= هذه الحركة تصبح موثوقة.
     */
    public function verify(int $ownerId, int $transactionId, int $verifiedBy, ?string $note = null): CashBoxVerification
    {
        if (! $this->userCanVerify(Auth::user())) {
            throw new RuntimeException('غير مصرح: توثيق رصيد الصندوق للمسؤول فقط.');
        }

        return DB::transaction(function () use ($ownerId, $transactionId, $verifiedBy, $note) {
            [, $wallet] = $this->resolveMainBox($ownerId);

            $tx = Transactions::query()
                ->where('id', $transactionId)
                ->where('wallet_id', $wallet->id)
                ->whereIn('type', CashBoxLedgerService::CASH_TYPES)
                ->first();

            if (! $tx) {
                throw new RuntimeException('الحركة غير موجودة ضمن حركات الصندوق.');
            }

            $ledgerDollar = $this->cashBoxLedger->ledgerBalanceUpTo((int) $wallet->id, '$', (int) $tx->id);
            $ledgerDinar = $this->cashBoxLedger->ledgerBalanceUpTo((int) $wallet->id, 'IQD', (int) $tx->id);

            $verification = CashBoxVerification::create([
                'owner_id' => $ownerId,
                'wallet_id' => $wallet->id,
                'transaction_id' => $tx->id,
                'ledger_balance_at_confirm' => $ledgerDollar,
                'ledger_balance_dinar_at_confirm' => $ledgerDinar,
                'note' => $note,
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
            ]);

            Log::info('Cash box balance verified against physical cash', [
                'verification_id' => $verification->id,
                'owner_id' => $ownerId,
                'wallet_id' => $wallet->id,
                'transaction_id' => $tx->id,
                'ledger_balance_at_confirm' => $ledgerDollar,
                'ledger_balance_dinar_at_confirm' => $ledgerDinar,
                'verified_by' => $verifiedBy,
                'note' => $note,
            ]);

            return $verification->load('verifier');
        });
    }

    public function userCanVerify(?User $user): bool
    {
        return $user !== null && (int) $user->type_id === 1;
    }

    /**
     * @return array{0: User, 1: Wallet}
     */
    protected function resolveMainBox(int $ownerId): array
    {
        $box = User::with('wallet')
            ->where('owner_id', $ownerId)
            ->where('email', 'mainBox@account.com')
            ->first();

        if (! $box || ! $box->wallet) {
            throw new RuntimeException('حساب الصندوق غير موجود.');
        }

        return [$box, $box->wallet];
    }

    protected function latestVerification(int $ownerId, int $walletId): ?CashBoxVerification
    {
        return CashBoxVerification::query()
            ->with('verifier')
            ->where('owner_id', $ownerId)
            ->where('wallet_id', $walletId)
            ->orderByDesc('verified_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeVerification(CashBoxVerification $v): array
    {
        return [
            'id' => (int) $v->id,
            'transaction_id' => (int) $v->transaction_id,
            'ledger_balance_at_confirm' => (float) $v->ledger_balance_at_confirm,
            'ledger_balance_dinar_at_confirm' => (float) $v->ledger_balance_dinar_at_confirm,
            'note' => $v->note,
            'verified_by' => $v->verified_by ? (int) $v->verified_by : null,
            'verified_by_name' => $v->verifier->name ?? null,
            'verified_at' => $v->verified_at,
        ];
    }
}
