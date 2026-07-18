@php
    $logoSrc = \App\Models\SystemConfig::resolveLogoUrl();
    $isDefaultLogo = str_contains($logoSrc, '/img/logo.');
    $logoMaxWidth = (int) ($logoMaxWidth ?? 125);
    if ($logoMaxWidth < 40) {
        $logoMaxWidth = 125;
    }
@endphp
<img
    src="{{ $logoSrc }}"
    alt="logo"
    width="{{ $logoMaxWidth }}"
    class="app-print-logo"
    style="width:{{ $logoMaxWidth }}px !important;max-width:{{ $logoMaxWidth }}px !important;height:auto !important;display:inline-block;object-fit:contain;"
    @if($isDefaultLogo)
        onerror="this.src='/img/logo.jpg'; this.onerror=null;"
    @endif
/>
