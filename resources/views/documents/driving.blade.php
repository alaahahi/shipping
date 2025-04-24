
@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>شركة نور البصرة أيوب</title>
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
<body dir="rtl">
  <div class="container-fluid">
  
  
    <!-- Header Section -->
    <div  class="row">
      <div class="col-12 px-0">
        <img src="/img/cbg.jpg" alt="Company Banner" width="100%" >

      </div>
    
    </div>
    <div class="row py-3"  style="margin: 0 40px">
      <div class="col-sm-6 px-4 fs-6  "> رقم : {{$doc['id']}} </div>

      <div class="col-sm-6 px-4 fs-6  text-start"> بتاريخ : {{$doc['created']}} </div>
    </div>
  
    <!-- Title -->
    <h3 class="title py-3">
        كتاب / تخويل قيادة
    </h3>

    <!-- Content Section -->
    <p class="fs-6 text-center"  style="margin: 0 40px">
      {{ str_replace('name', $doc['name'], $doc['note']) }}
    </p>
    <div  style="margin: 0 50px" class="py-3">
      <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>المعلومات</th>
                <th>التفاصيل</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>نوع السيارة</td>
                <td class="fw-bold">{{$doc['car_type'] ?? ''}}</td>
            </tr>
            <tr>
                <td>رقم الشاصي</td>
                <td class="fw-bold">{{$doc['vin'] ?? ''}}</td>
            </tr>
            <tr>
                <td>موديل</td>
                <td class="fw-bold">{{$doc['year'] ?? ''}}</td>
            </tr>
            <tr>
                <td>اللون</td>
                <td class="fw-bold">{{$doc['color'] ?? ''}}</td>
            </tr>
            <tr>
                <td>رقم السيارة</td>
                <td class="fw-bold">{{$doc['car_number'] ?? ''}}</td>
            </tr>
        </tbody>
    </table>
    </div>
    <!-- Table Section -->
 

    <!-- Footer Section -->
    <div class="row pt-4 pb-5 "  style="margin: 0 40px">
     
      <div class="col-sm-6 px-5">
        <div style="width: 110px">
          <div style="color: #fff;text-align: center;font-size: 5px">
              {!! QrCode::size(100)->generate(url('').'/makeDrivingDocumentPdf?doc_id='.$doc['id']); !!}
          </div>
        </div>
      </div>
      <div class="col-sm-6  text-start">
        <p class="px-5">سلام جلال  </p>
        <p style="padding: 0 58px">{{$doc['created']}} </p>
      </div>
        
    </div>

    <div class="row p-2  mt-5" style="font-size: 14px">
      <div class="col-sm-6 pe-5"> 
      العنوان:
      @if($doc['owner_id']??'')
      @if($doc['owner_id']==2)
      {{$config['address_kik']}}
      @else
      {{$config['address_erb']}}
      @endif
      @endif

      </div>

      <div class="col-sm-6 ps-5 text-start">
         Mobile:
         @if($doc['owner_id']??'')
         @if($doc['owner_id']==2)
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
