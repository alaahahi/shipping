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
        Schema::create('car_damage_reports', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name')->nullable();
            $table->string('cmr_number')->nullable();
            $table->integer('cars_count')->default(0);
            $table->decimal('total_damage', 15, 2)->default(0);
            $table->json('cars_info')->nullable(); // Array of cars with vin, color, model, damage
            $table->date('created')->nullable();
            $table->integer('year_date')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('owner_id');
            $table->index('user_id');
            $table->index('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_damage_reports');
    }
};
