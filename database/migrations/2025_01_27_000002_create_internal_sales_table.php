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
        Schema::create('internal_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); // user_id للزبون
            $table->unsignedBigInteger('car_id'); // id السيارة
            $table->decimal('sale_price', 15, 2)->default(0); // سعر البيع
            $table->decimal('paid_amount', 15, 2)->default(0); // المبلغ المدفوع
            $table->decimal('expenses', 15, 2)->default(0); // المصاريف
            $table->decimal('profit', 15, 2)->default(0); // الربح (حساب تلقائي: sale_price - paid_amount - expenses)
            $table->text('note')->nullable(); // ملاحظات
            $table->date('sale_date')->nullable(); // تاريخ البيع
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('car_id')->references('id')->on('car')->onDelete('cascade');
            $table->index(['client_id', 'car_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_sales');
    }
};

