<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarVinSearchArchive extends Model
{
    use HasFactory;

    protected $table = 'car_vin_search_archives';

    protected $fillable = [
        'owner_id',
        'user_id',
        'search_year',
        'vins_text',
        'vins_count',
        'matched_count',
        'ambiguous_count',
        'missing_count',
        'results_payload',
        'missing_vins',
    ];

    protected $casts = [
        'search_year' => 'integer',
        'vins_count' => 'integer',
        'matched_count' => 'integer',
        'ambiguous_count' => 'integer',
        'missing_count' => 'integer',
        'results_payload' => 'array',
        'missing_vins' => 'array',
    ];
}
