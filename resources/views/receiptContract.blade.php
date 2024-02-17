
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
    <div class="d-flex justify-content-around py-2"  style="font-size: 12px ; font-weight: 700;background-color: #f0f8ff">
      <div class="text-center"  style="width:300px">
        <span >
          الرقم : {{$data['id'] ??''}}
        </span>

      </div>
      <div class="text-center"  style="width:300px">
        <span>
        التاريخ و الوقت : {{$data['created_at'] ??''}}
      </span>
      </div>
    </div>

    <div class="d-flex justify-content-around  mt-4"  style="font-size: 12px ; font-weight: 700;">
      <div>
        <div class="text-center  p-1" style="width:300px;border: 1px darkslateblue solid;background-color: #d2cfe7">
          <span >
            لیەنی یەکەم فرۆشیار
            -
            الطرف الأول  البائع
          </span>

        </div>
        <div class="  p-1 " style="width:300px;border: 1px darkslateblue solid;">
          <div class="py-1">
            فرۆشیار / البائع : {{$data['name_seller'] ??''}}
          </div>
          <div class="py-1">
            دانیشتوی / الساکن : {{$data['address_seller'] ??''}}
          </div>
          <div class="py-1">
            رقم موبایل : {{$data['phone_seller'] ??''}}
          </div>
        </div>
      </div>
      <div>
      <div class="text-center p-1"   style="width:300px;border: 1px darkslateblue solid;background-color: #d2cfe7">
        <span>
          لیەنی دووەم کریار
          -
          الطرف الثانی المشتری

      </span>
      </div>
      <div class="  p-1 " style="width:300px;border: 1px darkslateblue solid">
        <div class="py-1">
          کریار / المشتری : {{$data['name_buyer'] ??''}}
        </div>
        <div class="py-1">
          دانیشتوی / الساکن : {{$data['address_buyer']??''}}
        </div>
        <div class="py-1">
          رقم موبایل : {{$data['phone_buyer'] ??''}}
        </div>
      </div>
      </div>
    </div>
    <div class="py-3 text-danger text-center" style="font-size: 12px">
      <div>
        رێکەوتن کرا لە نێوان هەردوو لیەن لە سەر ئەم خالنەی خوارەوه
      </div>
      <div>
        وتم الاتفاق على النقاط التالية بين الطرفين
      </div>
    </div>
    <div style="font-size: 13px;padding: 0 50px">
      <div>
        <div>
        1.
        فرۆشتنی لیەنی یەکەم بە لیەنی دووەم ئوتومبێلی ژمارە  
        (بيع سيارة الطرف الأول إلى سيارة الطرف الثاني رقم)
        :
        <b class="px-3">
          {{$data['no'] ?? ''}}
        </b>
        </div>
        <div class="pt-2">
          لە جۆری 
        (من النوع )
          :
          <b class="px-3">
            {{$data['car_name'] ?? ''}}
          </b>
          مودیل
          :
          <b class="px-3">
            {{$data['modal'] ?? ''}}
          </b>
          قبارە 
          (الحجم) 
          :
          <b class="px-3">
            {{$data['size'] ?? ''}}
          </b>
          رەنگ 
          (اللون) : 
          <b class="px-3">
            {{$data['color'] ?? ''}}
          </b>
        </div>
        <div class="pt-2">
          ژمارە لشە 
          (الشاصی) 
            :
          <b class="px-3">
            {{$data['vin'] ?? ''}}
          </b>
        </div>
        <div class="pt-2">
          لە جیاتی / بڕی پارە 
          (بمبلغ قدره)
          : 
          
          <b class="px-3 fs-6">
            {{$data['car_price'] ?? 0}} $
          </b>
          {{ $Help->numberToWords($data['car_price']??0)}}
        </div>
        <div class="pt-2">
          فرۆشیار وەری گرت بڕی پارە 
          (وقد قبض)
            :
          <b class="px-3 fs-6">
            {{$data['car_paid'] ?? 0}} $
          </b>
          {{ $Help->numberToWords($data['car_paid']??0)}}
        </div>
        <div class="pt-2">
          ئەو برەی ماوەتەوە 
          (الباقی)
            :
          <b class="px-3 fs-6">
            {{($data['car_price']??0)-($data['car_paid'] ?? 0)}} $
          </b>
          {{ $Help->numberToWords(($data['car_price']??0)-($data['car_paid'] ?? 0) ??0)}}
        </div>
      </div>
      <div>
        <div class="pt-3">
        2.
        گۆرینەوەی لیەنی یەکەم ئوتومبێلی ژمارە 
        (السيارة البديلة)
        :
        <b class="px-3">
          {{$data['no_s'] ?? ''}}
        </b>
        </div>
        <div class="pt-2">
          لە جۆری 
        (من النوع )
          :
          <b class="px-3">
            {{$data['car_name_s'] ?? ''}}
          </b>
          مودیل
          :
          <b class="px-3">
            {{$data['modal_s'] ?? ''}}
          </b>
          قبارە 
          (الحجم) 
          :
          <b class="px-3">
            {{$data['size_s'] ?? ''}}
          </b>
          رەنگ 
          (اللون) : 
          <b class="px-3">
            {{$data['color_s'] ?? ''}}
          </b>
        </div>
        <div class="pt-2">
          ژمارە لشە 
          (الشاصی) 
            :
          <b class="px-3">
            {{$data['vin_s'] ?? ''}}
          </b>
        </div>
        <div class="pt-2">
          تێبینی
          (ملاحظة)
            :
          <b class="px-3">
            {{$data['note'] ?? ''}}
          </b>
        </div>
        
      </div>
      <div class="pt-3 " style="color: brown">
        3
        .
         علی البائع و المشتری تسجیل السیارە حسب قوانین مدیریة المرور العامةمع اجراء معاملة نقل الملکیة

      </div>
      <div class="pt-3 " style="color: brown">
        4
        .
        علی المشتری فحص السیارة قبل الشراء و نحن غیر مسؤولین بعد توقیع عقد المعرض

      </div>
      <div class="pt-3 " style="color: brown">
        5
        .
        الطرف الاول مسوول عن کافة أنواع الغرامات قبل موعد الشراء

      </div>
      <div class="pt-3 " style="color: brown">
        6
        .
        صاحب المعرض غیر المسوول عن السیارة بعد البیع

      </div>
      <div class="pt-3 " style="color: brown">
        7
        .
        علی المشتري تسجیل السیارة خلال شهر واحد

      </div>
      <div class="pt-3 " style="color: brown">
        8
        .
         کتب هذا العقد بثلثة نسخ بتاریخ
         <b class="px-3">
          {{$data['created'] ?? ''}}
        </b>

      </div>
      <div class="pt-3 " style="color: brown">
        9
        .
        کل عقد غیر مختوم من المعرض یعتبر باطل
      </div>

      <div class="d-flex justify-content-around  mt-5 pt-2">
        <div>
          بەلێن و رەزامەندی لیەنی یەکەم 
          فرۆشیار
             (البائع)
        </div>
        <div>
          نووسەری پێشانگا
        </div>
        <div>
          بەلێن و رەزامەندی لیەنی دووەم 
          کریار
             (المشتری)
        </div>
      </div>
      <div class="d-flex justify-content-around  mt-4">
        <div>
          <b>
            {{$data['name_seller'] ??''}}
          </b>
        </div>
        <div>
          <b>
            كاتب المعرض
          </b>
        </div>
        <div>
          <b>
            {{$data['name_buyer'] ??''}}
          </b>
        </div>
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
