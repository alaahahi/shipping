<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class IranInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'iran_invoice_items';

    protected $attributes = [
        'sort_order' => 0,
    ];

    protected $fillable = [
        'invoice_id',
        'car_id',
        'chassis_no',
        'make',
        'model',
        'year',
        'color',
        'weight',
        'unit_price',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(IranInvoice::class, 'invoice_id');
    }

    public function car()
    {
        return $this->belongsTo(IranInvoiceCar::class, 'car_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(IranInvoiceAttachment::class, 'attachable');
    }
}
