<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_contract_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_contract_id')->index();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('received_by')->nullable();
            $table->text('note')->nullable();
            $table->date('created')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_contract_installments');
    }
};
