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
        @page { size: A4; margin: 10mm 10mm 22mm 10mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: Arial, 'Segoe UI', Tahoma, sans-serif;
            color: #111;
            direction: ltr;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .red { color: #d11212; }

        /* Watermark - repeats every printed page */
        .watermark {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 0;
            pointer-events: none;
        }
        .watermark img { width: 65%; max-width: 520px; opacity: 0.06; }

        /* Layout table: thead repeats company header on every page */
        table.layout { width: 100%; border-collapse: collapse; position: relative; z-index: 1; }
        table.layout > thead { display: table-header-group; }
        table.layout > thead td { padding: 0; border: 0; }

        .doc-header { text-align: center; padding-bottom: 6px; background: #fff; }
        .doc-header img { max-height: 78px; margin-bottom: 2px; }
        .company-name { font-size: 24px; font-weight: bold; margin: 2px 0; letter-spacing: .5px; }
        .company-sub { font-style: italic; font-size: 12px; margin: 1px 0; }

        /* Fixed footer - bottom of every page */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 2;
            background: #fff;
            text-align: center;
            font-size: 11px;
            padding-top: 6px;
            padding-bottom: 2mm;
        }
        .print-footer .line { border-top: 2px solid #d11212; margin-bottom: 5px; }
        .print-footer .red { color: #d11212; font-weight: bold; }

        /* Main content inside layout tbody */
        table.layout > tbody td { padding: 0; border: 0; }

        table.items thead { display: table-header-group; }
        table.items tr { page-break-inside: avoid; }

        .meta { width: 100%; margin-top: 14px; font-size: 15px; }
        .meta td { vertical-align: top; padding: 1px 0; }
        .meta .label { font-weight: bold; }

        .title { text-align: center; font-size: 25px; font-weight: bold; margin: 20px 0 12px; letter-spacing: 1px; }

        table.items { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.items th, table.items td { border: 1px solid #2b2b2b; padding: 8px 6px; text-align: center;     font-size: medium;
    padding: 5px;
    border: 1px solid;}
        table.items thead th { background: #1f3864; color: #fff; font-weight: bold; border-color: #1f3864;     font-size: medium;
    padding: 5px;
    border: 1px solid;}
        table.items td.carname ,table.items  td.no ,table.items  td.year ,table.items  td.color ,table.items  td.weight ,table.items  td.unit_price{ font-weight: bold;     font-size: medium;
    padding: 5px;
    border: 1px solid;  }
        table.items td.vin { font-weight: bold; letter-spacing: .3px; text-align: center;     font-size: medium;
    padding: 5px;
    border: 1px solid;}

        .summary-wrap { width: 100%; margin-top: 14px; margin-bottom: 48px; overflow: hidden; }
        .summary-top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .summary-qr-col {
            flex: 0 0 auto;
            text-align: center;
        }
        .summary-table-col {
            flex: 0 0 auto;
            margin-left: auto;
        }
        .summary-stamp-row {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 16px;
        }
        .summary-stamp-row .stamp-img {
            width: 250px;
            max-width: 250px;
            height: auto;
            display: block;
        }
        .summary-qr-col .qr-box {
            width: 100px;
            text-align: center;
        }
        .summary-qr-col .qr-box svg,
        .summary-qr-col .qr-box img {
            width: 100px !important;
            height: 100px !important;
            display: block;
            margin: 0 auto;
        }
        .summary-qr-col .qr-label {
            font-size: 12px;
            font-weight: bold;
            margin-top: 6px;
            letter-spacing: 0.3px;
        }
        .summary-qr-col .qr-caption {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        table.summary { border-collapse: collapse; font-size: 13px; min-width: 360px; text-align: center; }
        table.summary td.skey {       font-size: medium;  padding: 5px;background: #1f3864; color: #fff; font-weight: bold; width: 180px; }
        table.summary td.destination { border: 1px solid #2b2b2b; padding: 6px 10px;font-size: medium; }
        table.summary td.total_units { border: 1px solid #2b2b2b; padding: 6px 10px;font-size: medium; }
        table.summary td.total_weight { border: 1px solid #2b2b2b; padding: 6px 10px;font-size: medium; }
        table.summary td.total_price { border: 1px solid #2b2b2b; padding: 6px 10px;font-size: medium; }
    </style>
</head>
<body>
    <div class="watermark"><img src="/img/logo.jpg" alt=""></div>

    <div class="print-footer">
        <div class="line"></div>
        <div>100 Street, Salam Jalal Office, Erbil, Iraq</div>
        <div class="red">+964 7704459964 | +964 7504544320 | info@salam-jalal-co.intellij-app.com</div>
    </div>

    <table class="layout">
        <thead>
            <tr><td>
                <div class="doc-header">
                    <img src="/img/logo.jpg" alt="logo">
                    <div class="company-name">SALAM JALAL AYOUB Co.</div>
                    <div class="company-sub">For Individual Car Trading</div>
                    <div class="company-sub red">Erbil, Iraq</div>
                    <div class="company-sub red">Commercial Registration No. 298</div>
                </div>
            </td></tr>
        </thead>
        <tbody>
            <tr><td>
                <!-- Invoice meta -->
                <table class="meta">
                    <tr>
                        <td style="width: 55%"><span class="label">INVOICE NO:</span> {{ $invoice->invoice_no }}</td>
                        <td><span class="label">FROM:</span> IRAQ - ERBIL</td>
                    </tr>
                    <tr>
                        <td><span class="label">DATE:</span> &nbsp; {{ $invoiceDate }}</td>
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

                <div class="title">INVOICE AND PACKING LIST</div>

                <!-- Items -->
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width: 38px">NO</th>
                            <th>CAR NAME</th>
                            <th style="width: 60px">YEAR</th>
                            <th style="width: 115px">COLOR</th>
                            <th>VIN</th>
                            <th style="width: 70px">KG</th>
                            <th style="width: 90px">PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td class="no">{{ $index + 1 }}</td>
                                <td class="carname">{{ strtoupper(trim(($item->make ?? '') . ' ' . ($item->model ?? ''))) }}</td>
                                <td class="year">{{ $item->year }}</td>
                                <td class="color">{{ strtoupper($item->color ?? '') }}</td>
                                <td class="vin">{{ $item->chassis_no }}</td>
                                <td class="weight">{{ $item->weight }}</td>
                                <td class="unit_price">{{ $formatPrice($item->unit_price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Summary + Stamp + QR -->
                <div class="summary-wrap">
                    <div class="summary-top-row">
                        <div class="summary-qr-col">
                            <div class="qr-box">
                                {!! QrCode::size(100)->generate($verificationUrl ?? $invoice->invoice_no) !!}
                                <div class="qr-label">{{ $invoice->invoice_no }}</div>
                                <div class="qr-caption">Scan to verify</div>
                            </div>
                        </div>
                        <div class="summary-table-col">
                            <table class="summary">
                                <tr>
                                    <td class="skey">TOTAL UNITS IN CAR</td>
                                    <td class="total_units">{{ $totalUnits }}</td>
                                </tr>
                                <tr>
                                    <td class="skey">TOTAL WEIGHT</td>
                                    <td class="total_weight">{{ $totalWeight > 0 ? number_format($totalWeight) . 'KGS' : '' }}</td>
                                </tr>
                                <tr>
                                    <td class="skey">TOTAL PRICE</td>
                                    <td class="total_price">{{ $formatPrice($totalPrice) }}</td>
                                </tr>
                                <tr>
                                    <td class="skey">DESTINATION CIP</td>
                                    <td class="destination">{{ strtoupper($destination) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="summary-stamp-row">
                        <img src="/public/img/iran_stamp.png" alt="Official Stamp" class="stamp-img">
                    </div>
                </div>
            </td></tr>
        </tbody>
    </table>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
