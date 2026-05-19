@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
$currency = '$';
$amount = (float) ($payment->amount ?? 0);
$created = $payment->payment_date
    ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d')
    : ($payment->created_at ? $payment->created_at->format('Y-m-d') : '');
$receiptNoFull = (string) ($payment->payment_id ?? $payment->id);
$receiptNo = strlen($receiptNoFull) > 8 ? substr($receiptNoFull, -8) : $receiptNoFull;
$amountFormatted = rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
$buyerName = $buyer->name ?? '';
$merchantName = $merchant->name ?? '';
$vin = $car->vin ?? $car->car_number ?? '-';
$carLabel = trim(($car->car_type ?? '') . ' ' . ($car->year ?? ''));
$note = $payment->note ?? '';
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.company_name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    @page { size: auto; margin: 15px; margin-top: 60px; }
    </style>
</head>
<body style="direction: rtl;">
@foreach ([1, 2] as $copy)
<div class="container-fluid mt-2" style="border: 2px solid">
    <div class="row">
        <div class="col-4 text-center py-3">
            <h5 class="pt-3">{{ $config->first_title_ar ?? '' }}</h5>
            <h5>{{ $config->second_title_ar ?? '' }}</h5>
        </div>
        <div class="col-4 text-center py-3">
            <h5 class="pt-3">وصل دفع - مبيعات داخلية</h5>
            <h5 class="pt-1">Internal Sales Payment Receipt</h5>
        </div>
        <div class="col-4 text-center py-3">
            @include('Components.logo')
        </div>
    </div>
    <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
        <div class="col-4">الرقم: {{ $receiptNo }}</div>
        <div class="col-4"></div>
        <div class="col-4">تاريخ: {{ $created }}</div>
    </div>
    <div class="row p-2" style="font-size: 14px">
        <div class="col-12 p-2 pe-5">الزبون / Buyer: {{ $buyerName }}</div>
        @if($merchantName)
        <div class="col-12 p-2 pe-5">التاجر / Merchant: {{ $merchantName }}</div>
        @endif
        <div class="col-12 p-2 pe-5">السيارة: {{ $carLabel }}</div>
        <div class="col-12 p-2 pe-5"><strong>رقم الشاصي / VIN:</strong> {{ $vin }}</div>
        <div class="col-12 p-2 pe-5">استلمت مبلغ قدره: {{ $Help->numberToWords($amount, $currency) }}</div>
        @if($note)
        <div class="col-12 p-2 pe-5">الملاحظات: {{ $note }}</div>
        @endif
    </div>
    <div class="row text-center" style="font-size: 14px">
        <div class="col-1"></div>
        <div class="col-1 alert-primary border p-2">المبلغ</div>
        <div class="col-1 alert-primary border p-2">{{ $amountFormatted }}</div>
        <div class="col-1 alert-primary border p-2">{{ $currency }}</div>
        <div class="col-8 text-start ps-5">اسم وتوقيع المستلم</div>
    </div>
    <div class="row p-2 border-top border-bottom mt-3" style="font-size: 14px">
        <div class="col-6 pe-5">العنوان: اربيل - مدينة المعارض</div>
        <div class="col-6 ps-5 text-start">Mobile: 0770 445 9964</div>
    </div>
</div>
@if($copy === 1)
<hr>
@endif
@endforeach
<script>
    $(document).ready(function() { window.print(); });
</script>
</body>
</html>
