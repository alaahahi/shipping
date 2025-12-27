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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_sale_id'); // بيع السيارة
            $table->decimal('amount', 15, 2)->default(0); // مبلغ الدفعة
            $table->date('payment_date')->nullable(); // تاريخ الدفعة
            $table->text('note')->nullable(); // ملاحظات
            $table->unsignedBigInteger('owner_id'); // owner_id للتحكم
            $table->timestamps();
            
            $table->foreign('car_sale_id')->references('id')->on('car_sales')->onDelete('cascade');
            $table->index(['car_sale_id', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};

