<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.company_name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    @page { size: auto; margin: 12px; margin-top: 40px; }
    body { font-size: 12px; }
    </style>
</head>
<body style="direction: rtl;">
<div class="container-fluid">
    <div class="row">
        <div class="col-4 text-center py-2">
            <h6>{{ $config['first_title_ar'] ?? '' }}</h6>
            <h6>{{ $config['second_title_ar'] ?? '' }}</h6>
        </div>
        <div class="col-4 text-center py-2">
            <h5 class="pt-2">ملخص قيد العمل</h5>
            @if($filterLabel ?? '')
            <small>{{ $filterLabel }}</small>
            @endif
        </div>
        <div class="col-4 text-center py-2">
            @include('Components.logo')
        </div>
    </div>

    <div class="row p-2 text-center border-top border-bottom alert-primary fw-bold">
        <div class="col-6">مجموع الدولار: {{ number_format($totalDollar ?? 0) }}</div>
        <div class="col-6">مجموع الدينار: {{ number_format($totalDinar ?? 0) }}</div>
    </div>

    <table class="table table-sm table-bordered text-center mt-2">
        <thead class="table-success">
            <tr>
                <th>#</th>
                <th>المالك</th>
                <th>النوع</th>
                <th>شانص</th>
                <th>رقم</th>
                <th>دولار</th>
                <th>دينار</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cars as $car)
            <tr>
                <td>{{ $car['no'] }}</td>
                <td>{{ $car['client']['name'] ?? '' }}</td>
                <td>{{ $car['car_type'] }}</td>
                <td>{{ $car['vin'] }}</td>
                <td>{{ $car['car_number'] }}</td>
                <td>{{ number_format($car['sum_dollar'] ?? 0) }}</td>
                <td>{{ number_format($car['sum_dinar'] ?? 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function () { window.print(); });
</script>
</body>
</html>
