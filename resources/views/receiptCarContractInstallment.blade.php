@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
$amount = (float) ($installment->amount ?? 0);
$currency = '$';
$created = $installment->created ?? '';
$receiptId = $installment->id ?? '';
$buyerName = $contract->name_buyer ?? '';
$receivedBy = $installment->received_by ?? '';
$note = $installment->note ?? '';
$firstTitle = $config->first_title_ar ?? ($config['first_title_ar'] ?? config('app.company_name'));
$secondTitle = $config->second_title_ar ?? ($config['second_title_ar'] ?? '');
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
        @page {
            size: auto;
            margin: 15px;
            margin-top: 60px;
        }
        .receipt-logo {
            max-width: 125px;
            width: 100%;
            height: auto;
            object-fit: contain;
        }
    </style>
</head>
<body style="direction: rtl;">

@php
    $copies = ['نسخة الزبون', 'نسخة المكتب'];
@endphp

@foreach ($copies as $copyLabel)
<div class="container-fluid mt-2" style="border: 2px solid">
    <div class="row">
        <div class="col-4 text-center py-3">
            <h5 class="pt-3">{{ $firstTitle }}</h5>
            @if($secondTitle)
                <h5>{{ $secondTitle }}</h5>
            @endif
        </div>
        <div class="col-4 text-center py-3">
            <h5 class="pt-3">وصل دفع</h5>
            <h5 class="pt-1">Cash Receipt Voucher</h5>
        </div>
        <div class="col-4 text-center py-3">
            @include('Components.logo', ['logoMaxWidth' => 125])
        </div>
    </div>

    <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
        <div class="col-3">الرقم: {{ $receiptId }}</div>
        <div class="col-3"></div>
        <div class="col-3"></div>
        <div class="col-3">تاريخ: {{ $created }}</div>
    </div>

    <div class="row p-2" style="font-size: 14px">
        <div class="col-12 p-2 pe-5">
            من السيد: {{ $buyerName }}
        </div>
        <div class="col-12 p-2 pe-5">
            المستلم: {{ $receivedBy ?: '—' }}
        </div>
        <div class="col-12 p-2 pe-5">
            مبلغ قدره:
            {{ $Help->numberToWords($amount, $currency) }}
        </div>
        <div class="col-12 p-2 pe-5">
            الملاحظات:
            {{ $note ?: '—' }}
        </div>
    </div>

    <div class="row text-center" style="font-size: 14px">
        <div class="col-1"></div>
        <div class="col-1 alert-primary border p-2">المبلغ:</div>
        <div class="col-1 alert-primary border p-2">{{ $amount }}</div>
        <div class="col-1 alert-primary border p-2">{{ $currency }}</div>
        <div class="col-8 text-start ps-5">اسم وتوقيع المستلم</div>
    </div>

    @include('Components.receiptFooterContact')
</div>
@if (!$loop->last)
<hr>
@endif
@endforeach

<script>
    $(document).ready(function () {
        window.print();
    });
</script>
</body>
</html>
