<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_cars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('dealer_name');
            $table->string('car_type');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('car_color')->nullable();
            $table->string('car_number');
            $table->unsignedInteger('paid_dollar')->default(0);
            $table->unsignedInteger('paid_dinar')->default(0);
            $table->text('note')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_cars');
    }
};
