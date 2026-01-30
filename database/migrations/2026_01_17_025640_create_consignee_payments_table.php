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
        Schema::create('consignee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('currency', ['dollar', 'dinar'])->default('dollar');
            $table->text('notes')->nullable();
            $table->date('payment_date');
            $table->string('receipt_number')->nullable()->unique();
            $table->unsignedBigInteger('owner_id');
            $table->timestamps();
            
            $table->index('consignee_id');
            $table->index('trip_id');
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
        Schema::dropIfExists('consignee_payments');
    }
};
