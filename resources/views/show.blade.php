<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.company_name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    @page {
      size: auto; /* auto is the initial value */

      /* this affects the margin in the printer settings */
      margin: 15px;
      margin-top: 60px;
    }
    .expense-print-list {
      list-style: none;
      padding: 0;
      margin: 4px 0 0;
      font-size: 10px;
      line-height: 1.4;
      text-align: right;
    }
    .expense-print-list li {
      border-bottom: 1px dotted #ccc;
      padding: 1px 0;
    }
    </style>
</head>
<body style="direction: rtl;">
<div class="container-fluid">       
<div class="row">
    <div class="col-4 text-center py-3">
        <h5>
       {{$config['first_title_ar']}}
        </h5>
        <h5>
        {{$config['second_title_ar']}}
        </h5>
    </div>
    <div class="col-4 text-center py-3">

    
       
    <h5 class="pt-3">  كشف حساب</h5>
    </div>
    <div class="col-4 text-center py-3"> 
        @include('Components.logo')

    </div>
    </div>
    <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
    <div class="col-3"> 
    التاجر:
    {{$clientData['client']->name}}
    </div>
    <div class="col-3">
    موبايل:
    {{$clientData['client']->phone}}
    </div>
    @if($_GET['from'] ?? '')
    <div class="col-3">
    من تاريخ:
    <?= $_GET['from'] ??'' ?>
    </div>
    @endif
    @if($_GET['to'] ?? '')
    <div class="col-3">
        حتى تاريخ:
    <?= $_GET['to'] ??'' ?>
    </div>
    @endif
  </div>
  <div class="row p-2 text-center border-bottom alert-primary "  style="font-size: 14px">
    <div class="col-3"> 
    مجموع النهائي:
    {{$clientData['cars_sum']}}
    </div>
    @if(($clientData['print'] ?? 0)==6 )
    <div class="col-3">
      مبلغ مدفوع:
      {{$clientData['cars_paid']}}
      </div>
      <div class="col-3">
       مبلغ الباقي:
       {{$clientData['cars_sum'] - $clientData['cars_paid']}}
      </div>
    @else
    <div class="col-3">
    مبلغ مدفوع:
    {{$clientData['cars_sum']-$clientData['client']->wallet->balance}}
    </div>
    <div class="col-3">
     مبلغ الباقي:
     {{$clientData['client']->wallet->balance}}
    </div>
    @endif
    <div class="col-3">
      عدد السيارات:
    {{$clientData['car_total']}}
    </div>
  </div>
  <div class="row text-center py-2">
    <table class="table table-sm table-striped table-bordered" style="font-size: 12px">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">السيارة</th>
            <th scope="col">تاريخ</th>
            <th scope="col">رقم شاسى</th>
            <th scope="col">التاغات</th>
            <th scope="col">لون</th>
            <th scope="col">موديل</th>
            <th scope="col">كمرك</th>
            <th scope="col">تخليص</th>
            <th scope="col">شهادة</th>
            @if(config('app.show_container_open'))
            <th scope="col">فتح كونتينر</th>
            @endif
            <th scope="col">نقل</th>
            <th scope="col">مصاريف</th>
            <th scope="col">نقل بري</th>

            <th scope="col">مجموع</th>
            <th scope="col">ملاحطة</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($clientData['data'] as $key=>$data)
            @php
                $isSingleCarPrint = ($clientData['print'] ?? 0) == 6;
                $expensePrintLines = $isSingleCarPrint
                    ? \App\Support\CarNoteFormatter::expensePrintLines($data, 'sales')
                    : [];
                $hasExpenseBreakdown = \App\Services\CarExpenseBreakdownService::hasBreakdown($data->expenses_breakdown ?? null);
            @endphp
            <tr>
                <th scope="row">{{$key+1}}</th>
                <td>{{$data->car_type}}</td>
                <td>{{$data->date}}</td>
                <td>{{$data->vin}}</td>
                <td>{{ $data->tags->pluck('name')->implode('، ') }}</td>
                <td>{{$data->car_color}}</td>
                <td>{{$data->year}}</td>
                <td>    <?php
                    $dinar_s = $data->dinar_s;
                    $dolar_price_s = $data->dolar_price_s ?? 1;
                
                    if ($dolar_price_s != 0) {
                        echo round(($dinar_s / $dolar_price_s)*100);
                    } else {
                        echo 0; // or any other appropriate message
                    }
                    ?></td>
                <td>{{$data->checkout_s}}</td>
                <td>{{$data->coc_dolar_s}}</td>
                @if(config('app.show_container_open'))
                <td>{{$data->container_open_s}}</td>
                @endif
                <td>{{$data->shipping_dolar_s}}</td>
                <td>
                    <strong>{{$data->expenses_s}}</strong>
                    @if($isSingleCarPrint && count($expensePrintLines))
                        <ul class="expense-print-list">
                            @foreach($expensePrintLines as $expenseLine)
                                <li>{{ $expenseLine }}</li>
                            @endforeach
                        </ul>
                    @endif
                </td>
                <td>{{$data->land_shipping_s}}</td>
                <td>{{$data->total_s}}</td>
                <td>
                    @if($isSingleCarPrint)
                        @if($hasExpenseBreakdown)
                            {{ $data->note }}
                        @endif
                    @else
                        {{ $data->note }}
                    @endif
                </td>
              </tr>
            @endforeach
        </tbody>
      </table>  
  </div>
</div>


<script>
    $(document).ready(function() {
        // Function to open the print dialog
        function openPrintDialog() {
            window.print();
        }
    
        // Call the function to open the print dialog
        openPrintDialog();
    });
    </script>

</body>
</html>
