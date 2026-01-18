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
        Schema::create('internal_transport_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_company_id')->constrained('trip_companies')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->comment('مبلغ الدفعة بالدولار');
            $table->string('driver_name')->comment('اسم السائق');
            $table->string('cmr_number')->nullable()->comment('رقم CMR');
            $table->date('payment_date')->comment('تاريخ الدفعة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->unsignedBigInteger('owner_id');
            $table->timestamps();
            
            $table->index('trip_company_id');
            $table->index('owner_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_transport_payments');
    }
};
