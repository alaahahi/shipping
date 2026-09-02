<?php

namespace App\Services;

use App\Models\Driving;
use App\Models\SystemConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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

    /**
     * سجل واحد مقيّد بالفرع (لعرضه في نافذة منبثقة).
     */
    public function find(int $id, int $ownerId): Driving
    {
        $doc = Driving::query()->find($id);

        if (! $doc) {
            throw new RuntimeException('Driving authorization not found.');
        }

        if ((int) $doc->owner_id !== $ownerId) {
            throw new RuntimeException('Not authorized to view this driving authorization.');
        }

        return $doc;
    }

    /**
     * الحقول القابلة للتعديل على مستوى السجل (النص العام يُدار من الإعدادات).
     *
     * @var array<int, string>
     */
    public const EDITABLE_FIELDS = ['name', 'car_type', 'car_number', 'vin', 'year', 'color', 'created'];

    /**
     * تعديل بيانات تخويل قيادة مع تسجيل الحقول المتغيرة (قبل/بعد).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, int $ownerId, array $data): Driving
    {
        $doc = Driving::query()->find($id);

        if (! $doc) {
            throw new RuntimeException('Driving authorization not found.');
        }

        if ((int) $doc->owner_id !== $ownerId) {
            throw new RuntimeException('Not authorized to update this driving authorization.');
        }

        $changes = [];

        foreach (self::EDITABLE_FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $new = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
            $old = $doc->{$field};

            if ((string) $old === (string) $new) {
                continue;
            }

            $changes[$field] = ['from' => $old, 'to' => $new];
            $doc->{$field} = $new;
        }

        if ($changes !== []) {
            $doc->save();
        }

        Log::info('Driving authorization updated', [
            'driving_id' => $doc->id,
            'client_id' => $doc->client_id,
            'changes' => $changes,
            'updated_by' => Auth::id(),
            'owner_id' => $ownerId,
        ]);

        return $doc;
    }

    /**
     * حذف ناعم لتخويل قيادة (قابل للاسترجاع) مع تسجيل العملية.
     */
    public function delete(int $id, int $ownerId): Driving
    {
        $doc = Driving::query()->find($id);

        if (! $doc) {
            throw new RuntimeException('Driving authorization not found.');
        }

        if ((int) $doc->owner_id !== $ownerId) {
            throw new RuntimeException('Not authorized to delete this driving authorization.');
        }

        $doc->delete();

        Log::info('Driving authorization deleted', [
            'driving_id' => $doc->id,
            'client_id' => $doc->client_id,
            'name' => $doc->name,
            'car_number' => $doc->car_number,
            'vin' => $doc->vin,
            'deleted_by' => Auth::id(),
            'owner_id' => $ownerId,
        ]);

        return $doc;
    }
}
