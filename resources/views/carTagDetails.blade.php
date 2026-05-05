<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $tag->name }} - تفاصيل التاغ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @page { margin: 15px; margin-top: 60px; }
    @media print {
      body { margin: 0; padding: 0; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body style="direction: rtl;">

<div class="container-fluid">
  <div class="row">
    <div class="col-4 text-center py-3">
      <h5>{{ $config['first_title_ar'] ?? '' }}</h5>
      <h5>{{ $config['second_title_ar'] ?? '' }}</h5>
    </div>
    <div class="col-4 text-center py-3">
      <h5 class="pt-3">تفاصيل التاغ</h5>
    </div>
    <div class="col-4 text-center py-3">
      @include('Components.logo')
    </div>
  </div>

  <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
    <div class="col">
      التاغ: <strong>{{ $tag->name ?? '' }}</strong>
    </div>
    <div class="col">
      عدد السيارات: <strong>{{ $tag->cars_count ?? ($cars->count() ?? 0) }}</strong>
    </div>
    <div class="col">
      تاريخ الطباعة: {{ date('Y-m-d') }}
    </div>
  </div>

  <div class="row text-center py-2">
    <table class="table table-sm table-striped table-bordered" style="font-size: 12px">
      <thead>
        <tr>
          <th>#</th>
          <th>نوع السيارة</th>
          <th>رقم الشاصي (VIN)</th>
          <th>رقم السيارة</th>
          <th>الزبون</th>
          <th>السنة</th>
          <th>التاريخ</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cars as $i => $car)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $car->car_type ?? '' }}</td>
            <td>{{ $car->vin ?? '' }}</td>
            <td>{{ $car->car_number ?? '' }}</td>
            <td>{{ $car->client->name ?? '' }}</td>
            <td>{{ $car->year ?? '' }}</td>
            <td>
              {{ $car->date ?? $car->created_at ?? '' }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<script>
  window.print();
</script>

</body>
</html>

