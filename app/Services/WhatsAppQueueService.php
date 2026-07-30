<?php

namespace App\Services;

use App\Models\Car;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Queue WhatsApp messages via wa.intellij-app.com tenant API.
 */
class WhatsAppQueueService
{
    public const EVENT_CLIENT_DEBT = 'client_debt_reminder';

    public const EVENT_PAYMENT_RECEIPT = 'payment_receipt';

    public const EVENT_CAR_ADDED = 'car_added';

    public const SOURCE_CRM = 'crm';

    public const SOURCE_INVOICES = 'invoices';

    public const SOURCE_SALES = 'sales';

    public function config(): ?SystemConfig
    {
        SystemConfig::ensureWhatsAppColumns();

        return SystemConfig::query()->first();
    }

    public function isEnabled(?SystemConfig $config = null): bool
    {
        $config ??= $this->config();

        return $config
            && (bool) ($config->wa_enabled ?? false)
            && is_string($config->wa_tenant)
            && trim($config->wa_tenant) !== '';
    }

    public function isEventEnabled(string $event, ?SystemConfig $config = null): bool
    {
        $config ??= $this->config();
        if (! $this->isEnabled($config)) {
            return false;
        }

        return match ($event) {
            self::EVENT_CLIENT_DEBT => (bool) ($config->wa_notify_client_debt ?? true),
            self::EVENT_PAYMENT_RECEIPT => (bool) ($config->wa_notify_payment_receipt ?? true),
            self::EVENT_CAR_ADDED => (bool) ($config->wa_notify_car_added ?? true),
            default => true,
        };
    }

    /**
     * Normalize local Iraqi numbers to E.164 (+964…).
     */
    public function normalizePhone(?string $phone): ?string
    {
        if (! is_string($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '964')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '7')) {
            return '+964'.$digits;
        }

        if (strlen($digits) >= 10) {
            return '+'.$digits;
        }

        return null;
    }

    /**
     * @param  array{
     *   phone: string,
     *   message: string,
     *   source?: string,
     *   event?: string,
     *   recipient_name?: string|null,
     *   priority?: int,
     *   unique_key?: string|null,
     *   max_retry?: int,
     *   created_by?: string|null
     * }  $payload
     * @return array{ok: bool, queue_id?: mixed, error?: string, status?: int}
     */
    public function queue(array $payload): array
    {
        $config = $this->config();
        if (! $this->isEnabled($config)) {
            return ['ok' => false, 'error' => 'whatsapp_disabled'];
        }

        $phone = $this->normalizePhone($payload['phone'] ?? null);
        $message = trim((string) ($payload['message'] ?? ''));
        if (! $phone || $message === '') {
            return ['ok' => false, 'error' => 'invalid_phone_or_message'];
        }

        $tenant = trim((string) $config->wa_tenant);
        $baseUrl = rtrim(
            (string) ($config->wa_base_url ?: config('services.whatsapp_queue.base_url')),
            '/'
        );
        $url = "{$baseUrl}/{$tenant}/api/v1/queue";

        $body = [
            'phone' => $phone,
            'message' => mb_substr($message, 0, 4096),
            'source' => $payload['source'] ?? self::SOURCE_CRM,
            'event' => $payload['event'] ?? null,
            'recipient_name' => $payload['recipient_name'] ?? null,
            'priority' => (int) ($payload['priority'] ?? 5),
            'unique_key' => $payload['unique_key'] ?? null,
            'max_retry' => (int) ($payload['max_retry'] ?? 3),
            'created_by' => $payload['created_by']
                ?? ($config->wa_created_by ?: config('services.whatsapp_queue.created_by')),
        ];

        $body = array_filter($body, static fn ($v) => $v !== null && $v !== '');

        try {
            $response = Http::acceptJson()
                ->timeout(20)
                ->post($url, $body);

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'queue_id' => $response->json('data.id'),
                    'status' => $response->status(),
                ];
            }

            Log::warning('WhatsAppQueueService: queue rejected', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
                'event' => $body['event'] ?? null,
            ]);

            return [
                'ok' => false,
                'error' => 'queue_rejected',
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppQueueService: request failed', [
                'message' => $e->getMessage(),
                'event' => $body['event'] ?? null,
            ]);

            return ['ok' => false, 'error' => 'request_failed'];
        }
    }

    /**
     * @param  array<int, string|null>  $phones
     * @return array{sent: int, failed: int, results: array<int, array>}
     */
    public function notifyClientDebtReminders(array $phones, ?string $recipientName = null): array
    {
        $config = $this->config();
        if (! $this->isEventEnabled(self::EVENT_CLIENT_DEBT, $config)) {
            return ['sent' => 0, 'failed' => 0, 'results' => [], 'error' => 'event_disabled'];
        }

        $message = $this->resolveClientDebtMessage($config);
        $sent = 0;
        $failed = 0;
        $results = [];

        foreach (array_unique(array_filter($phones)) as $phone) {
            $normalized = $this->normalizePhone($phone);
            if (! $normalized) {
                $failed++;
                $results[] = ['phone' => $phone, 'ok' => false, 'error' => 'invalid_phone'];

                continue;
            }

            $result = $this->queue([
                'phone' => $normalized,
                'message' => $message,
                'source' => self::SOURCE_CRM,
                'event' => self::EVENT_CLIENT_DEBT,
                'recipient_name' => $recipientName,
                'priority' => 7,
                'unique_key' => 'debt-'.$normalized.'-'.now()->format('Ymd'),
            ]);

            $results[] = array_merge(['phone' => $normalized], $result);
            if ($result['ok']) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'results' => $results];
    }

    public function notifyPaymentReceipt(
        User $user,
        float|int|string $amountDollar = 0,
        float|int|string $amountDinar = 0,
        ?int $transactionId = null
    ): array {
        $config = $this->config();
        if (! $this->isEventEnabled(self::EVENT_PAYMENT_RECEIPT, $config)) {
            return ['ok' => false, 'error' => 'event_disabled'];
        }

        $phone = $this->normalizePhone($user->phone ?? null);
        if (! $phone) {
            return ['ok' => false, 'error' => 'invalid_phone'];
        }

        $dollar = (float) $amountDollar;
        $dinar = (float) $amountDinar;
        $amountParts = [];
        if ($dollar > 0) {
            $amountParts[] = number_format($dollar, 0).' $';
        }
        if ($dinar > 0) {
            $amountParts[] = number_format($dinar, 0).' دينار';
        }
        $amountLabel = $amountParts ? implode(' و ', $amountParts) : '—';

        $message = $this->applyPlaceholders(
            $this->resolvePaymentReceiptMessage($config),
            [
                'name' => (string) ($user->name ?? ''),
                'amount' => $amountLabel,
                'amount_dollar' => number_format($dollar, 0),
                'amount_dinar' => number_format($dinar, 0),
                'company' => $this->companyName($config),
            ]
        );

        return $this->queue([
            'phone' => $phone,
            'message' => $message,
            'source' => self::SOURCE_INVOICES,
            'event' => self::EVENT_PAYMENT_RECEIPT,
            'recipient_name' => $user->name,
            'priority' => 8,
            'unique_key' => $transactionId
                ? 'payment-'.$transactionId
                : 'payment-'.$user->id.'-'.now()->format('YmdHis'),
        ]);
    }

    /**
     * @param  Collection<int, Car>|array<int, Car>  $cars
     */
    public function notifyCarAdded(User $client, Collection|array $cars): array
    {
        $config = $this->config();
        if (! $this->isEventEnabled(self::EVENT_CAR_ADDED, $config)) {
            return ['ok' => false, 'error' => 'event_disabled'];
        }

        $phone = $this->normalizePhone($client->phone ?? null);
        if (! $phone) {
            return ['ok' => false, 'error' => 'invalid_phone'];
        }

        $cars = $cars instanceof Collection ? $cars : collect($cars);
        if ($cars->isEmpty()) {
            return ['ok' => false, 'error' => 'no_cars'];
        }

        $vins = $cars->pluck('vin')->filter()->values()->all();
        $vinLabel = implode(', ', $vins);
        $first = $cars->first();

        $message = $this->applyPlaceholders(
            $this->resolveCarAddedMessage($config),
            [
                'name' => (string) ($client->name ?? ''),
                'vin' => $vinLabel,
                'car_number' => (string) ($first->car_number ?? ''),
                'count' => (string) $cars->count(),
                'company' => $this->companyName($config),
            ]
        );

        $ids = $cars->pluck('id')->filter()->implode('-');

        return $this->queue([
            'phone' => $phone,
            'message' => $message,
            'source' => self::SOURCE_SALES,
            'event' => self::EVENT_CAR_ADDED,
            'recipient_name' => $client->name,
            'priority' => 6,
            'unique_key' => 'car-added-'.$ids,
        ]);
    }

    public function resolveClientDebtMessage(?SystemConfig $config = null): string
    {
        $config ??= $this->config();
        $custom = trim((string) ($config->wa_msg_client_debt ?? ''));
        if ($custom !== '') {
            return $this->applyPlaceholders($custom, [
                'company' => $this->companyName($config),
            ]);
        }

        $company = $this->companyName($config);

        return "السلام عليكم: {$company} - أربيل، يرجى الأخذ بالعلم تسديد المبلغ المستحق عليكم في أقرب وقت ممكن. في حال التأخير بالسداد لأكثر من أسبوع من تاريخ وصول السيارة، لا يتم حساب الجمرك على سعر 130000. شكرا لتعاونكم .......... سڵاو 
تکایە بەزووترین کات حسابەکەتان واصل بكه ن نقل و گمرک،
لە حاڵەتی نەدان، هیچ جیاوازی نرخی دۆلار بەرامبەر دینار ناگەڕێندرێتەوە بە هیچ شێوەیەک.
هەروەها ئاگاداری دەکرێنەوە کە ناتوانرێت خروجی بۆ هیچ ئۆتۆمبێلێک بکرێت ئەگەر  واصل نه كرابێت.";
    }

    public function resolvePaymentReceiptMessage(?SystemConfig $config = null): string
    {
        $config ??= $this->config();
        $custom = trim((string) ($config->wa_msg_payment_receipt ?? ''));
        if ($custom !== '') {
            return $custom;
        }

        return 'السلام عليكم {name}، تم استلام دفعة بمبلغ {amount}. شكراً لتعاونكم. — {company}';
    }

    public function resolveCarAddedMessage(?SystemConfig $config = null): string
    {
        $config ??= $this->config();
        $custom = trim((string) ($config->wa_msg_car_added ?? ''));
        if ($custom !== '') {
            return $custom;
        }

        return 'السلام عليكم {name}، تم إضافة {count} سيارة/سيارات برقم الشاصي: {vin}. — {company}';
    }

    /**
     * @param  array<string, string>  $vars
     */
    public function applyPlaceholders(string $template, array $vars): string
    {
        $replacements = [];
        foreach ($vars as $key => $value) {
            $replacements['{'.$key.'}'] = $value;
        }

        return strtr($template, $replacements);
    }

    public function companyName(?SystemConfig $config = null): string
    {
        $config ??= $this->config();
        $name = trim((string) ($config->first_title_ar ?? ''));

        return $name !== '' ? $name : 'شركة سلام جلال أيوب';
    }
}
