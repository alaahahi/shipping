
@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.company_name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>

<style>
@font-face {
  font-family: 'Peshang';
  src: url('/Peshang.ttf') format('truetype');
}

body {
  font-family: 'Peshang', sans-serif; /* Use the custom font */
  background-color: #e5e9f2;
}
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
  b{
    color:cornflowerblue;
  }
  .content {
    margin: 16mm 12mm 20mm;
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
    overflow: hidden;
    padding: 28px 36px 34px;
  }
  .qr-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #d6e0f0;
    font-size: 2px;
    font-weight: 600;
    color: #0f172a;
  }
  .qr-header .info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }
  .qr-header .info-item .label {
    color: #475569;
    font-weight: 500;
  }
  .qr-header .info-item .value {
    color: #1e293b;
    font-weight: 700;
  }
  .qr-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
  }
  .qr-wrapper img {
    display: none;
    width: 80px;
    height: 80px;
    background: #ffffff;
    padding: 4px;
    border-radius: 12px;
    border: 1px solid #d6e0f0;
  }
  .qr-wrapper .qr-caption {
    font-size: 9px;
    color: #475569;
  }
  .party-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-top: 20px;
  }
  .party-card {
    border: 1px solid #d6e0f0;
    border-radius: 14px;
    overflow: hidden;
    background: #f8fafc;
  }
  .party-card__header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    text-align: center;
    padding: 10px 12px;
    font-weight: 700;
    font-size: 13px;
  }
  .party-card__body {
    padding: 14px 16px;
    background: #ffffff;
  }
  .info-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: 12px;
    padding: 6px 0;
    border-bottom: 1px dashed #e2e8f0;
  }
  .info-row:last-child {
    border-bottom: none;
  }
  .info-row .label {
    color: #475569;
    font-weight: 500;
    white-space: nowrap;
  }
  .info-row .value {
    color: #1e293b;
    font-weight: 600;
    flex: 1;
  }
  .section-title {
    margin-top: 28px;
    margin-bottom: 12px;
    font-weight: 700;
    font-size: 14px;
    color: #1d4ed8;
    border-right: 4px solid #2563eb;
    padding-right: 10px;
  }
  .detail-card {
    border: 1px solid #d6e0f0;
    border-radius: 14px;
    padding: 18px 20px;
    background: #ffffff;
  }
  .info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px 20px;
  }
  .info-block {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 12px;
    color: #1e293b;
  }
  .info-block .label {
    color: #475569;
    font-weight: 600;
  }
  .info-block .value {
    font-weight: 700;
  }
  .terms-card {
    border: 1px solid #d6e0f0;
    border-radius: 14px;
    padding: 18px 22px;
    background: #ffffff;
  }
  .terms-list {
    counter-reset: term;
    margin: 0;
    padding: 0;
    list-style: none;
  }
  .terms-list li {
    counter-increment: term;
    font-size: 12px;
    color: #334155;
    margin-bottom: 10px;
    line-height: 1.6;
    position: relative;
    padding-right: 28px;
  }
  .terms-list li::before {
    content: counter(term) ".";
    position: absolute;
    right: 0;
    top: 0;
    font-weight: 700;
    color: currentColor;
  }
  .highlight {
    color: #1d4ed8;
    font-weight: 700;
    padding: 0 4px;
  }
  .info-note {
    margin-top: 12px;
    font-size: 11px;
    color: #475569;
    background: #f8fafc;
    border-radius: 10px;
    padding: 10px 14px;
  }
  .signature-row {
    display: flex;
    justify-content: space-between;
    gap: 24px;
    margin-top: 40px;
  }
  .signature-col {
    flex: 1;
    text-align: center;
    font-size: 12px;
    color: #1e293b;
  }
  .signature-label {
    color: #475569;
    font-weight: 600;
    display: block;
    margin-bottom: 12px;
  }
  .signature-line {
    border-bottom: 1px dashed #94a3b8;
    margin: 0 auto 12px;
    width: 80%;
    height: 18px;
  }
  @media print {
    body {
      background: #ffffff;
    }
    .content {
      margin: 0;
      border-radius: 0;
      box-shadow: none;
      padding: 0 12px 12px;
    }
  }
  </style>
<body style="direction: rtl;"> 
  @if($config['second_title_ar']=='عين دبي')
  <img src="/img/bg1.jpg" width="100%" class="p-2 pb-0" />
  @else
  <img src="/img/bg.jpg" width="100%" class="p-3" />
  @endif
  <div class="content">
    <div class="qr-header">
      <div class="info-item">
        <span class="label">الرقم:</span>
        <span class="value">{{$data['id'] ?? ''}}</span>
      </div>
      @if(!empty($verificationUrl))
      <div class="qr-wrapper">
        <img id="contract-qr" alt="QR" />
        <span class="qr-caption">intellij-app.com بواسطة  </span>
      </div>
      @endif
      <div class="info-item">
        <span class="label">التاريخ:</span>
        <span class="value">{{$data['created'] ?? ''}}</span>
      </div>
    </div>

    <div class="party-grid">
      <div class="party-card">
        <div class="party-card__header">
          لایەنی یەکەم فرۆشیار – الطرف الأول (البائع)
        </div>
        <div class="party-card__body">
          <div class="info-row">
            <span class="label">فرۆشیار / البائع</span>
            <span class="value">{{$data['name_seller'] ?? ''}}</span>
          </div>
          <div class="info-row">
            <span class="label">دانیشتوی / السكن</span>
            <span class="value">{{$data['address_seller'] ?? ''}}</span>
          </div>
          <div class="info-row">
            <span class="label">رقم الموبايل</span>
            <span class="value">{{$data['phone_seller'] ?? ''}}</span>
          </div>
          <div class="info-row">
            <span class="label">رقم الهوية</span>
            <span class="value">{{$data['seller_id_number'] ?? ''}}</span>
          </div>
        </div>
      </div>
      <div class="party-card">
        <div class="party-card__header">
          لایەنی دووەم کریار – الطرف الثاني (المشتري)
        </div>
        <div class="party-card__body">
          <div class="info-row">
            <span class="label">کریار / المشتري</span>
            <span class="value">{{$data['name_buyer'] ?? ''}}</span>
          </div>
          <div class="info-row">
            <span class="label">دانیشتوی / السكن</span>
            <span class="value">{{$data['address_buyer'] ?? ''}}</span>
          </div>
          <div class="info-row">
            <span class="label">رقم الموبايل</span>
            <span class="value">{{$data['phone_buyer'] ?? ''}}</span>
          </div>
          <div class="info-row">
            <span class="label">رقم الهوية</span>
            <span class="value">{{$data['buyer_id_number'] ?? ''}}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="py-1 text-danger text-center" style="font-size: 13px">
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
        فرۆشتنی لایەنی یەکەم بە لایەنی دووەم ئوتومبێلی ژمارە  
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
        گۆرینەوەی لایەنی یەکەم ئوتومبێلی ژمارە 
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
      <ol class="terms-list" style="counter-reset: term 2;">
        <li style="color: brown;font-size: 11px">
          علی البائع و المشتری تسجیل السیارة حسب قوانین مدیریة المرور العامة مع إجراء معاملة نقل الملکیة
        </li>
        <li style="color: brown;font-size: 11px">
          علی المشتری فحص السیارة قبل الشراء و نحن غیر مسؤولین بعد توقیع عقد المعرض
        </li>
        <li style="color: brown;font-size: 11px">
          الطرف الاول مسؤول عن کافة أنواع الغرامات قبل موعد الشراء
        </li>
        <li style="color: brown;font-size: 11px">
          صاحب المعرض غیر مسؤول عن السیارة بعد البیع و کل عقد غیر مختوم من المعرض یعتبر باطل
        </li>
        <li style="color: brown;font-size: 11px">
          علی المشتري تسجیل السیارة خلال شهر واحد
        </li>
        <li style="color: brown;font-size: 11px">
          کتب هذا العقد بثالثة نسخ بتاریخ
          <b class="px-2">{{$data['created'] ?? ''}}</b>
          <span class="px-5">الساعة</span>
          <b class="px-2">{{ \Carbon\Carbon::parse($data['created_at'])->format('h:i:s A') }}</b>
        </li>
      </ol>
      <div class="d-flex justify-content-between  mt-3 pt-2">
        <div>
          بەلێن و رەزامەندی لایەنی یەکەم 
          فرۆشیار
             (البائع)
        </div>
        <div>
          نووسەری پێشانگا
        </div>
        <div>
          بەلێن و رەزامەندی لایەنی دووەم 
          کریار
             (المشتری)
        </div>
      </div>
      <div class="d-flex justify-content-between  mt-2">
        <div class="text-center" style="width: 184px">
          <b>
            {{$data['name_seller'] ??''}}
          </b>
        </div>
        <div class="text-center" style="width: 184px">
          <b>
            كاتب المعرض
          </b>
        </div>
        <div class="text-center" style="width: 184px">
          <b>
            {{$data['name_buyer'] ??''}}
          </b>
        </div>
      </div>
    </div>
  </div>

<script>
    $(document).ready(function() {
        const verificationUrl = @json($verificationUrl ?? '');
        const qrImg = document.getElementById('contract-qr');
        const triggerPrint = () => window.print();
        
        // دالة للانتظار حتى يتم تحميل الصورة ثم الطباعة
        const waitForImageLoad = (img) => {
            return new Promise((resolve) => {
                if (img.complete) {
                    // الصورة محملة بالفعل
                    resolve();
                } else {
                    // انتظار تحميل الصورة
                    img.onload = () => resolve();
                    img.onerror = () => resolve(); // حتى لو فشل التحميل، نكمل
                    // timeout احتياطي بعد 2 ثانية
                    setTimeout(() => resolve(), 2000);
                }
            });
        };

        const fallbackRender = async () => {
            if (verificationUrl && qrImg) {
                qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(verificationUrl);
                qrImg.style.display = 'block';
                // انتظار تحميل الصورة قبل الطباعة
                await waitForImageLoad(qrImg);
            }
            triggerPrint();
        };

        if (typeof QRCode !== 'undefined' && verificationUrl && qrImg && typeof QRCode.toDataURL === 'function') {
            QRCode.toDataURL(
                verificationUrl,
                {
                    width: 150,
                    margin: 1,
                    colorDark: "#000000",
                    colorLight: "#ffffff"
                },
                async function (error, url) {
                    if (error) {
                        console.error(error);
                        await fallbackRender();
                    } else {
                        qrImg.src = url;
                        qrImg.style.display = 'block';
                        // انتظار تحميل الصورة قبل الطباعة
                        await waitForImageLoad(qrImg);
                        // تأخير إضافي صغير للتأكد من عرض الصورة
                        setTimeout(triggerPrint, 300);
                    }
                }
            );
        } else {
            fallbackRender();
        }
    });
    </script>

</body>
</html>
