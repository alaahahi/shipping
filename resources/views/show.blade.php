<!DOCTYPE html>
<html>
<head>
    <title>شركة سلام جلال أيوب</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body style="direction: rtl;">
<div class="container">       
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
    <div class="row p-2 text-center border-top border-bottom" >
    <div class="col-4"> 
    الاسم:
    <span></span>
    </div>
    <div class="col-4">
    التاريخ:
    <span style="direction: ltr;"></span>
    </div>
    <div class="col-4">
    رقم الهاتف:
        <span></span>
    </div>
  </div>
  <div class="row text-center">

  </div>
  <div class="row text-center py-2">
    <div class="col-6">
        
    </div>   
  </div>
</div>
</body>
</html>
