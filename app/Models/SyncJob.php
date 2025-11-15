<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncJob extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection;

    protected $table = 'sync_jobs';

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    protected $fillable = [
        'model',
        'operation',
        'payload',
        'created_at',
        'synced_at',
        'uuid',
        'attempts',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('sync.local_connection', $this->connection);
    }
}

