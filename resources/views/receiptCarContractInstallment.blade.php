<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>وصل دفعة قسط</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 24px; color: #111; }
        .box { border: 2px solid #111; padding: 20px; max-width: 760px; margin: 0 auto; }
        h1 { text-align: center; margin: 0 0 16px; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td, th { border: 1px solid #ccc; padding: 8px; text-align: center; }
        .total { font-size: 18px; font-weight: bold; }
        .muted { color: #555; font-size: 13px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="box">
        <h1>وصل دفعة قسط سيارة</h1>
        <p class="muted">رقم العقد: {{ $contract->id }} | التاريخ: {{ $installment->created }}</p>

        <table>
            <tr><th>المشتري</th><td>{{ $contract->name_buyer }}</td></tr>
            <tr><th>السيارة</th><td>{{ $contract->car_name }}</td></tr>
            <tr><th>الشانصي</th><td style="direction:ltr">{{ $contract->vin }}</td></tr>
            <tr><th>هذه الدفعة</th><td class="total">{{ number_format((float) $installment->amount, 0) }}</td></tr>
            <tr><th>إجمالي المدفوع</th><td>{{ number_format($paidTotal, 0) }}</td></tr>
            <tr><th>المتبقي</th><td class="total">{{ number_format($remaining, 0) }}</td></tr>
            <tr><th>المستلم</th><td>{{ $installment->received_by }}</td></tr>
            @if($installment->note)
            <tr><th>ملاحظة</th><td>{{ $installment->note }}</td></tr>
            @endif
        </table>
    </div>
    <p class="no-print" style="text-align:center;margin-top:16px;">
        <button onclick="window.print()">طباعة</button>
    </p>
</body>
</html>
