<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id'); // السيارة المباعة
            $table->unsignedBigInteger('buyer_id'); // العميل المشتري (user_id)
            $table->decimal('sale_price', 15, 2)->default(0); // سعر البيع الكامل
            $table->decimal('paid_amount', 15, 2)->default(0); // إجمالي المبلغ المدفوع
            $table->decimal('remaining_amount', 15, 2)->default(0); // المبلغ المتبقي (سعر البيع - المدفوع)
            $table->text('note')->nullable(); // ملاحظات
            $table->date('sale_date')->nullable(); // تاريخ البيع
            $table->unsignedBigInteger('owner_id'); // owner_id للتحكم
            $table->timestamps();
            
            $table->foreign('car_id')->references('id')->on('car')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['buyer_id', 'owner_id']);
            $table->index('car_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_sales');
    }
};

