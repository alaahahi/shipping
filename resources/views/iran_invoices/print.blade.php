@php
    $invoiceDate = $invoice->invoice_date
        ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y')
        : '';

    $toNumber = function ($value) {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return $clean === '' ? null : (float) $clean;
    };

    $totalUnits = $invoice->items->count();

    $totalWeight = 0;
    foreach ($invoice->items as $line) {
        $w = $toNumber($line->weight);
        if ($w !== null) {
            $totalWeight += $w;
        }
    }

    $formatPrice = function ($value) use ($toNumber) {
        $num = $toNumber($value);
        return $num === null ? '' : number_format($num, 2);
    };

    $totalPrice = $invoice->total_price;
    $destination = $invoice->destination ?? '';
@endphp
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, 'Segoe UI', Tahoma, sans-serif;
            color: #111;
            margin: 0;
            direction: ltr;
        }
        .wrap { width: 100%; }
        .center { text-align: center; }
        .red { color: #d11212; }
        .navy { background: #1f3864; color: #fff; }

        .header { text-align: center; }
        .header img { max-height: 90px; margin-bottom: 4px; }
        .company-name { font-size: 26px; font-weight: bold; margin: 2px 0; letter-spacing: .5px; }
        .company-sub { font-style: italic; font-size: 13px; margin: 1px 0; }

        .meta { width: 100%; margin-top: 18px; font-size: 15px; }
        .meta td { vertical-align: top; padding: 1px 0; }
        .meta .label { font-weight: bold; }

        .title { text-align: center; font-size: 26px; font-weight: bold; margin: 22px 0 14px; letter-spacing: 1px; }

        table.items { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.items th, table.items td { border: 1px solid #2b2b2b; padding: 8px 6px; text-align: center; }
        table.items thead th { background: #1f3864; color: #fff; font-weight: bold; border-color: #1f3864; }
        table.items td.carname { font-weight: bold; }
        table.items td.vin { font-weight: bold; letter-spacing: .3px; }

        .summary-wrap { width: 100%; margin-top: 14px; }
        table.summary { border-collapse: collapse; font-size: 13px; float: right; min-width: 360px; }
        table.summary td { border: 1px solid #2b2b2b; padding: 6px 10px; }
        table.summary td.skey { background: #1f3864; color: #fff; font-weight: bold; width: 180px; }

        .footer { clear: both; text-align: center; font-size: 12px; margin-top: 60px; }
        .footer .line { border-top: 2px solid #d11212; margin-bottom: 6px; }
        .footer .red { color: #d11212; font-weight: bold; }
    </style>
</head>
<body>
<div class="wrap">
    <!-- Company header -->
    <div class="header">
        <img src="/img/logo.jpg" alt="logo">
        <div class="company-name">SALAM JALAL AYOUB Co.</div>
        <div class="company-sub">For Individual Car Trading</div>
        <div class="company-sub red">Erbil, Iraq</div>
        <div class="company-sub red">Commercial Registration No. 298</div>
    </div>

    <!-- Invoice meta -->
    <table class="meta">
        <tr>
            <td style="width: 55%">
                <span class="label">INVOICE NO:</span> {{ $invoice->invoice_no }}
            </td>
            <td>
                <span class="label">FROM:</span> IRAQ - ERBIL
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">DATE:</span> &nbsp; {{ $invoiceDate }}
            </td>
            <td>SALAM JALAL AYOUB CO.</td>
        </tr>
        <tr>
            <td colspan="2" class="label" style="padding-top:6px;">
                {{ $invoice->carrier_name ?? optional($invoice->carrier)->name }}
            </td>
        </tr>
        @if($destination)
        <tr>
            <td colspan="2" class="label">TRANSIT TO {{ strtoupper($destination) }}</td>
        </tr>
        @endif
    </table>

    <!-- Title -->
    <div class="title">INVOICE AND PACKING LIST</div>

    <!-- Items table -->
    <table class="items">
        <thead>
            <tr>
                <th style="width: 38px">NO</th>
                <th>CAR NAME</th>
                <th style="width: 60px">YEAR</th>
                <th style="width: 70px">COLOR</th>
                <th>VIN</th>
                <th style="width: 70px">KG</th>
                <th style="width: 90px">PRICE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="carname">{{ strtoupper(trim(($item->make ?? '') . ' ' . ($item->model ?? ''))) }}</td>
                    <td>{{ $item->year }}</td>
                    <td>{{ strtoupper($item->color ?? '') }}</td>
                    <td class="vin">{{ $item->chassis_no }}</td>
                    <td>{{ $item->weight }}</td>
                    <td>{{ $formatPrice($item->unit_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary-wrap">
        <table class="summary">
            <tr>
                <td class="skey">TOTAL UNITS IN CAR</td>
                <td>{{ $totalUnits }}</td>
            </tr>
            <tr>
                <td class="skey">TOTAL WEIGHT</td>
                <td>{{ $totalWeight > 0 ? number_format($totalWeight) . 'KGS' : '' }}</td>
            </tr>
            <tr>
                <td class="skey">TOTAL PRICE</td>
                <td>{{ $formatPrice($totalPrice) }}</td>
            </tr>
            <tr>
                <td class="skey">DESTINATION CIP</td>
                <td>{{ strtoupper($destination) }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="line"></div>
        <div>100 Street, Salam Jalal Office, Erbil, Iraq</div>
        <div class="red">+964 7704459964 | +964 7504544320 | info@salam-jalal-co.intellij-app.com</div>
    </div>
</div>
<script>
    window.onload = function () { window.print(); };
</script>
</body>
</html>
