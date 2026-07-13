@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
$contact = MyHelp::receiptContact($config ?? null, $owner_id ?? null);
$companyName = config('app.company_name') ?: ($config->first_title_ar ?? 'شركة كامل');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>وصل دفعة قسط — {{ $companyName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A5 portrait; margin: 12mm; }
        body { font-family: Tahoma, Arial, sans-serif; background: #f3f4f6; }
        .receipt-wrap { max-width: 720px; margin: 16px auto; }
        .receipt-box { border: 2px solid #1e3a5f; border-radius: 8px; background: #fff; overflow: hidden; }
        .receipt-header { background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color: #fff; }
        .receipt-logo img { max-height: 72px; max-width: 120px; object-fit: contain; background: #fff; border-radius: 8px; padding: 6px; }
        .receipt-title { font-size: 1.35rem; font-weight: 700; }
        .receipt-subtitle { font-size: 0.85rem; opacity: 0.9; }
        .meta-bar { background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; }
        .info-table td, .info-table th { padding: 10px 12px; vertical-align: middle; }
        .info-table th { width: 38%; background: #f1f5f9; color: #334155; font-weight: 600; }
        .amount-highlight { font-size: 1.4rem; font-weight: 800; color: #047857; }
        .remaining-highlight { font-size: 1.15rem; font-weight: 700; color: #b91c1c; }
        .footer-contact { background: #f8fafc; border-top: 1px dashed #cbd5e1; font-size: 0.9rem; }
        .stamp-area { min-height: 70px; border: 1px dashed #cbd5e1; border-radius: 6px; }
        .print-actions { text-align: center; margin: 16px 0 24px; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .receipt-wrap { margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="receipt-wrap">
    <div class="receipt-box">
        <div class="receipt-header p-3">
            <div class="row align-items-center g-3">
                <div class="col-4 text-center text-md-end">
                    <div class="receipt-logo d-inline-block">
                        @include('Components.logo')
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="receipt-title">وصل دفعة قسط</div>
                    <div class="receipt-subtitle">Car Installment Receipt</div>
                </div>
                <div class="col-4 text-center text-md-start">
                    <div class="fw-bold">{{ $config->first_title_ar ?? $companyName }}</div>
                    @if(!empty($config->second_title_ar))
                        <div class="receipt-subtitle">{{ $config->second_title_ar }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="meta-bar row g-0 text-center py-2 px-2">
            <div class="col-4"><strong>رقم الوصل:</strong> {{ $installment->id }}</div>
            <div class="col-4"><strong>رقم العقد:</strong> {{ $contract->id }}</div>
            <div class="col-4"><strong>التاريخ:</strong> {{ $installment->created }}</div>
        </div>

        <div class="p-3">
            <table class="table table-bordered info-table mb-3">
                <tbody>
                    <tr>
                        <th>المشتري</th>
                        <td>{{ $contract->name_buyer }}</td>
                    </tr>
                    <tr>
                        <th>السيارة</th>
                        <td>{{ $contract->car_name }}</td>
                    </tr>
                    <tr>
                        <th>رقم الشانصي</th>
                        <td style="direction:ltr; text-align:right;">{{ $contract->vin }}</td>
                    </tr>
                    <tr>
                        <th>سعر السيارة</th>
                        <td>{{ number_format((float) $contract->car_price, 0) }} د.ع</td>
                    </tr>
                    <tr>
                        <th>هذه الدفعة</th>
                        <td class="amount-highlight">{{ number_format((float) $installment->amount, 0) }} د.ع</td>
                    </tr>
                    <tr>
                        <th>إجمالي المدفوع</th>
                        <td>{{ number_format($paidTotal, 0) }} د.ع</td>
                    </tr>
                    <tr>
                        <th>المتبقي</th>
                        <td class="remaining-highlight">{{ number_format($remaining, 0) }} د.ع</td>
                    </tr>
                    <tr>
                        <th>المستلم</th>
                        <td>{{ $installment->received_by ?: '—' }}</td>
                    </tr>
                    @if($installment->note)
                    <tr>
                        <th>ملاحظة</th>
                        <td>{{ $installment->note }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="row g-3 align-items-end">
                <div class="col-6">
                    <div class="small text-muted mb-1">توقيع المستلم</div>
                    <div class="stamp-area"></div>
                </div>
                <div class="col-6">
                    <div class="small text-muted mb-1">توقيع المحاسب</div>
                    <div class="stamp-area"></div>
                </div>
            </div>
        </div>

        <div class="footer-contact p-3">
            <div class="row">
                <div class="col-md-6 mb-2 mb-md-0">
                    <strong>العنوان:</strong> {{ $contact['address'] ?: '—' }}
                </div>
                <div class="col-md-6 text-md-start">
                    <strong>هاتف الشركة:</strong>
                    <span dir="ltr">{{ $contact['mobile'] ?: '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="print-actions no-print">
        <button type="button" class="btn btn-primary px-4" onclick="window.print()">طباعة الوصل</button>
        <button type="button" class="btn btn-outline-secondary px-4 ms-2" onclick="window.close()">إغلاق</button>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
</script>
</body>
</html>
