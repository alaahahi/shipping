@php
    $logoSrc = \App\Models\SystemConfig::resolveLogoUrl();
@endphp
<img src="{{ $logoSrc }}" alt="logo" style="max-width:100%;height:auto;display:block;" onerror="this.src='/img/logo.jpg'; this.onerror=null;" />
