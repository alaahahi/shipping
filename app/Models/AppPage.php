<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AppPage extends Model
{
    protected $fillable = [
        'slug',
        'route_name',
        'path',
        'label',
        'nav_group',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function userTypes(): BelongsToMany
    {
        return $this->belongsToMany(UserType::class, 'app_page_user_type', 'app_page_id', 'user_type_id')
            ->withTimestamps();
    }
}
