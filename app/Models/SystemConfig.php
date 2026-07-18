<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    use HasFactory;

    protected $table = 'system_config';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'first_title_ar',
        'first_title_kr',
        'second_title_ar',
        'second_title_kr',
        'third_title_ar',
        'third_title_kr',
        'default_price_s',
        'default_price_p',
        'usd_to_aed_rate',
        'usd_to_dinar_rate',
        'contract_terms',
        'contract_terms_2',
        'external_contract_terms',
        'external_contract_terms_2',
        'contract_template',
        'contract_currency',
        'primary_color',
        'logo',
    ];
    protected $casts = [
        'default_price_s' => 'array',
        'default_price_p' => 'array',
        'contract_terms' => 'array',
        'contract_terms_2' => 'array',
        'external_contract_terms' => 'array',
        'external_contract_terms_2' => 'array',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute(): string
    {
        // Pass '' when null so we do not hit the DB again for an already-loaded model.
        return self::resolveLogoUrl($this->logo ?? '');
    }

    public static function resolveLogoUrl(?string $logo = null): string
    {
        $path = $logo;
        if ($path === null) {
            try {
                $path = static::query()->value('logo');
            } catch (\Throwable $e) {
                $path = null;
            }
        }

        if (is_string($path) && $path !== '' && is_file(public_path($path))) {
            return '/'.ltrim($path, '/');
        }

        if (is_file(public_path('img/logo.png'))) {
            return '/img/logo.png';
        }

        return '/img/logo.jpg';
    }
}
