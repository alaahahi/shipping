<?php

namespace App\Services;

use App\Models\Driving;
use App\Models\SystemConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DrivingAuthorizationService
{
    /**
     * النص الافتراضي لتخويل القيادة (يُستخدم عندما لا يحفظ المدير نصاً مخصصاً).
     */
    public const DEFAULT_TEXT = 'انا كارزان سرهنك محمد (وكيل عام سلام جلال ايوب ) (مدير مفوض شركة سلام جلال ايوب) قد خولت السيد({name}) بقيادة السيارة ذات المواصفات ادناه له حق  نقلها  من محافظة الى محافظة اخرى ودفع الرسوم والغرمات بيع وشراء القبض الثمن.';

    /**
     * @var array<string, string>
     */
    public const PLACEHOLDERS = [
        '{name}' => 'اسم المخوَّل له',
        '{car_type}' => 'نوع السيارة',
        '{vin}' => 'رقم الشاصي',
        '{year}' => 'الموديل',
        '{color}' => 'اللون',
        '{car_number}' => 'رقم السيارة',
        '{date}' => 'تاريخ التخويل',
    ];

    /**
     * النص المعتمد من الإعدادات مع الرجوع للنص الافتراضي.
     */
    public function template(): string
    {
        SystemConfig::ensureDrivingAuthorizationColumn();

        $stored = SystemConfig::query()->value('driving_authorization_text');

        return is_string($stored) && trim($stored) !== '' ? $stored : self::DEFAULT_TEXT;
    }

    public function saveTemplate(?string $text): string
    {
        SystemConfig::ensureDrivingAuthorizationColumn();

        $config = SystemConfig::first() ?? SystemConfig::create([]);
        $config->forceFill([
            'driving_authorization_text' => is_string($text) && trim($text) !== '' ? $text : null,
        ])->save();

        return $this->template();
    }

    /**
     * استبدال المتغيرات داخل النص. يدعم الصيغة القديمة (كلمة name المجردة).
     */
    public function render(?string $text, Driving $doc): string
    {
        $text = is_string($text) && trim($text) !== '' ? $text : $this->template();

        $values = [
            '{name}' => (string) ($doc->name ?? ''),
            '{car_type}' => (string) ($doc->car_type ?? ''),
            '{vin}' => (string) ($doc->vin ?? ''),
            '{year}' => (string) ($doc->year ?? ''),
            '{color}' => (string) ($doc->color ?? ''),
            '{car_number}' => (string) ($doc->car_number ?? ''),
            '{date}' => (string) ($doc->created ?? ''),
        ];

        // نصوص قديمة محفوظة قبل اعتماد المتغيرات كانت تستخدم الصيغة (name).
        $text = str_replace('(name)', '('.$values['{name}'].')', $text);

        return strtr($text, $values);
    }

    /**
     * قائمة تخويلات القيادة مع البحث والفلترة الزمنية.
     */
    public function paginate(int $ownerId, array $filters = []): LengthAwarePaginator
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? 25);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 25;

        return Driving::query()
            ->where('owner_id', $ownerId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('vin', 'like', '%'.$q.'%')
                        ->orWhere('car_type', 'like', '%'.$q.'%')
                        ->orWhere('car_number', 'like', '%'.$q.'%');
                    if (ctype_digit($q)) {
                        $inner->orWhere('id', (int) $q);
                    }
                });
            })
            ->when($from && $to, fn ($query) => $query->whereBetween('created', [$from, $to]))
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
