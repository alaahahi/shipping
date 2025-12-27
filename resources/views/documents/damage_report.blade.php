@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>شركة سلام جلال أيوب - تقرير ضرر</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    
        .title {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .footer {
            margin-top: 30px;
            font-weight: bold;
        }
        table {
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }
    
        @page {
            margin: 5px;
        }
    </style>
</head>
<body dir="ltr" style="direction: ltr; text-align: left;">
  <div class="container-fluid">
  
    <!-- Header Section -->
    <div class="row">
      <div class="col-12 px-0">
        <img src="/img/cbg.jpg" alt="Company Banner" width="100%">
      </div>
    </div>
    
    <div class="row py-3" style="margin: 0 40px; direction: ltr; text-align: center;">
      <div class="col-sm-6 px-4 fs-6">NO: {{$report['id']}}</div>
      <div class="col-sm-6 px-4 fs-6">DATE: {{ \Carbon\Carbon::parse($report['created'])->format('Y-m-d') }}</div>
    </div>
  
    <!-- Title -->
    <h3 class="title py-3" style="direction: ltr; text-align: center;">
        CAR DAMAGE REPORT
    </h3>

    <!-- Driver Info Section -->
    <div style="margin: 0 50px; direction: ltr; text-align: center;" class="py-3">
      <div class="row mb-3">
        <div class="col-md-12">
          <strong>B / {{strtoupper($report['driver_name'] ?? '')}}</strong>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-12">
          <strong>CMR NO: {{$report['cmr_number'] ?? ''}}  DRIVER NAME: {{strtoupper($report['driver_name'] ?? '')}}  CARS COUNT: {{$report['cars_count'] ?? 0}}  TOTAL: {{number_format($report['total_damage'] ?? 0, 0)}}$</strong>
        </div>
      </div>
    </div>

    <!-- Cars Info Table -->
    <div style="margin: 0 50px; direction: ltr; text-align: center;" class="py-3">
      <h5 class="mb-3" style="direction: ltr; text-align: center;"><strong>CARS INFO</strong></h5>
      <table class="table table-bordered" style="direction: ltr; text-align: center;">
        <thead>
            <tr>
                <th style="text-align: center;">CAR</th>
                <th style="text-align: center;">VIN</th>
                <th style="text-align: center;">MODEL</th>
                <th style="text-align: center;">COLOR</th>
                <th style="text-align: center;">DAMAGE</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($report['cars_info']) && is_array($report['cars_info']))
                @foreach($report['cars_info'] as $car)
                <tr>
                    <td class="fw-bold" style="text-align: center;">{{strtoupper($car['car'] ?? '')}}</td>
                    <td class="fw-bold" style="text-align: center;">{{strtoupper($car['vin'] ?? '')}}</td>
                    <td class="fw-bold" style="text-align: center;">{{$car['model'] ?? ''}}</td>
                    <td class="fw-bold" style="text-align: center;">{{strtoupper($car['color'] ?? '')}}</td>
                    <td class="fw-bold" style="text-align: center;">{{number_format(floatval(str_replace('$', '', $car['damage'] ?? 0)), 0)}}$</td>
                </tr>
                @endforeach
            @endif
        </tbody>
      </table>
    </div>

    <!-- Footer Section -->
    <div class="row pt-4 pb-5" style="margin: 0 40px; direction: ltr; text-align: center;">
      <div class="col-sm-6 px-5">
        <div style="width: 110px; margin: 0 auto;">
          <div style="color: #fff;text-align: center;font-size: 5px">
              {!! QrCode::size(100)->generate(route('damage_report.verify', $report['verification_token'] ?? $report['id'])); !!}
          </div>
        </div>
      </div>
      <div class="col-sm-6" style="direction: ltr; text-align: center;">
        <p class="px-5">SALAM JALAL AYOUB</p>
        <p style="padding: 0 58px">{{ \Carbon\Carbon::parse($report['created'])->format('Y-m-d') }}</p>
      </div>
    </div>

    <div class="row p-2 mt-5" style="font-size: 14px; direction: ltr; text-align: center;">
      <div class="col-sm-6 pe-5"> 
        ADDRESS:
        @if($report['owner_id']??'')
          @if($report['owner_id']==2)
            {{$config['address_kik']}}
          @else
            {{$config['address_erb']}}
          @endif
        @endif
      </div>

      <div class="col-sm-6 ps-5" style="direction: ltr; text-align: center;">
         MOBILE:
         @if($report['owner_id']??'')
           @if($report['owner_id']==2)
             {{$config['mobile_kik']}}
           @else
             {{$config['mobile_erb']}}
           @endif
         @endif
      </div>
    </div>

</div>

<script>
    $(document).ready(function() {
        function openPrintDialog() {
           window.print();
        }
        openPrintDialog();
    });
</script>

</body>
</html>

