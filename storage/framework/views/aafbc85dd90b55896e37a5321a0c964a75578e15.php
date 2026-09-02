<!DOCTYPE html>
<html>
<head>
    <title><?php echo e(config('app.company_name')); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    .report-logo {
      max-width: 125px;
      height: auto;
    }
    @page {
      size: landscape; /* Set page orientation to landscape */

      /* this affects the margin in the printer settings */
      margin: 15px;
      margin-top: 60px;
    }
    </style>
</head>
<?php
    $totalPaid = 0;
    $totalPaidDinar = 0;
    $totalDibt = 0;
    $totalDinarDibt = 0;
    $count=0

?>

<?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $count = $count+1;
        $totalPaid += $transaction->tex_seller_paid + $transaction->tex_buyer_paid;
        $totalPaidDinar += $transaction->tex_seller_dinar_paid + $transaction->tex_buyer_dinar_paid;
        $totalDibt += ($transaction->tex_seller + $transaction->tex_buyer) - ($transaction->tex_seller_paid + $transaction->tex_buyer_paid);
        $totalDinarDibt += ($transaction->tex_seller_dinar + $transaction->tex_buyer_dinar) - ($transaction->tex_seller_dinar_paid + $transaction->tex_buyer_dinar_paid);

    ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<body style="direction: rtl;">
<div class="container-fluid">       
<div class="row">
    <div class="col-4 text-center py-3">
        <h5>
       <?php echo e($config['first_title_ar']); ?>

        </h5>
        <h5>
        <?php echo e($config['second_title_ar']); ?>

        </h5>
    </div>
    <div class="col-4 text-center py-3">

    
       
    <h5 class="pt-3">  جميع عقود البيع </h5>
    </div>
    <div class="col-4 text-center py-3"> 
        <?php echo $__env->make('Components.logo', ['logoMaxWidth' => 125], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    </div>
    </div>
    <div class="row p-2 text-center border-top border-bottom" style="font-size: 14px">
    <div class="col"> 
    العدد:
    <?php echo e($count); ?>

    </div>
    <div class="col">

    </div>
    <?php if($_GET['from'] ??''): ?>
    <div class="col">
    من تاريخ:
    <?= $_GET['from'] ??'' ?>
    </div>
    <?php endif; ?>
    <?php if($_GET['to'] ??''): ?>
    <div class="col">
        حتى تاريخ:
    <?= $_GET['to'] ??'' ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="row p-2 text-center border-bottom alert-primary "  style="font-size: 14px">
    <div class="col-6"> 
    مجموع المدفوع بالدولار:
    <?php echo e($totalPaid); ?>

    </div>
    <?php if($totalPaidDinar ??''): ?>
    <div class="col-6">
      مجموع المدفوع بالدينار العراقي:
      <?php echo e($totalPaidDinar??0); ?>

    </div>
    <?php endif; ?>
    <div class="col-6"> 
    مجموع الدين بالدولار:
    <?php echo e($totalDibt??0); ?>

    </div>
    <?php if($totalDinarDibt??0): ?>
    <div class="col-6">
      مجموع الدين بالدينار العراقي:
      <?php echo e($totalDinarDibt??0); ?>

    </div>
    <?php endif; ?>
  </div>
  <div class="row text-center py-2">
    <table class="table table-sm table-striped table-bordered" style="font-size: 12px">
        <thead>
          <tr>
            <th>رقم</th>
            <th scope="col">البائع</th>
            <th scope="col">المشتري</th>
            <th scope="col">هاتف</th>
            <th scope="col">السيارة</th>
            <th scope="col">السعر</th>
            <th scope="col">موديل</th>

            <th scope="col">المدفوع بالدولار</th>
            <?php if($totalPaidDinar ??''): ?>
            <th scope="col">المدفوع بالدينار</th>
            <?php endif; ?>
            <th scope="col">الدين بالدولار</th>
            <?php if($totalDinarDibt??0): ?>
            <th scope="col">الدين بالدينار</th>
            <?php endif; ?>
            <th scope="col">ملاحظة ستاف</th>
            <th scope="col">تاريخ</th>

          </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($key+1); ?></td>
                <td><?php echo e($data->name_seller); ?></td>
                <td><?php echo e($data->name_buyer); ?></td>
                <td><?php echo e($data->phone_buyer); ?></td>
                <td><?php echo e($data->car_name); ?></td>
                <td><?php echo e($data->car_price); ?></td>
                <td><?php echo e($data->modal); ?></td>

                
                <td>
                  <?php echo e($data->tex_seller_paid + $data->tex_buyer_paid); ?>

                </td>
                <?php if($totalPaidDinar ??''): ?>
                <td>  
                  <?php echo e($data->tex_seller_dinar_paid + $data->tex_buyer_dinar_paid); ?>

                </td>
                <?php endif; ?>
                <td><?php echo e(($data->tex_seller + $data->tex_buyer)- ($data->tex_seller_paid + $data->tex_buyer_paid)); ?></td>
                <?php if($totalDinarDibt??0): ?>
                <td><?php echo e(($data->tex_seller_dinar + $data->tex_buyer_dinar)  -  ($data->tex_seller_dinar_paid + $data->tex_buyer_dinar_paid)); ?></td>
                <?php endif; ?>
                <td><?php echo e($data->system_note); ?></td>
                <td><?php echo e($data->created); ?></td>

            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>  
  </div>
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
<?php /**PATH C:\xampp\htdocs\shipping\resources\views/Contract/reportContractTotal.blade.php ENDPATH**/ ?>