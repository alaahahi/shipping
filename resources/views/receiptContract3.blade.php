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
:root { --c3-primary: {{ $primaryColor }}; }
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
  padding: 9mm 11mm 10mm;
  max-width: 210mm;
  border: 2px solid #111;
  outline: 1px solid #111;
  outline-offset: 3px;
  min-height: 277mm;
}
.c3-watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 300px;
  max-width: 60%;
  opacity: 0.08;
  pointer-events: none;
  z-index: 0;
  user-select: none;
}
.c3-sheet > *:not(.c3-watermark) { position: relative; z-index: 1; }

/* رأس الصفحة */
.c3-top {
  display: table;
  width: 100%;
  border-bottom: 2px solid var(--c3-primary);
  padding-bottom: 8px;
  margin-bottom: 0;
}
.c3-top > div { display: table-cell; vertical-align: middle; }
.c3-top-logo { width: 22%; text-align: right; }
.c3-top-logo img { max-height: 58px; max-width: 100%; }
.c3-top-center { text-align: center; padding: 0 8px; }
.c3-top-center h1 {
  margin: 0 0 3px;
  font-size: 17px;
  font-weight: 700;
  color: #111;
}
.c3-top-center .addr {
  font-size: 12px;
  color: #333;
  margin-bottom: 4px;
}
.c3-top-center .phones {
  font-size: 11px;
  font-weight: 700;
  color: #111;
  direction: ltr;
  unicode-bidi: embed;
}
.c3-top-qr { width: 18%; text-align: left; }
.c3-top-qr img { width: 56px; height: 56px; display: block; margin-left: auto; border: 1px solid #ccc; }
.c3-top-qr span { display: block; font-size: 9px; color: var(--c3-primary); font-weight: 700; text-align: left; margin-top: 2px; }

/* شريط العنوان */
.c3-titlebar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--c3-primary);
  color: #fff;
  padding: 5px 10px;
  font-size: 12px;
  margin: 8px 0 10px;
}
.c3-titlebar .mid {
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0;
}

/* عناوين الأقسام */
.c3-h {
  font-size: 12px;
  font-weight: 700;
  color: var(--c3-primary);
  border-bottom: 1px solid var(--c3-primary);
  padding-bottom: 3px;
  margin: 0 0 6px;
}

/* الأطراف */
.c3-parties {
  display: table;
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 10px;
}
.c3-parties > div {
  display: table-cell;
  width: 50%;
  vertical-align: top;
  padding-left: 6px;
}
.c3-parties > div:first-child { padding-left: 0; padding-right: 6px; }
.c3-party {
  border: 1px solid #333;
}
.c3-party-h {
  background: #f2f2f2;
  border-bottom: 1px solid #333;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  padding: 4px;
  color: #111;
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

/* جدول السيارة */
.c3-tbl {
  width: 100%;
  border-collapse: collapse;
  font-size: 11.5px;
  margin-bottom: 10px;
}
.c3-tbl th, .c3-tbl td {
  border: 1px solid #333;
  padding: 5px 7px;
  text-align: right;
}
.c3-tbl th {
  background: #f2f2f2;
  font-weight: 700;
  width: 16%;
  color: #222;
  font-size: 11px;
}
.c3-tbl td { font-weight: 700; }

/* المبالغ */
.c3-money {
  border: 1px solid #333;
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
  border: 1px dotted #333;
  padding: 6px 8px;
  font-size: 11.5px;
  margin-bottom: 10px;
  min-height: 28px;
}
.c3-note b { margin-left: 4px; }

/* الشروط */
.c3-terms {
  border: 1px solid #333;
  margin-bottom: 10px;
}
.c3-terms-h {
  background: #f2f2f2;
  border-bottom: 1px solid #333;
  text-align: center;
  font-weight: 700;
  font-size: 12px;
  padding: 5px;
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
  .c3-sheet { outline: none; border-width: 1.5px; min-height: auto; padding: 7mm 9mm; }
  .c3-watermark { opacity: 0.07; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .c3-titlebar, .c3-party-h, .c3-tbl th, .c3-terms-h {
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

    <div class="c3-top">
      <div class="c3-top-logo">
        <img src="{{ asset('img/logo.png') }}" alt="" onerror="this.src='{{ asset('img/logo.jpg') }}'; this.onerror=null;" />
      </div>
      <div class="c3-top-center">
        <h1>{{ $companyName }}</h1>
        <div class="addr">{{ $config['third_title_ar'] ?? '' }}</div>
        @if(count($phones))
        <div class="phones">{{ implode(' &nbsp;|&nbsp; ', $phones) }}</div>
        @endif
      </div>
      <div class="c3-top-qr">
        @if(!empty($verificationUrl))
        <img id="c3-qr-img" alt="QR" style="display:none;" />
        <span>امسح QR للتحقق</span>
        @endif
      </div>
    </div>

    <div class="c3-titlebar">
      <span>الرقم: {{ $data['id'] ?? '' }}</span>
      <span class="mid">عقد بيع وشراء سيارة</span>
      <span>التاريخ: {{ $data['created'] ?? '' }}</span>
    </div>

    <div class="c3-parties">
      <div>
        <div class="c3-party">
          <div class="c3-party-h">الطرف الأول — البائع</div>
          <div class="c3-party-b">
            <div class="c3-row"><span class="k">الاسم:</span><span class="v">{{ $data['name_seller'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">رقم الهوية:</span><span class="v">{{ $data['seller_id_number'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">العنوان:</span><span class="v">{{ $data['address_seller'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">الهاتف:</span><span class="v">{{ $data['phone_seller'] ?? '' }}</span></div>
          </div>
        </div>
      </div>
      <div>
        <div class="c3-party">
          <div class="c3-party-h">الطرف الثاني — المشتري</div>
          <div class="c3-party-b">
            <div class="c3-row"><span class="k">الاسم:</span><span class="v">{{ $data['name_buyer'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">رقم الهوية:</span><span class="v">{{ $data['buyer_id_number'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">العنوان:</span><span class="v">{{ $data['address_buyer'] ?? '' }}</span></div>
            <div class="c3-row"><span class="k">الهاتف:</span><span class="v">{{ $data['phone_buyer'] ?? '' }}</span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="c3-h">بيانات السيارة</div>
    <table class="c3-tbl">
      <tr>
        <th>النوع</th><td>{{ $data['car_name'] ?? '' }}</td>
        <th>الموديل</th><td>{{ $data['modal'] ?? '' }}</td>
        <th>اللون</th><td>{{ $data['color'] ?? '' }}</td>
      </tr>
      <tr>
        <th>رقم السيارة</th><td>{{ $data['no'] ?? '' }}</td>
        <th>رقم الشاصي</th><td colspan="3">{{ $data['vin'] ?? '' }}</td>
      </tr>
      <tr>
        <th>صاحب السنوية</th><td colspan="5">{{ $data['annual_owner_name'] ?? '' }}</td>
      </tr>
    </table>

    @if(!empty($data['vin_s'] ?? null))
    <div class="c3-h">السيارة البديلة</div>
    <table class="c3-tbl">
      <tr>
        <th>النوع</th><td>{{ $data['car_name_s'] ?? '' }}</td>
        <th>الموديل</th><td>{{ $data['modal_s'] ?? '' }}</td>
        <th>اللون</th><td>{{ $data['color_s'] ?? '' }}</td>
      </tr>
      <tr>
        <th>رقم السيارة</th><td>{{ $data['no_s'] ?? '' }}</td>
        <th>رقم الشاصي</th><td colspan="3">{{ $data['vin_s'] ?? '' }}</td>
      </tr>
      <tr>
        <th>صاحب السنوية</th><td colspan="5">{{ $data['annual_owner_name_s'] ?? '' }}</td>
      </tr>
    </table>
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

    <div class="c3-h">المبالغ المالية</div>
    <div class="c3-money">
      <div class="c3-money-row">
        <span class="lbl">سعر البيع</span>
        <span class="num">{{ number_format($priceVal) }}{{ $sym }}</span>
        <span class="wrt">كتابةً</span>
        <span class="words">{{ $Help->numberToWords($priceVal, $wordsCurrency) }}</span>
      </div>
      <div class="c3-money-row">
        <span class="lbl">الواصل</span>
        <span class="num">{{ number_format($paidVal) }}{{ $sym }}</span>
        <span class="wrt">كتابةً</span>
        <span class="words">{{ $Help->numberToWords($paidVal, $wordsCurrency) }}</span>
      </div>
      <div class="c3-money-row">
        <span class="lbl">الباقي</span>
        <span class="num">{{ number_format($remainVal) }}{{ $sym }}</span>
        <span class="wrt">كتابةً</span>
        <span class="words">{{ $Help->numberToWords($remainVal, $wordsCurrency) }}</span>
      </div>
    </div>

    <div class="c3-note"><b>ملاحظات:</b> {{ $data['note'] ?? '' }}</div>

    <div class="c3-terms">
      <div class="c3-terms-h">شروط وأحكام بيع وشراء السيارة</div>
      <ul>
        @foreach($contractTerms as $term)
          <li>{{ $term }}</li>
        @endforeach
        <li>تم إنشاء هذا العقد بتاريخ {{ $data['created'] ?? '' }} الساعة {{ \Carbon\Carbon::now()->format('H:i') }}</li>
      </ul>
    </div>

    <div class="c3-sigs">
      <div>
        <div class="role">توقيع البائع</div>
        <div class="line"></div>
        <div class="name">{{ $data['name_seller'] ?? '' }}</div>
      </div>
      <div>
        <div class="role">منظم العقد</div>
        <div class="line"></div>
        <div class="name">{{ $contractOrganizer ?: '—' }}</div>
      </div>
      <div>
        <div class="role">توقيع المشتري</div>
        <div class="line"></div>
        <div class="name">{{ $data['name_buyer'] ?? '' }}</div>
      </div>
    </div>

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
