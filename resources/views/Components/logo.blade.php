@php
    $logoSrc = \App\Models\SystemConfig::resolveLogoUrl();
    $isDefaultLogo = str_contains($logoSrc, '/img/logo.');
@endphp
<img
    src="{{ $logoSrc }}"
    alt="logo"
    style="max-width:100%;height:auto;display:block;"
    @if($isDefaultLogo)
        onerror="this.src='/img/logo.jpg'; this.onerror=null;"
    @endif
/>
