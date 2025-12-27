<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // حذف الجدول إذا كان موجوداً
        Schema::dropIfExists('buyer_payments');
        
        // إعادة إنشاء الجدول بدون foreign key constraint على owner_id
        Schema::create('buyer_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id'); // المشتري (user_id)
            $table->unsignedBigInteger('merchant_id'); // التاجر (user_id)
            $table->unsignedBigInteger('internal_sale_id')->nullable(); // رابط بالمبيعة (اختياري)
            $table->decimal('amount', 15, 2)->default(0); // مبلغ الدفعة
            $table->date('payment_date')->nullable(); // تاريخ الدفعة
            $table->text('note')->nullable(); // ملاحظات
            $table->unsignedBigInteger('owner_id'); // owner_id للتحكم (بدون foreign key)
            $table->unsignedBigInteger('created_by')->nullable(); // من أنشأ الدفعة
            $table->string('payment_id')->nullable()->unique(); // معرف فريد للدفعة (للمجموعات)
            $table->timestamps();
            $table->softDeletes(); // للحذف الناعم
            
            // Foreign keys (بدون owner_id)
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('merchant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('internal_sale_id')->references('id')->on('internal_sales')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['buyer_id', 'merchant_id']);
            $table->index(['internal_sale_id']);
            $table->index(['owner_id']);
            $table->index(['payment_id']);
            $table->index(['payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buyer_payments');
    }
};
