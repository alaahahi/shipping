
<?php
use App\Helpers\Help as MyHelp;
$Help = new MyHelp();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo e(config('app.company_name')); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    @page {
      size: auto; /* auto is the initial value */

      /* this affects the margin in the printer settings */
      margin: 15px;
      margin-top: 60px;
    }
    </style>
</head>
<?php
  // تعريف القيم الافتراضية
  $currency = '$';
  $description = '';
  $amount = 0;
  $created = '';
?>

<?php if($transaction): ?>

  <?php  
  $currency = $transaction->currency ?? '$';
  $description =$transaction->description ?? '';
  $amount= ($transaction->amount ?? 0)-($transaction->discount ?? 0);
  if($amount<0){
    $amount= $amount * -1;
  }
  $created =$transaction->created_at ?? '';
  ?>

<?php endif; ?>
<body style="direction: rtl;">
<div class="container-fluid mt-2 " style="border: 2px solid">       
<div class="row" >
    <div class="col-4 text-center py-3">
        <h5 class="pt-3">
       <?php echo e($config['first_title_ar']); ?>

        </h5>
        <h5>
        <?php echo e($config['second_title_ar']); ?>

        </h5>
    </div>
    <div class="col-4 text-center py-3">

    
    <h5 class="pt-3"> وصل دفع</h5>
    <h5 class="pt-1">Cash Receipt Voucher </h5>
    </div>
    <div class="col-4 text-center py-3"> 
        <div style="max-width:125px;margin:0 auto;">
            <?php echo $__env->make('Components.logo', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

    </div>
    </div>
    <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
      <div class="col-3"> 
      الرقم:
      <?php echo e($transactions_id); ?>

    </div>
      <div class="col-3">
 
      </div>
      <div class="col-3">
    
      </div>
      <div class="col-3">
         تاريخ:
      <?= $created ??'' ?>
      </div>
    </div>

    <div class="row p-2" style="font-size: 14px">
    <div class="col-12  p-2 pe-5"> 
    شركة / Company:
    <?php echo e($clientData['client']->name); ?>

    </div>
    <div class="col-12  p-2  pe-5"> 
    دفع مبلغ ل:
      <?php echo e($clientData['client']->name); ?>

      </div>

      <div class="col-12  p-2  pe-5"> 
         مبلغ قدره :
         <?php echo e($Help->numberToWords($amount??0,($currency??'$'))); ?>

        </div>
        <div class="col-12  p-2  pe-5"> 
          الملاحظات:
          <?php echo e($description??''); ?>

         </div>
        
        
      
  </div>
  <div class="row  text-center   "  style="font-size: 14px">
    <div class="col-1">
      </div>
    <div class="col-1 alert-primary border p-2"> 
     المبلغ:
    </div>
    <div class="col-1 alert-primary border p-2">
    <?php echo e($amount ?? 0); ?>

    </div>
    <div class="col-1 alert-primary border p-2">
      <?php echo e($currency ?? '$'); ?>

    </div>
    <div class="col-8 text-start ps-5">
      اسم وتوقيع المستلم
    </div>
  </div>
  <?php echo $__env->make('Components.receiptFooterContact', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  
</div>
<hr>
<div class="container-fluid mt-2 " style="border: 2px solid">       
  <div class="row" >
      <div class="col-4 text-center py-3">
          <h5 class="pt-3">
         <?php echo e($config['first_title_ar']); ?>

          </h5>
          <h5>
          <?php echo e($config['second_title_ar']); ?>

          </h5>
      </div>
      <div class="col-4 text-center py-3">
  
      
         
      <h5 class="pt-3"> وصل دفع</h5>
      <h5 class="pt-1">Cash Receipt Voucher </h5>
      </div>
      <div class="col-4 text-center py-3"> 
          <div style="max-width:125px;margin:0 auto;">
              <?php echo $__env->make('Components.logo', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
          </div>
  
      </div>
      </div>
      <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
        <div class="col-3"> 
        الرقم:
        <?php echo e($transactions_id); ?>

        </div>
        <div class="col-3">
   
        </div>
        <div class="col-3">
      
        </div>
        <div class="col-3">
           تاريخ:
        <?= $created ??'' ?>
        </div>
      </div>
  
      <div class="row p-2" style="font-size: 14px">
      <div class="col-12  p-2  pe-5"> 
      شركة / Company:
      <?php echo e($clientData['client']->name); ?>

      </div>
      <div class="col-12  p-2  pe-5"> 
        استلمت من :
        <?php echo e($clientData['client']->name); ?>

        </div>
  
        <div class="col-12  p-2  pe-5"> 
           مبلغ قدره :
           <?php echo e($Help->numberToWords($amount??0,($currency??'$'))); ?>

          </div>
          <div class="col-12  p-2  pe-5"> 
            الملاحظات:
            <?php echo e($description ??''); ?>

           </div>
          
          
        
    </div>
    <div class="row  text-center   "  style="font-size: 14px">
      <div class="col-1">
      </div>
      <div class="col-1 alert-primary border p-2"> 
       المبلغ:
      </div>
      <div class="col-1 alert-primary border p-2">
      <?php echo e($amount ?? 0); ?>

      </div>
      <div class="col-1 alert-primary border p-2">
        <?php echo e($currency ?? '$'); ?>

      </div>
      <div class="col-8 text-start ps-5">
        اسم وتوقيع المستلم
      </div>
    </div>
    <?php echo $__env->make('Components.receiptFooterContact', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
  </div>
<script>
    $(document).ready(function() {
        // Function to open the print dialog
        function openPrintDialog() {
            window.print();
        }
    
        // Call the function to open the print dialog
        openPrintDialog();
    });
    </script>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\shipping\resources\views/receiptPayment.blade.php ENDPATH**/ ?>