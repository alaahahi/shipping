<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IranInvoiceTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'iran_invoice_transfers';

    protected $fillable = [
        'transfer_date',
        'amount',
        'currency',
        'reference_no',
        'from_text',
        'to_text',
        'notes',
        'invoice_id',
        'is_archived',
        'archived_at',
        'owner_id',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(IranInvoice::class, 'invoice_id');
    }
}
