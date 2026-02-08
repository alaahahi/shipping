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
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.company_name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<style>
:root { --t2-radius: 8px; --t2-primary: {{ $primaryColor }}; }
@font-face { font-family: 'Peshang'; src: url('/Peshang.ttf') format('truetype'); }
body { font-family: 'Peshang', sans-serif; background: #fff; direction: rtl; color: #000; }
@page { size: A4; margin: 0; }
html, body { width: 210mm; margin: 0; padding: 0; }
.t2-page { padding: 10mm 13mm; max-width: 210mm; margin: 0 auto; background: #fff; border-radius: var(--t2-radius); }
.t2-meta { display: flex; justify-content: space-between; align-items: center; font-size: 11px; margin-bottom: 8px; color: #000; border: 1px solid var(--t2-primary); padding: 6px 12px; background: #fff; border-radius: var(--t2-radius); }
.t2-header { display: flex; justify-content: space-between; align-items: flex-start; border: 2px solid #000; padding: 10px 12px; margin-bottom: 10px; background: #fff; border-radius: var(--t2-radius); }
.t2-header-right { text-align: right; }
.t2-company { font-weight: 700; font-size: 15px; color: #000; }
.t2-badge { display: inline-block; background: #f5c542; color: #000; width: 32px; height: 32px; line-height: 32px; text-align: center; font-weight: 700; font-size: 18px; margin-left: 6px; vertical-align: middle; border-radius: var(--t2-radius); }
.t2-address { font-size: 12px; color: #000; margin-top: 4px; }
.t2-header-left { text-align: left; display: flex; align-items: flex-start; gap: 12px; }
.t2-logo-wrap { border: 1px solid #000; padding: 5px; margin-bottom: 6px; background: #fff; display: inline-block; border-radius: var(--t2-radius); overflow: hidden; }
.t2-logo { max-height: 50px; display: block; border-radius: 4px; }
.t2-header-qr { display: flex; flex-direction: column; align-items: center; gap: 2px; border: none; }
.t2-header-qr img { width: 50px; height: 50px; display: block; }
.t2-header-qr .t2-qr-caption { font-size: 10px; color: var(--t2-primary); font-weight: 700; }
.t2-phones { font-size: 11px; color: #000; font-weight: 600; }
.t2-phones span { display: block; margin-bottom: 2px; }
.t2-section { margin-bottom: 10px; }
.t2-section-title { font-weight: 700; font-size: 13px; margin-bottom: 6px;  }
.t2-party-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.t2-party-box { border: 1px solid var(--t2-primary); padding: 10px 12px; background: #fff; border-radius: var(--t2-radius); }
.t2-party-box:first-child .t2-section-title {text-align: center; background: var(--t2-primary); color: #fff; border: 1px solid var(--t2-primary); border-radius: var(--t2-radius);font-size: 16px; }
.t2-party-box:last-child .t2-section-title {text-align: center; background: var(--t2-primary); color: #fff; border: 1px solid var(--t2-primary); border-radius: var(--t2-radius);font-size: 16px; }
.t2-dotted-row { display: flex; align-items: baseline; margin-bottom: 6px; font-size: 12px; color: #000; }
.t2-dotted-row .label {  color: #000; font-weight: 600; }
.t2-dotted-row .value { flex: 1; border-bottom: 1px dotted #000; margin-right: 4px; min-height: 20px; background: #fcfcfc; padding: 0 4px; border-radius: 2px;font-weight: 700; }
.t2-car-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 14px; font-size: 12px; }
.t2-car-item { display: flex; gap: 8px; color: #000; }
.t2-car-item .label { color: #000; font-weight: 600; }
.t2-car-item .value { font-weight: 700; }
.t2-money-row { display: flex; align-items: baseline; margin-bottom: 8px; font-size: 12px; border-bottom: 1px dotted #000; padding-bottom: 4px; }
.t2-money-row .label { min-width: 130px; color: #000; }
.t2-money-row .value { flex: 1; margin-right: 8px; min-height: 20px; font-weight: 700; }
.t2-money-inline { display: flex; justify-content: space-between; align-items: stretch; gap: 16px; font-size: 12px; border-bottom: 1px dotted #000; padding-bottom: 8px; }
.t2-money-inline .item { display: flex; flex-direction: column; gap: 2px; flex: 1; min-width: 0; }
.t2-money-inline .item .label { color: #000; font-weight: 600; }
.t2-money-inline .item .value { font-weight: 700; color: #000; }
.t2-money-inline .item .words { font-size: 11px; color: #333; font-weight: 500; }
/* صندوق السعر والواصل والباقي - شكل العقود الرسمي */
.t2-money-box { border: 1px solid var(--t2-primary); padding: 10px 12px; background: #fff; border-radius: var(--t2-radius); }
.t2-money-line { display: flex; align-items: center; margin-bottom: 8px; font-size: 12px; direction: rtl; }
.t2-money-line:last-child { margin-bottom: 0; }
.t2-money-line > span { line-height: 20px; }
.t2-money-line .t2-ml-label { color: var(--t2-primary); font-weight: 700; white-space: nowrap; margin-right: 4px; }
.t2-money-line .t2-ml-num { flex: 0 0 28%; min-width: 80px; border-bottom: 1px dotted #333; margin: 0 4px; min-height: 20px; font-weight: 700; }
.t2-money-line .t2-ml-written { color: var(--t2-primary); font-weight: 600; font-size: 11px; margin: 0 4px; white-space: nowrap; line-height: 20px; }
.t2-money-line .t2-ml-words { flex: 1; border-bottom: 1px dotted #333; margin-right: 4px; min-height: 20px; font-weight: 700; }
.t2-notes-row { display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 8px; border: 1px dotted #000; padding: 6px 10px; min-height: 32px; background: #fff; border-radius: var(--t2-radius); }
.t2-notes-row .label { font-weight: 700; color: #000; white-space: nowrap; }
.t2-notes-row .value { flex: 1; min-height: 20px; color: #000; }
.t2-terms-title { color: var(--t2-primary); font-weight: 700; font-size: 17px; margin: 8px 0 4px; padding: 4px 0; border-bottom: 2px solid var(--t2-primary); border-radius: 2px; }
.t2-terms-wrap { position: relative; margin-bottom: 6px; }
.t2-terms-wrap.has-qr { padding-left: 88px; }
.t2-terms-list { margin: 0; padding: 6px 8px 6px 14px; list-style: none; border: 1px solid var(--t2-primary); background: #fff; border-radius: var(--t2-radius); }
.t2-terms-list li { font-size: 12px; margin-bottom: 3px; line-height: 1.5; position: relative; padding-right: 6px; color: #000; }
.t2-terms-list li::before { content: "* "; color: var(--t2-primary); font-weight: 700; }
.t2-terms-qr { position: absolute; left: 0; bottom: 0; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.t2-terms-qr img { width: 72px; height: 72px; border: 1px solid var(--t2-primary); background: #fff; display: block; border-radius: var(--t2-radius); }
.t2-terms-qr .t2-qr-caption { font-size: 12px; color: var(--t2-primary); font-weight: 700; }
.t2-signatures { display: flex; justify-content: space-between; margin-top: 18px; padding-top: 12px; border-top: 2px solid var(--t2-primary); font-size: 12px; border-radius: 0 0 var(--t2-radius) var(--t2-radius); }
.t2-sig-col { text-align: center; flex: 1; color: #000; }
.t2-sig-label { font-weight: 700; margin-bottom: 22px; color: #000; }
@media print {
  body { background: #fff; }
  .t2-page { padding: 8mm 11mm; box-shadow: none; }
  html, body { height: auto; min-height: auto; }
}
</style>
<body>
  <div class="t2-page">
    <div class="t2-meta">
      <span>الرقم: {{ $data['id'] ?? '' }}</span>
      <span>التاريخ: {{ $data['created'] ?? '' }}</span>
    </div>

    <div class="t2-header">
      <div class="t2-header-right">
        <div class="t2-company">
          {{ $config['second_title_ar'] ?? $config['first_title_ar'] ?? config('app.company_name') }}
          <span class="t2-badge">2</span>
        </div>
        <div class="t2-address">العنوان / {{ $config['third_title_ar'] ?? '—' }}</div>
      </div>
      @if(!empty($verificationUrl))
      <div class="t2-header-qr">
        <img id="t2-qr-img" alt="QR" style="display:none;" />
        <span class="t2-qr-caption">امسح QR للتحقق</span>
      </div>
      @endif
      <div class="t2-header-left">
 
        <div class="t2-logo-wrap">
          <img src="{{ asset('img/logo.png') }}" alt="Logo" class="t2-logo" onerror="this.src='{{ asset('img/logo.jpg') }}'; this.onerror=null;" />
        </div>
      
        <div class="t2-phones mt-1">
          @php
            $phones = $config['phones'] ?? ['07701575738','07707588987','07718456595'];
            if (is_string($phones)) { $phones = array_filter(array_map('trim', explode(',', $phones))); }
            if (empty($phones)) { $phones = ['07701575738','07707588987','07718456595']; }
          @endphp
          @foreach($phones as $p) <span>{{ $p }}</span> @endforeach
        </div>
      </div>
    </div>

    <div class="t2-section t2-party-grid">
      <div class="t2-party-box">
        <div class="t2-section-title">الطرف الاول</div>
        <div class="t2-dotted-row"><span class="label">اسم البائع:</span><span class="value">{{ $data['name_seller'] ?? '' }}</span></div>
        <div class="t2-dotted-row"><span class="label">رقم الهوية:</span><span class="value">{{ $data['seller_id_number'] ?? '' }}</span></div>
        <div class="t2-dotted-row"><span class="label">عنوان السكن:</span><span class="value">{{ $data['address_seller'] ?? '' }}</span></div>
        <div class="t2-dotted-row"><span class="label">رقم الهاتف:</span><span class="value">{{ $data['phone_seller'] ?? '' }}</span></div>
      </div>
      <div class="t2-party-box">
        <div class="t2-section-title">الطرف الثاني</div>
        <div class="t2-dotted-row"><span class="label">اسم المشتري:</span><span class="value">{{ $data['name_buyer'] ?? '' }}</span></div>
        <div class="t2-dotted-row"><span class="label">رقم الهوية:</span><span class="value">{{ $data['buyer_id_number'] ?? '' }}</span></div>
        <div class="t2-dotted-row"><span class="label">عنوان السكن:</span><span class="value">{{ $data['address_buyer'] ?? '' }}</span></div>
        <div class="t2-dotted-row"><span class="label">رقم الهاتف:</span><span class="value">{{ $data['phone_buyer'] ?? '' }}</span></div>
      </div>
    </div>

    <div class="t2-section">
      <div class="t2-section-title">تفاصيل السيارة</div>
      <div class="t2-car-grid" style=" padding: 5px 8px;border: 1px solid var(--t2-primary); color: #000; border-radius: var(--t2-radius);">
        <div class="t2-car-item"><span class="label">نوع السيارة:</span><span class="value">{{ $data['car_name'] ?? '' }}</span></div>
        <div class="t2-car-item"><span class="label">رقم السيارة:</span><span class="value">{{ $data['no'] ?? '' }}</span></div>
        <div class="t2-car-item"><span class="label">رقم الشاصي:</span><span class="value">{{ $data['vin'] ?? '' }}</span></div>

        <div class="t2-car-item"><span class="label">لون السيارة:</span><span class="value">{{ $data['color'] ?? '' }}</span></div>
        <div class="t2-car-item"><span class="label">الموديل:</span><span class="value">{{ $data['modal'] ?? '' }}</span></div>
        <div class="t2-car-item"><span class="label">صاحب السنوية:</span><span class="value">{{ $data['annual_owner_name'] ?? '' }}</span></div>
      </div>
    </div>

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
    <div class="t2-section">
      <div class="t2-money-box">
        <div class="t2-money-line">
          <span class="t2-ml-label">سعر البيع</span>
          <span class="t2-ml-num">/ {{ number_format($priceVal) }}{{ $sym }}</span>
          <span class="t2-ml-written">كتابتاً</span>
          <span class="t2-ml-words">/ {{ $Help->numberToWords($priceVal, $wordsCurrency) }}</span>
        </div>
        <div class="t2-money-line">
          <span class="t2-ml-label" style="
              width: 36px;
          ">الواصل</span>
          <span class="t2-ml-num">/ {{ number_format($paidVal) }}{{ $sym }}</span>
          <span class="t2-ml-written">كتابتاً</span>
          <span class="t2-ml-words">/ {{ $Help->numberToWords($paidVal, $wordsCurrency) }}</span>
        </div>
        <div class="t2-money-line">
          <span class="t2-ml-label" style="
              width: 36px;
          ">الباقي</span>
          <span class="t2-ml-num">/ {{ number_format($remainVal) }}{{ $sym }}</span>
          <span class="t2-ml-written">كتابتاً</span>
          <span class="t2-ml-words">/ {{ $Help->numberToWords($remainVal, $wordsCurrency) }}</span>
        </div>
      </div>
    </div>

    <div class="t2-section">
      <div class="t2-notes-row">
        <span class="label">الملاحظات /</span>
        <span class="value">{{ $data['note'] ?? '' }}</span>
      </div>
    </div>

    <div class="t2-terms-title">شروط وأحكام بيع وشراء السيارة</div>
    <div class="t2-terms-wrap">
      <ul class="t2-terms-list">
        @foreach($contractTerms as $term)
          <li>{{ $term }}</li>
        @endforeach
        <li>تم إنشاء هذا العقد بتاريخ {{ $data['created'] ?? '' }} في الساعة {{ \Carbon\Carbon::now()->format('H:i') }}</li>
      </ul>
    </div>

    <div class="t2-signatures">
      <div class="t2-sig-col"><div class="t2-sig-label">الطرف الأول (البائع)</div><div>{{ $data['name_seller'] ?? '' }}</div></div>
      <div class="t2-sig-col"><div class="t2-sig-label">منظم العقد</div><div>{{ $contractOrganizer ?: '—' }}</div></div>
      <div class="t2-sig-col"><div class="t2-sig-label">الطرف الثاني (المشتري)</div><div>{{ $data['name_buyer'] ?? '' }}</div></div>
    </div>
  </div>

  @if(!empty($verificationUrl))
  <script>
    $(document).ready(function() {
        var verificationUrl = @json($verificationUrl ?? '');
        var qrImg = document.getElementById('t2-qr-img');
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
                    console.error(err);
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
