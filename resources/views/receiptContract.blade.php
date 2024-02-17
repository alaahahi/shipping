
@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>شركة سلام جلال أيوب</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<style>

  /* Set page size to A4 (210mm x 297mm) */
  @page {
      size: A4;
      margin: 0;
    }

    /* Set content to fill the entire page
     margin: 20px;
      margin-top: 60px;
    }
    */
    html, body {
      width: 210mm;
      height: 297mm;
      margin: 0;
      padding: 0;
    }

    /* Your additional CSS styles go here */
    /* For example: */
  
  </style>
<body style="direction: rtl;">
  <img src="/img/bg.jpg" width="100%" />
  <div class="content">
    <div class="d-flex justify-content-around"  style="font-size: 11px ; font-weight: 700;">
      <div class="text-center">
        <span >
          الرقم : {{$data['id']}}
        </span>

      </div>
      <div class="text-center">
        <span>
        التاريخ و الوقت : {{$data['created_at']}}
      </span>
      </div>
    </div>
  </div>

<script>
    $(document).ready(function() {
        function openPrintDialog() {
             window.print();
        }
    
       // openPrintDialog();
    });
    </script>

</body>
</html>
