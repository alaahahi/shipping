<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyerPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'buyer_payments';

    protected $fillable = [
        'buyer_id',
        'merchant_id',
        'internal_sale_id',
        'amount',
        'payment_date',
        'note',
        'owner_id',
        'created_by',
        'payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected $dates = ['deleted_at'];

    // Relations
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function internalSale()
    {
        return $this->belongsTo(InternalSale::class, 'internal_sale_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
