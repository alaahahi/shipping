<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        'login_background',
    ];

    protected $attributes = [
        'usd_to_aed_rate' => 3.67,
        'usd_to_dinar_rate' => 150,
        'contract_template' => 1,
        'contract_currency' => 'usd',
        'primary_color' => '#c00',
        'first_title_ar' => '',
        'first_title_kr' => '',
        'second_title_ar' => '',
        'second_title_kr' => '',
        'third_title_ar' => '',
        'third_title_kr' => '',
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
        'login_background_url',
    ];

    /**
     * Ensure media columns exist (covers SQLite prod when migrate was skipped).
     */
    public static function ensureMediaColumns(): void
    {
        try {
            if (! Schema::hasTable('system_config')) {
                return;
            }

            if (! Schema::hasColumn('system_config', 'logo')) {
                Schema::table('system_config', function (Blueprint $table) {
                    $table->string('logo', 255)->nullable();
                });
            }

            if (! Schema::hasColumn('system_config', 'login_background')) {
                Schema::table('system_config', function (Blueprint $table) {
                    $table->string('login_background', 255)->nullable();
                });
            }
        } catch (\Throwable $e) {
            // Ignore — uploads will still surface a clear error if schema cannot change.
        }
    }

    public function getLogoUrlAttribute(): string
    {
        $stored = $this->attributes['logo'] ?? null;

        return self::resolveLogoUrl(
            (is_string($stored) && $stored !== '') ? $stored : null
        );
    }

    public function getLoginBackgroundUrlAttribute(): ?string
    {
        $stored = $this->attributes['login_background'] ?? null;

        return self::resolveLoginBackgroundUrl(
            (is_string($stored) && $stored !== '') ? $stored : null
        );
    }

    /**
     * Locate a system media file on disk (supports legacy upload folders).
     */
    public static function resolveMediaAbsolutePath(?string $storedPath): ?string
    {
        if (! is_string($storedPath) || trim($storedPath) === '') {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', trim($storedPath)), '/');
        $basename = basename($relative);

        $candidates = array_values(array_unique([
            public_path($relative),
            public_path('img/system/'.$basename),
            public_path('uploads/system/'.$basename),
            public_path('storage/system/'.$basename),
        ]));

        foreach ($candidates as $absolute) {
            if (is_file($absolute)) {
                return $absolute;
            }
        }

        return null;
    }

    /**
     * Public URL served via Laravel route (works even when static /uploads is blocked).
     */
    public static function mediaServeUrl(?string $storedPath): ?string
    {
        if (! is_string($storedPath) || trim($storedPath) === '') {
            return null;
        }

        $basename = basename(str_replace('\\', '/', trim($storedPath)));
        if ($basename === '' || $basename === '.' || $basename === '..') {
            return null;
        }

        $absolute = self::resolveMediaAbsolutePath($storedPath);
        $url = '/media/system/'.$basename;
        if ($absolute) {
            $url .= '?v='.filemtime($absolute);
        }

        return $url;
    }

    /**
     * URL الشعار من system_config.logo (المسار المخزّن بعد الرفع).
     */
    public static function resolveLogoUrl(?string $logo = null): string
    {
        $path = $logo;
        if ($path === null || $path === '') {
            try {
                self::ensureMediaColumns();
                $path = static::query()->value('logo');
            } catch (\Throwable $e) {
                $path = null;
            }
        }

        $served = self::mediaServeUrl($path);
        if ($served) {
            return $served;
        }

        if (is_file(public_path('img/logo.png'))) {
            return '/img/logo.png';
        }

        return '/img/logo.jpg';
    }

    /**
     * Custom login background URL from system_config.login_background.
     */
    public static function resolveLoginBackgroundUrl(?string $path = null): ?string
    {
        $value = $path;
        if ($value === null || $value === '') {
            try {
                self::ensureMediaColumns();
                $value = static::query()->value('login_background');
            } catch (\Throwable $e) {
                $value = null;
            }
        }

        return self::mediaServeUrl($value);
    }
}
