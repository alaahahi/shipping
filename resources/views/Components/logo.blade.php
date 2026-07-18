@php
    $logoSrc = \App\Models\SystemConfig::resolveLogoUrl();
@endphp
<img src="{{ $logoSrc }}" alt="logo" onerror="this.src='/img/logo.jpg'; this.onerror=null;" />
