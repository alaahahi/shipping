<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>{{ config('app.company_name') }} — سيارة خارجية</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    @page { size: auto; margin: 15px; margin-top: 40px; }
    :root {
      --ink: #0F172A;
      --ink-soft: #334155;
      --muted: #64748B;
      --border: #E4E7EB;
      --navy: #1E3A5F;
      --paid: #047857;
      --paid-bg: #ECFDF5;
      --surface: #F8FAFC;
    }
    body { color: var(--ink); -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .num { font-variant-numeric: tabular-nums; unicode-bidi: plaintext; }
    .doc-title { color: var(--navy); font-weight: 700; }
    .meta-row {
      background: var(--surface);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      color: var(--ink-soft);
    }
    .meta-row .label { color: var(--muted); font-weight: 600; }
    .meta-row .value { color: var(--ink); font-weight: 700; }
    .grand-total {
      border: 2px solid var(--paid);
      background: var(--paid-bg);
      border-radius: 12px;
    }
    .grand-total .gt-label { color: var(--ink-soft); font-size: 13px; font-weight: 600; }
    .grand-total .gt-value { color: var(--paid); font-size: 28px; font-weight: 800; }
    .exp-table { font-size: 13px; border-color: var(--border); }
    .exp-table thead th {
      background: var(--navy);
      color: #fff;
      font-weight: 700;
      border-color: var(--navy);
    }
    .exp-table tbody td { border-color: var(--border); }
    .exp-table tbody tr:nth-child(even) td { background: var(--surface); }
    .exp-table .amount-dollar { color: var(--paid); font-weight: 700; }
    .exp-table .amount-dinar { color: var(--navy); font-weight: 700; }
    .exp-table tfoot td { background: #F1F5F9; font-weight: 800; }
    </style>
</head>
<body>
<div class="container-fluid">
  <div class="row align-items-center">
    <div class="col-4 text-center py-3">
      <h5 class="mb-1">{{ $config['first_title_ar'] ?? '' }}</h5>
      <h6 class="text-muted mb-0">{{ $config['second_title_ar'] ?? '' }}</h6>
    </div>
    <div class="col-4 text-center py-3">
      <h4 class="doc-title pt-2 mb-0">تفاصيل سيارة خارجية</h4>
    </div>
    <div class="col-4 text-center py-3">
      @include('Components.logo')
    </div>
  </div>

  <div class="row p-2 text-center meta-row" style="font-size: 14px">
    <div class="col-3"><span class="label">تاجر:</span> <span class="value">{{ $car->dealer_name }}</span></div>
    <div class="col-3"><span class="label">نوع السيارة:</span> <span class="value">{{ $car->car_type }}</span></div>
    <div class="col-3"><span class="label">رقم السيارة:</span> <span class="value">{{ $car->car_number }}</span></div>
    <div class="col-3"><span class="label">شانصي:</span> <span class="value num">{{ $car->vin ?: '—' }}</span></div>
  </div>
  <div class="row p-2 text-center meta-row" style="font-size: 14px">
    <div class="col-3"><span class="label">السنة:</span> <span class="value">{{ $car->year ?: '—' }}</span></div>
    <div class="col-3"><span class="label">اللون:</span> <span class="value">{{ $car->car_color ?: '—' }}</span></div>
    <div class="col-3"><span class="label">التاريخ:</span> <span class="value num">{{ optional($car->date)->format('Y-m-d') ?? $car->date }}</span></div>
    <div class="col-3"><span class="label">ملاحظة:</span> <span class="value">{{ $car->note ?: '—' }}</span></div>
  </div>

  <div class="row py-3 justify-content-center">
    <div class="col-8">
      <div class="grand-total text-center py-3 px-4">
        <div class="row">
          <div class="col-6">
            <div class="gt-label">المجموع بالدولار</div>
            <div class="gt-value num">{{ number_format($totalAmountDollar) }} $</div>
          </div>
          <div class="col-6">
            <div class="gt-label">المجموع بالدينار</div>
            <div class="gt-value num">{{ number_format($totalAmountDinar) }} د</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row text-center py-2">
    <table class="table table-sm table-bordered exp-table">
      <thead>
        <tr>
          <th style="width: 18%">التاريخ</th>
          <th>الملاحظة</th>
          <th style="width: 18%">المبلغ بالدولار</th>
          <th style="width: 18%">المبلغ بالدينار</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($car->payments as $payment)
        <tr>
          <td class="num">{{ optional($payment->created)->format('Y-m-d') ?? $payment->created }}</td>
          <td>{{ $payment->note ?: '—' }}</td>
          <td class="num amount-dollar">{{ number_format($payment->amount_dollar) }}</td>
          <td class="num amount-dinar">{{ number_format($payment->amount_dinar) }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="4">لا توجد دفعات</td>
        </tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2" class="text-center">المجموع</td>
          <td class="num">{{ number_format($totalAmountDollar) }} $</td>
          <td class="num">{{ number_format($totalAmountDinar) }} د</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script>
  $(document).ready(function() {
    window.print();
  });
</script>
</body>
</html>
