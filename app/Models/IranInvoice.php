<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IranInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'iran_invoices';

    protected $attributes = [
        'currency' => 'USD',
        'is_archived' => 0,
        'total_price' => 0,
    ];

    protected $fillable = [
        'invoice_no',
        'verification_token',
        'invoice_date',
        'carrier_id',
        'consignee_id',
        'carrier_name',
        'consignee_name',
        'destination',
        'notes',
        'total_price',
        'currency',
        'is_archived',
        'archived_at',
        'owner_id',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(IranInvoiceItem::class, 'invoice_id')->orderBy('sort_order');
    }

    public function carrier()
    {
        return $this->belongsTo(IranInvoiceCarrier::class, 'carrier_id');
    }

    public function consignee()
    {
        return $this->belongsTo(IranInvoiceConsignee::class, 'consignee_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(IranInvoiceAttachment::class, 'attachable');
    }

    public function transfers()
    {
        return $this->hasMany(IranInvoiceTransfer::class, 'invoice_id');
    }

    public function toArray()
    {
        $array = parent::toArray();

        if ($this->invoice_date) {
            $array['invoice_date'] = $this->invoice_date->format('Y-m-d');
        }

        return $array;
    }
}
