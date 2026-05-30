<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IranInvoiceAttachment extends Model
{
    use HasFactory;

    protected $table = 'iran_invoice_attachments';

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'file_name',
        'original_name',
        'owner_id',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }
}
