<?php

namespace App\Services;

use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Reassign a قاصة wallet payment (inUser/outUser/Amanah) to another user's wallet,
 * or assign a direct mainBox movement (in/out/debt) to a customer قاصة —
 * without deleting history. Parent mainBox rows stay on الصندوق; ledger amount unchanged.
 */
class TransferWalletTransactionService
{
    private const CHILD_TYPES = ['inUser', 'outUser', 'inUserAmanah', 'outUserAmanah'];

    private const BOX_TYPES = ['inUserBox', 'outUserBox'];

    private const DIRECT_BOX_TYPES = ['in', 'out', 'debt'];

    public function __construct(
        protected AccountingCacheService $accounting
    ) {
    }

    /**
     * @return array{
     *   transaction: Transactions,
     *   parent_updated: bool,
     *   from_user_id: int|null,
     *   to_user_id: int,
     *   mode: string
     * }
     */
    public function transfer(
        int $transactionId,
        int $targetUserId,
        int $ownerId,
        ?string $note = null,
        ?int $actedBy = null
    ): array {
        $note = trim((string) ($note ?? '')) ?: 'استئناس';
        $actedBy = $actedBy ?? Auth::id();

        return DB::transaction(function () use ($transactionId, $targetUserId, $ownerId, $note, $actedBy) {
            $this->accounting->loadAccounts($ownerId);

            $transaction = Transactions::with('wallet.user')->find($transactionId);

            if (! $transaction) {
                throw new RuntimeException('Transaction not found.');
            }

            if (in_array($transaction->type, self::CHILD_TYPES, true)) {
                return $this->transferChildBetweenWallets(
                    $transaction,
                    $targetUserId,
                    $ownerId,
                    $note,
                    $actedBy
                );
            }

            if (in_array($transaction->type, self::DIRECT_BOX_TYPES, true)
                || in_array($transaction->type, self::BOX_TYPES, true)) {
                return $this->assignOrReassignBoxTransaction(
                    $transaction,
                    $targetUserId,
                    $ownerId,
                    $note,
                    $actedBy
                );
            }

            throw new RuntimeException('يمكن نقل حركات القاصة أو حركات الصندوق المباشرة فقط.');
        });
    }

    /**
     * قاصة→قاسة: move inUser/outUser/Amanah child and sync parent box description.
     *
     * @return array{transaction: Transactions, parent_updated: bool, from_user_id: int, to_user_id: int, mode: string}
     */
    protected function transferChildBetweenWallets(
        Transactions $transaction,
        int $targetUserId,
        int $ownerId,
        string $note,
        int $actedBy
    ): array {
        $sourceUser = $transaction->wallet?->user;
        if (! $sourceUser || (int) $sourceUser->owner_id !== (int) $ownerId) {
            throw new RuntimeException('Not authorized to transfer this transaction.');
        }

        if ((int) $sourceUser->id === (int) $targetUserId) {
            throw new RuntimeException('القاسة الهدف هي نفسها المصدر.');
        }

        $targetUser = $this->resolveTargetCustomerUser($targetUserId, $ownerId);

        $existingDetails = is_array($transaction->details) ? $transaction->details : [];
        $isGenExpense = ! empty($existingDetails['gen_expense_box']);

        $auditDetails = array_merge($existingDetails, [
            'transferred_from_user_id' => (int) $sourceUser->id,
            'transferred_to_user_id' => (int) $targetUser->id,
            'transferred_from_wallet_id' => (int) $transaction->wallet_id,
            'transferred_to_wallet_id' => (int) $targetUser->wallet->id,
            'transferred_at' => now()->toIso8601String(),
            'transfer_note' => $note,
            'استئناس' => $note,
            'transferred_by' => $actedBy,
            'transfer_mode' => 'qasa_to_qasa',
        ]);

        if ($isGenExpense) {
            $auditDetails['assigned_wallet_user_id'] = (int) $targetUser->id;
        }

        $newDescription = $this->rewriteQasaDescription(
            $transaction->description,
            $sourceUser->name,
            $targetUser->name
        );

        $originalCreated = $transaction->created;
        $originalCreatedAt = $transaction->created_at;

        $transaction->wallet_id = $targetUser->wallet->id;
        $transaction->description = $newDescription;
        $transaction->details = $auditDetails;

        if (! $isGenExpense && $this->isUserMorph($transaction)) {
            $transaction->morphed_id = $targetUser->id;
            $transaction->morphed_type = User::class;
        }

        $transaction->save();
        $this->restoreCreatedDates($transaction, $originalCreated, $originalCreatedAt);

        $parentUpdated = false;
        $parent = $this->resolveParentBox($transaction);
        if ($parent) {
            $parentDetails = is_array($parent->details) ? $parent->details : [];
            $parentIsGenExpense = ! empty($parentDetails['gen_expense_box']) || $isGenExpense;

            $parent->description = $this->rewriteQasaDescription(
                $parent->description ?: $transaction->description,
                $sourceUser->name,
                $targetUser->name
            );

            $parentAudit = array_merge($parentDetails, [
                'transferred_from_user_id' => (int) $sourceUser->id,
                'transferred_to_user_id' => (int) $targetUser->id,
                'transferred_at' => now()->toIso8601String(),
                'transfer_note' => $note,
                'استئناس' => $note,
                'transferred_by' => $actedBy,
                'child_transaction_id' => (int) $transaction->id,
                'transfer_mode' => 'qasa_to_qasa',
            ]);

            if ($parentIsGenExpense) {
                $parentAudit['gen_expense_box'] = true;
                $parentAudit['assigned_wallet_user_id'] = (int) $targetUser->id;
            } elseif ($this->isUserMorph($parent)) {
                $parent->morphed_id = $targetUser->id;
                $parent->morphed_type = User::class;
            }

            $parent->details = $parentAudit;
            $parentCreated = $parent->created;
            $parentCreatedAt = $parent->created_at;
            $parent->save();
            $this->restoreCreatedDates($parent, $parentCreated, $parentCreatedAt);

            $parentUpdated = true;
        }

        Log::info('Wallet transaction transferred', [
            'transaction_id' => $transaction->id,
            'parent_id' => $parent?->id,
            'from_user_id' => $sourceUser->id,
            'to_user_id' => $targetUser->id,
            'note' => $note,
            'transferred_by' => $actedBy,
            'owner_id' => $ownerId,
            'mode' => 'qasa_to_qasa',
        ]);

        return [
            'transaction' => $transaction->fresh(['wallet.user']),
            'parent_updated' => $parentUpdated,
            'from_user_id' => (int) $sourceUser->id,
            'to_user_id' => (int) $targetUser->id,
            'mode' => 'qasa_to_qasa',
        ];
    }

    /**
     * Direct الصندوق (in/out/debt) or already-typed box (inUserBox/outUserBox):
     * assign to قاصة, or re-target existing child.
     *
     * @return array{transaction: Transactions, parent_updated: bool, from_user_id: int|null, to_user_id: int, mode: string}
     */
    protected function assignOrReassignBoxTransaction(
        Transactions $transaction,
        int $targetUserId,
        int $ownerId,
        string $note,
        int $actedBy
    ): array {
        $mainBox = $this->accounting->mainBox();
        $mainBoxWalletId = $mainBox?->wallet?->id;

        if (! $mainBoxWalletId) {
            throw new RuntimeException('لم يتم العثور على صندوق المحاسبة');
        }

        if ((int) $transaction->wallet_id !== (int) $mainBoxWalletId) {
            throw new RuntimeException('هذه الحركة ليست من صندوق المحاسبة');
        }

        if ((int) ($transaction->parent_id ?? 0) > 0) {
            throw new RuntimeException('لا يمكن تحويل حركة مرتبطة بحركة أخرى');
        }

        $isDeposit = in_array($transaction->type, ['in', 'inUserBox'], true);
        $expectedChild = $isDeposit ? 'inUser' : 'outUser';

        $child = Transactions::where('parent_id', $transaction->id)
            ->where('type', $expectedChild)
            ->orderBy('id')
            ->first();

        // Already assigned → re-target via child transfer (same as قاصة→قاسة)
        if ($child) {
            $result = $this->transferChildBetweenWallets(
                $child->load('wallet.user'),
                $targetUserId,
                $ownerId,
                $note,
                $actedBy
            );
            $result['mode'] = 'box_retarget';
            $result['transaction'] = $transaction->fresh(['wallet.user']);
            $result['parent_updated'] = true;

            return $result;
        }

        return $this->assignDirectBoxToWallet(
            $transaction,
            $targetUserId,
            $ownerId,
            $note,
            $actedBy,
            $mainBox
        );
    }

    /**
     * Direct / unassigned box movement → inUserBox/outUserBox + child on target قاصة.
     * Amount and mainBox wallet_id stay the same → ledger balance unchanged.
     *
     * @return array{transaction: Transactions, parent_updated: bool, from_user_id: int|null, to_user_id: int, mode: string}
     */
    protected function assignDirectBoxToWallet(
        Transactions $transaction,
        int $targetUserId,
        int $ownerId,
        string $note,
        int $actedBy,
        ?User $mainBox = null
    ): array {
        $mainBox = $mainBox ?? $this->accounting->mainBox();

        $targetUser = $this->resolveTargetCustomerUser($targetUserId, $ownerId);

        if ((int) $targetUser->id === (int) $mainBox->id) {
            throw new RuntimeException('لا يمكن إسناد الحركة إلى الصندوق نفسه');
        }

        $amountAbs = abs((float) $transaction->amount);
        if ($amountAbs <= 0) {
            throw new RuntimeException('مبلغ الحركة غير صالح');
        }

        $originalType = $transaction->type;
        $isDeposit = in_array($originalType, ['in', 'inUserBox'], true);
        $boxType = $isDeposit ? 'inUserBox' : 'outUserBox';
        $childType = $isDeposit ? 'inUser' : 'outUser';
        $childAmount = $amountAbs;

        $originalDescription = trim((string) ($transaction->description ?? ''));
        $genExpenseAccountUserId = $this->resolveGenExpenseAccountUserId($originalDescription);
        $existingDetails = is_array($transaction->details) ? $transaction->details : [];

        if ($genExpenseAccountUserId) {
            $description = $originalDescription;
            $morphedId = $genExpenseAccountUserId;
            $childDetails = array_merge($existingDetails, [
                'gen_expense_box' => true,
                'assigned_wallet_user_id' => $targetUser->id,
            ]);
        } else {
            $description = $this->buildAssignedBoxDescription(
                $originalDescription,
                $targetUser->name,
                $isDeposit
            );
            $morphedId = $targetUser->id;
            $childDetails = $existingDetails;
        }

        $auditBase = array_merge($childDetails, [
            'assigned_from_direct_box' => true,
            'original_box_type' => $originalType,
            'transferred_from_user_id' => (int) ($mainBox->id ?? 0),
            'transferred_to_user_id' => (int) $targetUser->id,
            'transferred_to_wallet_id' => (int) $targetUser->wallet->id,
            'transferred_at' => now()->toIso8601String(),
            'transfer_note' => $note,
            'استئناس' => $note,
            'transferred_by' => $actedBy,
            'transfer_mode' => 'box_to_qasa',
            'amount' => $amountAbs,
        ]);

        $originalCreated = $transaction->created;
        $originalCreatedAt = $transaction->created_at;

        $transaction->type = $boxType;
        $transaction->morphed_id = $morphedId;
        $transaction->morphed_type = User::class;
        $transaction->description = $description;
        $transaction->details = $auditBase;
        // wallet_id + amount unchanged — mainBox ledger stays the same
        $transaction->save();
        $this->restoreCreatedDates($transaction, $originalCreated, $originalCreatedAt);

        $createdDate = $originalCreated
            ?: ($originalCreatedAt ? $originalCreatedAt->format('Y-m-d') : now()->format('Y-m-d'));

        $child = Transactions::create([
            'type' => $childType,
            'wallet_id' => $targetUser->wallet->id,
            'description' => $description,
            'amount' => $childAmount,
            'is_pay' => $transaction->is_pay,
            'morphed_id' => $morphedId,
            'morphed_type' => User::class,
            'user_added' => 0,
            'created' => $createdDate,
            'discount' => $transaction->discount ?? 0,
            'currency' => $transaction->currency,
            'parent_id' => $transaction->id,
            'details' => array_merge($auditBase, [
                'child_of_box_transaction_id' => (int) $transaction->id,
            ]),
            'tag' => $transaction->tag,
        ]);

        if ($originalCreatedAt) {
            $child->created_at = $originalCreatedAt;
            $child->updated_at = $originalCreatedAt;
            $child->saveQuietly();
        }

        $boxDetails = is_array($transaction->details) ? $transaction->details : [];
        $transaction->details = array_merge($boxDetails, [
            'child_transaction_id' => (int) $child->id,
        ]);
        $transaction->saveQuietly();
        $this->restoreCreatedDates($transaction, $originalCreated, $originalCreatedAt);

        Log::info('Direct box transaction assigned to wallet', [
            'transaction_id' => $transaction->id,
            'child_id' => $child->id,
            'to_user_id' => $targetUser->id,
            'original_type' => $originalType,
            'new_type' => $boxType,
            'note' => $note,
            'transferred_by' => $actedBy,
            'owner_id' => $ownerId,
            'mode' => 'box_to_qasa',
        ]);

        return [
            'transaction' => $transaction->fresh(['wallet.user']),
            'parent_updated' => false,
            'from_user_id' => (int) ($mainBox->id ?? 0),
            'to_user_id' => (int) $targetUser->id,
            'mode' => 'box_to_qasa',
        ];
    }

    protected function resolveTargetCustomerUser(int $targetUserId, int $ownerId): User
    {
        $targetUser = User::with('wallet')
            ->where('id', $targetUserId)
            ->where('owner_id', $ownerId)
            ->first();

        if (! $targetUser) {
            throw new RuntimeException('القاسة الهدف غير موجودة.');
        }

        $clientTypeId = $this->accounting->userClient();
        if ($clientTypeId && (int) $targetUser->type_id !== (int) $clientTypeId) {
            throw new RuntimeException('يمكن النقل فقط إلى قاصات الزبائن.');
        }

        if ($this->isSystemAccountEmail($targetUser->email)) {
            throw new RuntimeException('لا يمكن نقل الحركة إلى حساب نظام محاسبي.');
        }

        if (! $targetUser->wallet) {
            Wallet::create([
                'user_id' => $targetUser->id,
                'balance' => 0,
                'balance_dinar' => 0,
            ]);
            $targetUser->load('wallet');
        }

        return $targetUser;
    }

    protected function buildAssignedBoxDescription(
        string $originalDescription,
        string $targetName,
        bool $isDeposit
    ): string {
        $prefix = $isDeposit ? 'وصل قبض مباشر' : 'وصل سحب مباشر';
        $noteSuffix = $originalDescription;

        if ($isDeposit) {
            if (preg_match('/وصل\s+قبض\s+مباشر\s*(.*)/u', $noteSuffix, $matches)) {
                $noteSuffix = trim($matches[1]);
            }
        } else {
            if (preg_match('/سحب\s+دفعة\s*(.*)/u', $noteSuffix, $matches)) {
                $noteSuffix = trim($matches[1]);
            } elseif (preg_match('/وصل\s+سحب\s+مباشر\s*(.*)/u', $noteSuffix, $matches)) {
                $noteSuffix = trim($matches[1]);
            }
        }

        $noteSuffix = preg_replace('/^قاسه\s+\S+\s*/u', '', $noteSuffix) ?? $noteSuffix;
        $noteSuffix = trim($noteSuffix);

        return $prefix.' قاسه '.$targetName.($noteSuffix !== '' ? ' '.$noteSuffix : '');
    }

    protected function resolveGenExpenseAccountUserId(?string $description): ?int
    {
        if (! $description) {
            return null;
        }

        $account = null;
        if (preg_match('/مصاريف\s+أربيل/ui', $description)) {
            $account = $this->accounting->howler();
        } elseif (preg_match('/مصاريف\s+دبي/ui', $description)) {
            $account = $this->accounting->dubai();
        } elseif (preg_match('/مصاريف\s+ايران/ui', $description) || preg_match('/مصاريف\s+إيران/ui', $description)) {
            $account = $this->accounting->iran();
        } elseif (preg_match('/مصاريف\s+الحدود/ui', $description)) {
            $account = $this->accounting->border();
        } elseif (preg_match('/مصاريف\s+شهادة\s*coc/ui', $description)) {
            $account = $this->accounting->shippingCoc();
        }

        return $account?->id;
    }

    protected function resolveParentBox(Transactions $child): ?Transactions
    {
        $parentId = (int) ($child->parent_id ?? 0);
        if ($parentId <= 0) {
            return null;
        }

        $parent = Transactions::find($parentId);
        if (! $parent || ! in_array($parent->type, self::BOX_TYPES, true)) {
            return null;
        }

        return $parent;
    }

    protected function restoreCreatedDates(
        Transactions $tx,
        mixed $originalCreated,
        mixed $originalCreatedAt
    ): void {
        if (! $originalCreatedAt) {
            return;
        }

        $tx->created = $originalCreated;
        $tx->created_at = $originalCreatedAt;
        $tx->saveQuietly();
    }

    protected function rewriteQasaDescription(?string $description, string $fromName, string $toName): string
    {
        $description = trim((string) $description);
        if ($description === '' || $fromName === '' || $fromName === $toName) {
            return $description;
        }

        $pattern = '/(قاسه|قاسة)\s+' . preg_quote($fromName, '/') . '/u';
        $replaced = preg_replace($pattern, '$1 ' . $toName, $description, 1);

        if (is_string($replaced) && $replaced !== $description) {
            return $replaced;
        }

        $pos = mb_strpos($description, $fromName);
        if ($pos !== false) {
            return mb_substr($description, 0, $pos)
                . $toName
                . mb_substr($description, $pos + mb_strlen($fromName));
        }

        return rtrim($description) . ' [نُقلت من قاسة ' . $fromName . ' إلى ' . $toName . ']';
    }

    protected function isUserMorph(Transactions $tx): bool
    {
        $type = (string) ($tx->morphed_type ?? '');

        return $type === User::class
            || $type === 'App\\Models\\User'
            || $type === 'App\Models\User';
    }

    protected function isSystemAccountEmail(?string $email): bool
    {
        if ($email === null || $email === '') {
            return false;
        }

        if (str_ends_with(strtolower($email), '@account.com')) {
            return true;
        }

        return in_array($email, $this->accounting->systemAccountEmails(), true);
    }
}
