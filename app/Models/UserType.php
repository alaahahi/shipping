<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    use HasFactory;

    protected $table = 'user_type';

    protected $fillable = [
        'id',
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'type_id');
    }

    public function appPages(): BelongsToMany
    {
        return $this->belongsToMany(AppPage::class, 'app_page_user_type', 'user_type_id', 'app_page_id')
            ->withTimestamps();
    }
}
