@php
use App\Helpers\Help as MyHelp;
$carLogo = MyHelp::carBrandLogo($carName ?? '');
@endphp
<div class="c3-car-card">
  @if(!empty($titleKu))
  <div class="c3-car-card-intro">{{ $titleKu }}</div>
  @endif
  <div class="c3-car-card-body{{ $carLogo ? '' : ' c3-car-card-body--no-logo' }}">
    @if($carLogo)
    <div class="c3-car-logo-side">
      <img src="{{ asset('car-logos/'.$carLogo.'.svg') }}" alt="" class="c3-car-logo" />
    </div>
    @endif
    <div class="c3-car-specs">
      <div class="c3-spec-item">
        <span class="lbl">لە جۆری (من النوع):</span>
        <span class="val">{{ $carName ?? '' }}</span>
      </div>
      <div class="c3-spec-item">
        <span class="lbl">مودیل:</span>
        <span class="val">{{ $modal ?? '' }}</span>
      </div>
      <div class="c3-spec-item">
        <span class="lbl">رەنگ (اللون):</span>
        <span class="val">{{ $color ?? '' }}</span>
      </div>
      <div class="c3-spec-item">
        <span class="lbl">ئوتومبێلی ژمارە:</span>
        <span class="val">{{ $no ?? '' }}</span>
      </div>
      <div class="c3-spec-item c3-spec-full">
        <span class="lbl">ژمارە لشە (الشاصی):</span>
        <span class="val c3-vin-val">{{ $vin ?? '' }}</span>
      </div>
      @if(!empty($annualOwnerName))
      <div class="c3-spec-item c3-spec-full">
        <span class="lbl">صاحب السنوية:</span>
        <span class="val">{{ $annualOwnerName }}</span>
      </div>
      @endif
    </div>
  </div>
</div>
