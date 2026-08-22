<?php
    $logoSrc = \App\Models\SystemConfig::resolveLogoUrl();
    $isDefaultLogo = str_contains($logoSrc, '/img/logo.');
    $logoMaxWidth = (int) ($logoMaxWidth ?? 125);
    if ($logoMaxWidth < 40) {
        $logoMaxWidth = 125;
    }
?>
<img
    src="<?php echo e($logoSrc); ?>"
    alt="logo"
    width="<?php echo e($logoMaxWidth); ?>"
    class="app-print-logo"
    style="width:<?php echo e($logoMaxWidth); ?>px !important;max-width:<?php echo e($logoMaxWidth); ?>px !important;height:auto !important;display:inline-block;object-fit:contain;"
    <?php if($isDefaultLogo): ?>
        onerror="this.src='/img/logo.jpg'; this.onerror=null;"
    <?php endif; ?>
/>
<?php /**PATH C:\xampp\htdocs\shipping\resources\views/Components/logo.blade.php ENDPATH**/ ?>