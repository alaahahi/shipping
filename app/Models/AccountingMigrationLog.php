<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingMigrationLog extends Model
{
    protected $fillable = [
        'owner_id',
        'legacy_key',
        'user_id',
        'wallet_id',
        'balance_dollar_before',
        'balance_dinar_before',
        'transactions_count',
        'expenses_count',
        'display_name',
        'note',
        'migrated_by',
        'dry_run',
    ];

    protected $casts = [
        'balance_dollar_before' => 'decimal:2',
        'balance_dinar_before' => 'decimal:2',
        'transactions_count' => 'integer',
        'expenses_count' => 'integer',
        'dry_run' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function migratedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'migrated_by');
    }
}
