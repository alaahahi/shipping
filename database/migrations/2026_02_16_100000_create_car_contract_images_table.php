<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_contract_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_contract_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('car_contract_id')->references('id')->on('car_contract')->onDelete('cascade');
            $table->index('car_contract_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_contract_images');
    }
};
