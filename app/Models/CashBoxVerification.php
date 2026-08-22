<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBoxVerification extends Model
{
    protected $fillable = [
        'owner_id',
        'wallet_id',
        'transaction_id',
        'ledger_balance_at_confirm',
        'ledger_balance_dinar_at_confirm',
        'note',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'ledger_balance_at_confirm' => 'float',
        'ledger_balance_dinar_at_confirm' => 'float',
        'verified_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transactions::class, 'transaction_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
