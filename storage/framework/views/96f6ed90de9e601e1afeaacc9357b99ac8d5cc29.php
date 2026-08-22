<?php
  $contact = \App\Helpers\Help::receiptContact($config ?? null, $owner_id ?? null);
?>
<div class="<?php echo e($rowClass ?? 'row p-2 border-top border-bottom mt-3'); ?>" style="font-size: 14px">
    <div class="col-6 pe-5"><?php echo e($addressLabel ?? 'العنوان:'); ?> <?php echo e($contact['address']); ?></div>
    <div class="col-6 ps-5 text-start"><?php echo e($mobileLabel ?? 'Mobile:'); ?> <?php echo e($contact['mobile']); ?></div>
</div>
<?php /**PATH C:\xampp\htdocs\shipping\resources\views/Components/receiptFooterContact.blade.php ENDPATH**/ ?>