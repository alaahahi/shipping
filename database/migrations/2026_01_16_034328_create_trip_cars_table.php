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
        Schema::create('trip_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('trip_company_id')->constrained('trip_companies')->onDelete('cascade');
            $table->foreignId('car_id')->nullable()->constrained('car')->onDelete('set null');
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('shipper_name');
            $table->string('description')->nullable();
            $table->string('chassis_no')->nullable();
            $table->string('consignee_name');
            $table->foreignId('consignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->timestamps();
            
            $table->index('chassis_no');
            $table->index('consignee_id');
            $table->index('trip_id');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_cars');
    }
};
