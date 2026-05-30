@php
    $invoiceDate = $invoice->invoice_date
        ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y')
        : '—';
@endphp
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Verification — {{ $invoice->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        body { background: #f8fafc; font-family: Arial, 'Segoe UI', Tahoma, sans-serif; }
        .card-wrap {
            max-width: 820px;
            margin: 36px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1f3864, #2b5797);
            color: #fff;
            padding: 28px 32px;
        }
        .badge-soft {
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 0.85rem;
        }
        .section { padding: 24px 32px; }
        .label { color: #64748b; font-size: 0.9rem; }
        .value { color: #0f172a; font-weight: 600; }
        .verified {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #dcfce7;
            color: #166534;
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .qr-wrap { text-align: center; padding: 8px 0 20px; }
        .footer-note {
            background: #f1f5f9;
            padding: 16px 32px;
            color: #334155;
            font-size: 0.92rem;
        }
        table.items { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
        table.items th, table.items td { border: 1px solid #cbd5e1; padding: 8px; text-align: center; }
        table.items th { background: #1f3864; color: #fff; }
    </style>
</head>
<body>
    <div class="card-wrap">
        <div class="header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 class="h4 mb-2">Invoice Verification</h1>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-soft">Invoice No: {{ $invoice->invoice_no }}</span>
                        <span class="badge badge-soft">Date: {{ $invoiceDate }}</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">SALAM JALAL AYOUB Co.</div>
                    <div class="small opacity-75">For Individual Car Trading — Erbil, Iraq</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="verified mb-4">✓ Authentic invoice issued by the company</div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="label mb-1">Carrier</div>
                    <div class="value">{{ $invoice->carrier_name ?? optional($invoice->carrier)->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="label mb-1">Consignee</div>
                    <div class="value">{{ $invoice->consignee_name ?? optional($invoice->consignee)->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="label mb-1">Destination CIP</div>
                    <div class="value">{{ strtoupper($invoice->destination ?? '—') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="label mb-1">Total Price</div>
                    <div class="value">
                        @if($invoice->total_price !== null && $invoice->total_price !== '')
                            {{ number_format((float) $invoice->total_price, 2) }} {{ $invoice->currency }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="label mb-2">Cars in Invoice ({{ $invoice->items->count() }})</div>
            <table class="items">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Car</th>
                        <th>Year</th>
                        <th>Color</th>
                        <th>VIN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ strtoupper(trim(($item->make ?? '') . ' ' . ($item->model ?? ''))) }}</td>
                            <td>{{ $item->year ?? '—' }}</td>
                            <td>{{ strtoupper($item->color ?? '') }}</td>
                            <td>{{ $item->chassis_no ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="qr-wrap mt-4">
                <img id="verification-qr" alt="QR" style="display:none;width:150px;height:150px;margin:0 auto;background:#fff;padding:8px;border-radius:10px;border:1px solid #e2e8f0;" />
                <div class="label mt-2">Verification code: {{ $invoice->verification_token }}</div>
                <a href="{{ $verificationUrl }}" class="d-inline-block mt-1 small">{{ $verificationUrl }}</a>
            </div>
        </div>

        <div class="footer-note">
            This page confirms that the scanned invoice was issued by SALAM JALAL AYOUB Co. The information shown is for verification only.
            <br><span dir="rtl">هذه الصفحة تؤكد صحة الفاتورة وصدورها من الشركة عند مسح رمز QR.</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const verificationUrl = @json($verificationUrl ?? '');
            const qrImg = document.getElementById('verification-qr');
            const fallback = () => {
                if (verificationUrl && qrImg) {
                    qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(verificationUrl);
                    qrImg.style.display = 'block';
                }
            };
            if (typeof QRCode !== 'undefined' && verificationUrl && qrImg && typeof QRCode.toDataURL === 'function') {
                QRCode.toDataURL(verificationUrl, { width: 150, margin: 1 }, (err, url) => {
                    if (err) fallback();
                    else { qrImg.src = url; qrImg.style.display = 'block'; }
                });
            } else fallback();
        });
    </script>
</body>
</html>
