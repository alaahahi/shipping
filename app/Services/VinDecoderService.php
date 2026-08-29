<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Server-side proxy for the external VIN decoding service.
 *
 * The decoder is called from the backend (never from the browser) because the
 * external host does not send CORS headers.
 */
class VinDecoderService
{
    public function decode(string $vin): array
    {
        $vin = strtoupper(trim($vin));
        $ttl = (int) config('services.vin_decoder.cache_ttl', 86400);

        if ($ttl > 0) {
            return Cache::remember("vin_decode:{$vin}", $ttl, fn () => $this->fetch($vin));
        }

        return $this->fetch($vin);
    }

    private function fetch(string $vin): array
    {
        $baseUrl = rtrim((string) config('services.vin_decoder.base_url'), '/');
        $timeout = (int) config('services.vin_decoder.timeout', 10);
        $apiKey = config('services.vin_decoder.api_key');

        $query = ['format' => 'json'];
        if (! empty($apiKey)) {
            $query['api_key'] = $apiKey;
        }

        try {
            $response = Http::timeout($timeout > 0 ? $timeout : 10)
                ->connectTimeout(5)
                ->retry(2, 300, throw: false)
                ->acceptJson()
                ->get("{$baseUrl}/decodevinvalues/{$vin}", $query);
        } catch (\Throwable $e) {
            Log::warning('VIN decode request failed', ['vin' => $vin, 'error' => $e->getMessage()]);
            throw new RuntimeException('تعذر الاتصال بخدمة فحص معلومات السيارة، يرجى المحاولة لاحقاً.');
        }

        if ($response->failed()) {
            Log::warning('VIN decode returned an error status', [
                'vin' => $vin,
                'status' => $response->status(),
            ]);
            throw new RuntimeException('خدمة فحص معلومات السيارة غير متوفرة حالياً.');
        }

        $result = $response->json('Results.0');

        if (! is_array($result)) {
            throw new RuntimeException('لم يتم العثور على معلومات لرقم الشاصي المدخل.');
        }

        return $this->normalize($vin, $result);
    }

    private function normalize(string $vin, array $result): array
    {
        $make = ($result['Make'] ?? '') ?: ($result['Manufacturer'] ?? '');
        $model = $result['Model'] ?? '';

        return [
            'vin' => ($result['VIN'] ?? '') ?: $vin,
            'car_type' => trim($make.' '.$model),
            'make' => $make,
            'model' => $model,
            'year' => $result['ModelYear'] ?? '',
            'doors' => $result['Doors'] ?? '',
        ];
    }
}
