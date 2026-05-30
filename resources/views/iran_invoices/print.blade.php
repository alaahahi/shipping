@php
    $currencyLabel = $invoice->currency ?: 'USD';
    $invoiceDate = $invoice->invoice_date
        ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d')
        : '';
    $hasAnyPrice = $invoice->items->contains(function ($item) {
        return $item->unit_price !== null && $item->unit_price !== '';
    }) || ($invoice->total_price !== null && $invoice->total_price !== '');
    $formatPrice = function ($value) {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float) $value, 2);
    };
@endphp
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>{{ $config->first_title_en ?? config('app.company_name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @page { size: auto; margin: 15px; margin-top: 40px; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .invoice-box { border: 2px solid #222; padding: 0; }
        .muted { color: #666; }
        table.items th, table.items td { font-size: 13px; vertical-align: middle; }
    </style>
</head>
<body style="direction: ltr;">
<div class="container-fluid mt-2 invoice-box">
    <div class="row">
        <div class="col-4 text-center py-3">
            <h5 class="pt-3">{{ $config->first_title_en ?? ($config->first_title_ar ?? '') }}</h5>
            <h6 class="muted">{{ $config->second_title_en ?? ($config->second_title_ar ?? '') }}</h6>
        </div>
        <div class="col-4 text-center py-3">
            <h4 class="pt-3">INVOICE</h4>
            <h6 class="muted">Iran Branch</h6>
        </div>
        <div class="col-4 text-center py-3">
            @include('Components.logo')
        </div>
    </div>

    <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
        <div class="col-6 text-start ps-4">Invoice No: <strong>{{ $invoice->invoice_no }}</strong></div>
        <div class="col-6 text-end pe-4">Date: <strong>{{ $invoiceDate }}</strong></div>
    </div>

    <div class="row p-2" style="font-size: 14px">
        <div class="col-6 p-2 ps-4">
            <strong>Carrier:</strong> {{ $invoice->carrier_name ?? optional($invoice->carrier)->name ?? '—' }}
            @if(optional($invoice->carrier)->phone)
                <div class="muted">Phone: {{ $invoice->carrier->phone }}</div>
            @endif
            @if(optional($invoice->carrier)->address)
                <div class="muted">Address: {{ $invoice->carrier->address }}</div>
            @endif
        </div>
        <div class="col-6 p-2 ps-4">
            <strong>Consignee / User:</strong> {{ $invoice->consignee_name ?? optional($invoice->consignee)->name ?? '—' }}
            @if(optional($invoice->consignee)->phone)
                <div class="muted">Phone: {{ $invoice->consignee->phone }}</div>
            @endif
            @if(optional($invoice->consignee)->address)
                <div class="muted">Address: {{ $invoice->consignee->address }}</div>
            @endif
        </div>
    </div>

    <div class="row px-2 pb-2">
        <div class="col-12">
            <table class="table table-bordered text-center items">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px">#</th>
                        <th>Chassis No</th>
                        <th>Make / Model</th>
                        <th>Year</th>
                        <th>Color</th>
                        <th>Weight</th>
                        @if($hasAnyPrice)
                            <th>Unit Price</th>
                        @endif
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->chassis_no ?: '—' }}</td>
                            <td>{{ trim(($item->make ?? '') . ' ' . ($item->model ?? '')) ?: '—' }}</td>
                            <td>{{ $item->year ?: '—' }}</td>
                            <td>{{ $item->color ?: '—' }}</td>
                            <td>{{ $item->weight ?: '—' }}</td>
                            @if($hasAnyPrice)
                                <td>{{ $formatPrice($item->unit_price) }}</td>
                            @endif
                            <td>{{ $item->notes ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if($hasAnyPrice)
                    <tfoot>
                        <tr>
                            <td colspan="{{ 6 }}" class="text-end"><strong>Total ({{ $currencyLabel }})</strong></td>
                            <td><strong>{{ $formatPrice($invoice->total_price) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($invoice->notes)
        <div class="row px-2 pb-2" style="font-size: 14px">
            <div class="col-12 ps-4"><strong>Notes:</strong> {{ $invoice->notes }}</div>
        </div>
    @endif

    <div class="row p-2 border-top mt-3" style="font-size: 13px">
        <div class="col-6 ps-4 muted">Authorized Signature</div>
        <div class="col-6 pe-4 text-end muted">Received By</div>
    </div>
</div>
<script>
    $(document).ready(function() { window.print(); });
</script>
</body>
</html>
