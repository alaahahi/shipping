<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IranInvoiceCarrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'iran_invoice_carriers';

    protected $fillable = [
        'name',
        'phone',
        'address',
        'notes',
        'is_active',
        'owner_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function invoices()
    {
        return $this->hasMany(IranInvoice::class, 'carrier_id');
    }
}
