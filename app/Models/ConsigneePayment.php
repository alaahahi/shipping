<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsigneePayment extends Model
{
    use HasFactory;

    protected $table = 'consignee_payments';

    protected $fillable = [
        'consignee_id',
        'trip_id',
        'amount',
        'currency',
        'notes',
        'payment_date',
        'receipt_number',
        'owner_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // العلاقات
    public function consignee()
    {
        return $this->belongsTo(User::class, 'consignee_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
