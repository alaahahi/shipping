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
        Schema::create('iran_invoice_cars', function (Blueprint $table) {
            $table->id();
            $table->string('chassis_no')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('weight')->nullable();
            $table->text('notes')->nullable();
            // Optional in-module references (no FK to legacy system tables)
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->unsignedBigInteger('consignee_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('chassis_no');
            $table->index('carrier_id');
            $table->index('consignee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iran_invoice_cars');
    }
};
