@php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
$contractTerms = is_array($config) ? ($config['contract_terms_2'] ?? $config['contract_terms'] ?? []) : ($config->contract_terms_2 ?? $config->contract_terms ?? []);
$primaryColor = is_array($config) ? ($config['primary_color'] ?? '#c00') : ($config->primary_color ?? '#c00');
$contractOrganizer = $contractOrganizer ?? '';
if (is_string($contractTerms)) {
  $contractTerms = json_decode($contractTerms, true) ?? [];
}
if (!is_array($contractTerms)) {
  $contractTerms = [];
}
$contractTerms = array_filter($contractTerms, function($t) { return !empty(trim($t)); });
$companyName = $config['second_title_ar'] ?? $config['first_title_ar'] ?? config('app.company_name');
$phones = config('car_contract.phones', []);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>{{ $companyName }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<style>
:root { --c3-primary: {{ $primaryColor }}; --c3-r: 10px; }
@font-face { font-family: 'Peshang'; src: url('/Peshang.ttf') format('truetype'); }
* { box-sizing: border-box; }
body {
  font-family: 'Peshang', Tahoma, Arial, sans-serif;
  background: #fff;
  direction: rtl;
  color: #111;
  margin: 0;
}
@page { size: A4; margin: 0; }
html, body { width: 210mm; margin: 0; padding: 0; }

.c3-sheet {
  position: relative;
  overflow: hidden;
  margin: 0 auto;
  padding: 8mm 10mm 10mm;
  max-width: 210mm;
  border: 1px solid #d4d4d4;
  border-radius: 12px;
  min-height: 277mm;
}
.c3-watermark {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  transform: translateY(-50%);
  width: 100%;
  max-width: 100%;
  height: auto;
  opacity: 0.05;
  pointer-events: none;
  z-index: 0;
  user-select: none;
  object-fit: contain;
}
.c3-body { position: relative; z-index: 1; }

/* ── الهيدر ── */
.c3-head {
  border-radius: var(--c3-r);
  overflow: hidden;
  margin-bottom: 12px;
  border: 1px solid #1a1a1a;
}
.c3-head-main {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px 12px;
  background: #000;
}
.c3-head-logo {
  flex: 0 0 auto;
}
.c3-head-logo img {
  max-height: 82px;
  max-width: 145px;
  display: block;
  object-fit: contain;
}
.c3-head-info {
  flex: 1;
  text-align: center;
  min-width: 0;
}
.c3-head-info h1 {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 700;
  color: #fff;
  line-height: 1.3;
}
.c3-head-info .addr {
  font-size: 11.5px;
  color: #ccc;
  margin-bottom: 5px;
}
.c3-head-info .phones {
  font-size: 10.5px;
  font-weight: 700;
  color: #f0f0f0;
  direction: ltr;
  unicode-bidi: embed;
}
.c3-head-qr {
  flex: 0 0 auto;
  text-align: center;
}
.c3-head-qr img {
  width: 60px;
  height: 60px;
  display: block;
  margin: 0 auto;
  border: none;
  background: #fff;
}
.c3-head-qr span {
  display: block;
  font-size: 8.5px;
  color: var(--c3-primary);
  font-weight: 700;
  margin-top: 3px;
}
.c3-head-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--c3-primary);
  color: #fff;
  padding: 7px 14px;
  font-size: 11.5px;
}
.c3-head-bar .mid {
  font-size: 13.5px;
  font-weight: 700;
}

/* عناوين الأقسام */
.c3-h {
  font-size: 12px;
  font-weight: 700;
  color: var(--c3-primary);
  border-bottom: 1px solid var(--c3-primary);
  padding-bottom: 3px;
  margin: 0 0 6px;
  line-height: 1.45;
}
.c3-h .ku { display: block; font-size: 11px; color: #333; font-weight: 600; }
.c3-agree {
  text-align: center;
  font-size: 11.5px;
  color: var(--c3-primary);
  margin-bottom: 10px;
  line-height: 1.5;
}
.c3-party-h {
  font-size: 11px;
  line-height: 1.4;
  background: var(--c3-primary);
  border-bottom: 1px solid var(--c3-primary);
  text-align: center;
  font-weight: 700;
  padding: 6px 8px;
  color: #fff;
}
.c3-tbl th { font-size: 10px; line-height: 1.35; }

/* بطاقة السيارة */
.c3-car-card {
  border: 1px solid #ddd;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 12px;
  background: #fff;
}
.c3-car-card-intro {
  background: var(--c3-primary);
  color: #fff;
  font-size: 11.5px;
  font-weight: 700;
  line-height: 1.45;
  padding: 7px 14px;
  text-align: center;
}
.c3-car-card-body {
  display: flex;
  align-items: stretch;
  min-height: 0;
}
.c3-car-logo-col {
  flex: 0 0 78px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 10px;
  background: #fafafa;
  border-left: 1px solid #eee;
}
.c3-car-logo {
  width: 58px;
  height: 58px;
  object-fit: contain;
}
.c3-car-specs {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  min-width: 0;
}
.c3-spec-item {
  padding: 10px 12px;
  border-left: 1px solid #eee;
  border-bottom: 1px solid #eee;
}
.c3-spec-item:nth-child(3n) { border-left: none; }
.c3-spec-item .lbl {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: var(--c3-primary);
  margin-bottom: 5px;
  line-height: 1.35;
}
.c3-spec-item .val {
  display: block;
  font-size: 13.5px;
  font-weight: 700;
  color: #111;
  word-break: break-word;
  line-height: 1.35;
}
.c3-spec-wide {
  grid-column: span 2;
  border-left: none;
}
.c3-spec-full {
  grid-column: 1 / -1;
  border-left: none;
  border-bottom: none;
  background: #fafafa;
}
.c3-car-specs .c3-spec-item:nth-child(4) { border-left: none; }
.c3-money-row .lbl { font-size: 10px; line-height: 1.35; min-width: 0; flex: 0 1 38%; }
.c3-sigs .role { font-size: 10px; line-height: 1.4; }

/* الأطراف */
.c3-parties {
  display: flex;
  gap: 16px;
  margin-bottom: 10px;
}
.c3-parties > div {
  flex: 1;
  min-width: 0;
}
.c3-party {
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
}
.c3-party-b { padding: 6px 8px; }
.c3-row {
  display: flex;
  font-size: 11.5px;
  margin-bottom: 5px;
  align-items: baseline;
}
.c3-row:last-child { margin-bottom: 0; }
.c3-row .k { font-weight: 600; white-space: nowrap; margin-left: 4px; }
.c3-row .v {
  flex: 1;
  border-bottom: 1px dotted #555;
  font-weight: 700;
  min-height: 17px;
  padding: 0 3px;
}

/* جدول السيارة — محذوف، استُبدل بـ c3-car-card */

/* المبالغ */
.c3-money {
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 10px;
}
.c3-money-row {
  display: flex;
  align-items: center;
  font-size: 11.5px;
  padding: 5px 8px;
  border-bottom: 1px solid #ddd;
}
.c3-money-row:last-child { border-bottom: none; }
.c3-money-row .lbl { color: var(--c3-primary); font-weight: 700; min-width: 58px; }
.c3-money-row .num {
  min-width: 95px;
  border-bottom: 1px dotted #444;
  font-weight: 700;
  margin: 0 6px;
  text-align: center;
}
.c3-money-row .wrt { color: #444; font-size: 10.5px; margin-left: 4px; }
.c3-money-row .words {
  flex: 1;
  border-bottom: 1px dotted #444;
  font-weight: 700;
  margin-right: 4px;
}

/* ملاحظات */
.c3-note {
  border: 1px dashed #bbb;
  border-radius: 8px;
  padding: 6px 10px;
  font-size: 11.5px;
  margin-bottom: 10px;
  min-height: 28px;
}
.c3-note b { margin-left: 4px; }

/* الشروط */
.c3-terms {
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 10px;
}
.c3-terms-h {
  background: #f5f5f5;
  border-bottom: 1px solid #ddd;
  text-align: center;
  font-weight: 700;
  font-size: 11px;
  padding: 6px 8px;
  line-height: 1.45;
}
.c3-terms ul {
  margin: 0;
  padding: 6px 10px 6px 20px;
  list-style: none;
}
.c3-terms li {
  font-size: 11px;
  line-height: 1.55;
  margin-bottom: 2px;
  text-indent: -14px;
  padding-right: 14px;
}
.c3-terms li::before { content: '- '; font-weight: 700; color: var(--c3-primary); }

/* التوقيعات */
.c3-sigs {
  display: table;
  width: 100%;
  margin-top: 14px;
  border-top: 1px solid #333;
  padding-top: 8px;
}
.c3-sigs > div {
  display: table-cell;
  width: 33.33%;
  text-align: center;
  font-size: 11.5px;
  vertical-align: bottom;
  padding: 0 4px;
}
.c3-sigs .role { font-weight: 700; margin-bottom: 24px; font-size: 11px; }
.c3-sigs .line { border-top: 1px solid #111; margin: 0 10px 4px; }
.c3-sigs .name { font-weight: 700; }

@media print {
  body { background: #fff; }
  .c3-sheet { border-width: 1px; min-height: auto; padding: 7mm 9mm; }
  .c3-watermark { opacity: 0.045; }
  .c3-head-main, .c3-head-bar, .c3-party-h, .c3-car-card-intro, .c3-terms-h {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
<body>
  <div class="c3-sheet">
    <img
      class="c3-watermark"
      src="{{ asset('img/logo.png') }}"
      alt=""
      onerror="this.src='{{ asset('img/logo.jpg') }}'; this.onerror=null;"
    />

    <div class="c3-body">

    <header class="c3-head">
      <div class="c3-head-main">
        <div class="c3-head-logo">
          <img src="{{ asset('img/logo.png') }}" alt="" onerror="this.src='{{ asset('img/logo.jpg') }}'; this.onerror=null;" />
        </div>
        <div class="c3-head-info">
          <h1>{{ $companyName }}</h1>
          <div class="addr">{{ $config['third_title_ar'] ?? '' }}</div>
          @if(count($phones))
          <div class="phones">{{ implode(' &nbsp;|&nbsp; ', $phones) }}</div>
          @endif
        </div>
        <div class="c3-head-qr">
          @if(!empty($verificationUrl))
          <img id="c3-qr-img" alt="QR" style="display:none;" />
          <span>امسح QR للتحقق</span>
          @endif
        </div>
      </div>
      <div class="c3-head-bar">
        <span>الرقم: {{ $data['id'] ?? '' }}</span>
        <span class="mid">عقد بيع وشراء سيارة</span>
        <span>التاريخ: {{ $data['created'] ?? '' }}</span>
      </div>
    </header>

    <div class="c3-parties">
      <div>
        <div class="c3-party">
          <div class="c3-party-h">لایەنی یەکەم فرۆشیار – الطرف الأول (البائع)</div>
          <div class="c3-party-b">
            <div class="c3-row"><span class="k">فرۆشیار / البائع</span><span class="v">{{ $data['name_seller'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">رقم الهوية</span><span class="v">{{ $data['seller_id_number'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">دانیشتوی / السكن</span><span class="v">{{ $data['address_seller'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">رقم الموبايل</span><span class="v">{{ $data['phone_seller'] ?? '' }}</span></div>
          </div>
        </div>
      </div>
      <div>
        <div class="c3-party">
          <div class="c3-party-h">لایەنی دووەم کریار – الطرف الثاني (المشتري)</div>
          <div class="c3-party-b">
            <div class="c3-row"><span class="k">کریار / المشتري</span><span class="v">{{ $data['name_buyer'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">رقم الهوية</span><span class="v">{{ $data['buyer_id_number'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">دانیشتوی / السكن</span><span class="v">{{ $data['address_buyer'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">رقم الموبايل</span><span class="v">{{ $data['phone_buyer'] ?? '' }}</span></div>
          </div>
        </div>
      </div>
    </div>

  

    @include('partials.c3-car-spec', [
      'titleKu' => 'فرۆشتنی لایەنی یەکەم بە لایەنی دووەم ئوتومبێلی ژمارە (بيع سيارة الطرف الأول إلى الطرف الثاني رقم)',
      'carName' => $data['car_name'] ?? '',
      'modal' => $data['modal'] ?? '',
      'color' => $data['color'] ?? '',
      'no' => $data['no'] ?? '',
      'vin' => $data['vin'] ?? '',
      'annualOwnerName' => $data['annual_owner_name'] ?? '',
    ])

    @if(!empty($data['vin_s'] ?? null))
    @include('partials.c3-car-spec', [
      'titleKu' => 'گۆرینەوەی لایەنی یەکەم ئوتومبێلی ژمارە (السيارة البديلة)',
      'carName' => $data['car_name_s'] ?? '',
      'modal' => $data['modal_s'] ?? '',
      'color' => $data['color_s'] ?? '',
      'no' => $data['no_s'] ?? '',
      'vin' => $data['vin_s'] ?? '',
      'annualOwnerName' => $data['annual_owner_name_s'] ?? '',
    ])
    @endif

    @php
      $cfg = is_array($config) ? $config : (array) $config;
      $currency = $cfg['contract_currency'] ?? 'usd';
      $rate = (float) ($cfg['usd_to_dinar_rate'] ?? 0);
      if ($rate < 100) { $rate = 150000; }
      $price = (float) ($data['car_price'] ?? 0);
      $paid = (float) ($data['car_paid'] ?? 0);
      $remain = $price - $paid;
      if ($currency === 'dinar') {
        $priceVal = round($price * ($rate / 100));
        $paidVal = round($paid * ($rate / 100));
        $remainVal = round($remain * ($rate / 100));
        $sym = ' د.ع';
        $wordsCurrency = 'iqd';
      } else {
        $priceVal = $price;
        $paidVal = $paid;
        $remainVal = $remain;
        $sym = ' $';
        $wordsCurrency = 'usd';
      }
    @endphp

    <div class="c3-money">
      <div class="c3-money-row">
        <span class="lbl">لە جیاتی / بڕی پارە (بمبلغ قدره)</span>
        <span class="num">{{ number_format($priceVal) }}{{ $sym }}</span>
        <span class="wrt">كتابةً</span>
        <span class="words">{{ $Help->numberToWords($priceVal, $wordsCurrency) }}</span>
      </div>
      <div class="c3-money-row">
        <span class="lbl">فرۆشیار وەری گرت بڕی پارە (وقد قبض)</span>
        <span class="num">{{ number_format($paidVal) }}{{ $sym }}</span>
        <span class="wrt">كتابةً</span>
        <span class="words">{{ $Help->numberToWords($paidVal, $wordsCurrency) }}</span>
      </div>
      <div class="c3-money-row">
        <span class="lbl">ئەو برەی ماوەتەوە (الباقی)</span>
        <span class="num">{{ number_format($remainVal) }}{{ $sym }}</span>
        <span class="wrt">كتابةً</span>
        <span class="words">{{ $Help->numberToWords($remainVal, $wordsCurrency) }}</span>
      </div>
    </div>

    <div class="c3-note"><b>تێبینی (ملاحظة):</b> {{ $data['note'] ?? '' }}</div>
    <div class="c3-agree">
      <div>رێکەوتن کرا لە نێوان هەردوو لیەن لە سەر ئەم خالنەی خوارەوه</div>
      <div>وتم الاتفاق على النقاط التالية بين الطرفين</div>
    </div>image.png
    <div class="c3-terms">
  
      <ul>
        @foreach($contractTerms as $term)
          <li>{{ $term }}</li>
        @endforeach
        <li>تم إنشاء هذا العقد بتاريخ {{ $data['created'] ?? '' }} الساعة {{ \Carbon\Carbon::now()->format('H:i') }}</li>
      </ul>
    </div>

    <div class="c3-sigs">
      <div>
        <div class="role">بەلێن و رەزامەندی لایەنی یەکەم فرۆشیار (البائع)</div>
        <div class="line"></div>
        <div class="name">{{ $data['name_seller'] ?? '' }}</div>
      </div>
      <div>
        <div class="role">نووسەری پێشانگا</div>
        <div class="line"></div>
        <div class="name">{{ $contractOrganizer ?: 'كاتب المعرض' }}</div>
      </div>
      <div>
        <div class="role">بەلێن و رەزامەندی لایەنی دووەم کریار (المشتری)</div>
        <div class="line"></div>
        <div class="name">{{ $data['name_buyer'] ?? '' }}</div>
      </div>
    </div>

    </div><!-- .c3-body -->

  </div>

  @if(!empty($verificationUrl))
  <script>
    $(document).ready(function() {
        var verificationUrl = @json($verificationUrl ?? '');
        var qrImg = document.getElementById('c3-qr-img');
        var triggerPrint = function() { window.print(); };
        var waitForImageLoad = function(img) {
            return new Promise(function(resolve) {
                if (img.complete) { resolve(); }
                else {
                    img.onload = function() { resolve(); };
                    img.onerror = function() { resolve(); };
                    setTimeout(function() { resolve(); }, 2000);
                }
            });
        };
        var fallbackRender = async function() {
            if (verificationUrl && qrImg) {
                qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(verificationUrl);
                qrImg.style.display = 'block';
                await waitForImageLoad(qrImg);
            }
            triggerPrint();
        };
        if (typeof QRCode !== 'undefined' && verificationUrl && qrImg && typeof QRCode.toDataURL === 'function') {
            QRCode.toDataURL(verificationUrl, { width: 150, margin: 1, colorDark: '#000000', colorLight: '#ffffff' }, async function(err, url) {
                if (err) {
                    await fallbackRender();
                } else {
                    qrImg.src = url;
                    qrImg.style.display = 'block';
                    await waitForImageLoad(qrImg);
                    setTimeout(triggerPrint, 300);
                }
            });
        } else {
            fallbackRender();
        }
    });
  </script>
  @endif
  @if(empty($verificationUrl))
  <script>$(function() { setTimeout(window.print, 500); });</script>
  @endif
</body>
</html>
