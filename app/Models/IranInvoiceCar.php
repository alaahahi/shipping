<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IranInvoiceCar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'iran_invoice_cars';

    protected $fillable = [
        'chassis_no',
        'make',
        'model',
        'year',
        'color',
        'weight',
        'notes',
        'carrier_id',
        'consignee_id',
        'owner_id',
        'created_by',
    ];

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
}
