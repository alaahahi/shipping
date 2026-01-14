<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectedSystem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'domain',
        'api_key',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function transfers()
    {
        return $this->hasMany(Transfers::class, 'external_system_id');
    }
}
