<?php

namespace App\Helpers;

use Alkoumi\LaravelArabicTafqeet\Tafqeet;

class Help
{
    public static function numberToWords($number, $currency = 'usd')
    {
        if ($currency == '$') {
            $currency = 'usd';
        }
        if ($currency == 'IQD') {
            $currency = 'iqd';
        }

        return Tafqeet::inArabic($number, $currency);
    }

    /**
     * عنوان وموبايل الإيصال حسب owner_id (أربيل / كركوك) مع fallback من إعدادات النظام.
     *
     * @param  object|array|null  $config
     * @return array{address: string, mobile: string}
     */
    public static function receiptContact($config, $ownerId = null): array
    {
        $cfg = $config ? (is_array($config) ? $config : $config->toArray()) : [];
        $thirdTitleAr = $config
            ? (is_array($config) ? ($config['third_title_ar'] ?? '') : ($config->third_title_ar ?? ''))
            : '';
        $isKik = (int) $ownerId === 2;
        $address = $isKik
            ? ($cfg['address_kik'] ?? $thirdTitleAr)
            : ($cfg['address_erb'] ?? $thirdTitleAr);
        $mobile = $isKik
            ? ($cfg['mobile_kik'] ?? '')
            : ($cfg['mobile_erb'] ?? '');
        if ($mobile === '') {
            $phones = config('car_contract.phones', []);
            $mobile = $phones[0] ?? '';
        }

        return ['address' => (string) $address, 'mobile' => (string) $mobile];
    }
}
