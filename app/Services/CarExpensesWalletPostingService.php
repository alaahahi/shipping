<?php

namespace App\Services;

use App\Http\Controllers\AccountingController;
use App\Models\SystemConfig;
use App\Models\Transactions;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CarExpensesWalletPostingService
{
    public function __construct(
        protected AccountingController $accountingController,
        protected AccountingCacheService $accounting
    ) {}

    public function defaultWalletUser(): ?User
    {
        $id = SystemConfig::query()->value('car_expenses_wallet_user_id');
        if (! $id) {
            return null;
        }

        return User::with('wallet')
            ->where('owner_id', Auth::user()->owner_id)
            ->where('id', (int) $id)
            ->whereHas('wallet')
            ->first();
    }

    /**
     * Manual قاسة withdrawal for registration totals (same pattern as salesDebtUser).
     *
     * @return array{wallet: User, amount_dollar: int, amount_dinar: int, description: string}
     */
    public function postTotalsToDefaultWallet(int $amountDollar, int $amountDinar, string $note): array
    {
        $amountDollar = max(0, (int) $amountDollar);
        $amountDinar = max(0, (int) $amountDinar);

        if ($amountDollar <= 0 && $amountDinar <= 0) {
            throw new RuntimeException('لا يوجد مبلغ للترحيل');
        }

        $walletUser = $this->defaultWalletUser();
        if (! $walletUser || ! $walletUser->wallet) {
            throw new RuntimeException('حدد قاصة الترحيل الافتراضية من إعدادات النظام أولاً');
        }

        $this->accounting->loadAccounts(Auth::user()->owner_id);
        $mainBoxId = $this->accounting->mainBox()->id;

        $desc = 'وصل سحب مباشر'.' '.'قاسه'.' '.$walletUser->name.' '.$note;
        $date = Carbon::now()->format('Y-m-d');

        DB::transaction(function () use ($walletUser, $amountDollar, $amountDinar, $desc, $date, $mainBoxId) {
            if ($amountDollar > 0) {
                $parent = $this->accountingController->debtWallet(
                    $amountDollar,
                    $desc,
                    $mainBoxId,
                    $walletUser->id,
                    User::class,
                    0,
                    0,
                    '$',
                    $date,
                    0,
                    'outUserBox'
                );
                Transactions::create([
                    'type' => 'outUser',
                    'wallet_id' => $walletUser->wallet->id,
                    'description' => $desc,
                    'amount' => $amountDollar,
                    'is_pay' => 1,
                    'morphed_id' => $walletUser->id,
                    'morphed_type' => User::class,
                    'user_added' => 0,
                    'created' => $date,
                    'discount' => 0,
                    'currency' => '$',
                    'parent_id' => $parent->id,
                ]);
            }

            if ($amountDinar > 0) {
                $parent = $this->accountingController->debtWallet(
                    $amountDinar,
                    $desc,
                    $mainBoxId,
                    $walletUser->id,
                    User::class,
                    0,
                    0,
                    'IQD',
                    $date,
                    0,
                    'outUserBox'
                );
                Transactions::create([
                    'type' => 'outUser',
                    'wallet_id' => $walletUser->wallet->id,
                    'description' => $desc,
                    'amount' => $amountDinar,
                    'is_pay' => 1,
                    'morphed_id' => $walletUser->id,
                    'morphed_type' => User::class,
                    'user_added' => 0,
                    'created' => $date,
                    'discount' => 0,
                    'currency' => 'IQD',
                    'parent_id' => $parent->id,
                ]);
            }
        });

        return [
            'wallet' => $walletUser,
            'amount_dollar' => $amountDollar,
            'amount_dinar' => $amountDinar,
            'description' => $desc,
        ];
    }
}
