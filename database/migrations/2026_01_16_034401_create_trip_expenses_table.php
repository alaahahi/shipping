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
        Schema::create('trip_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->enum('expense_type', ['shipping', 'fuel', 'port', 'customs', 'other'])->default('other');
            $table->decimal('amount', 15, 2);
            $table->enum('currency', ['dinar', 'dollar'])->default('dollar');
            $table->text('note')->nullable();
            $table->date('date')->useCurrent();
            $table->unsignedBigInteger('owner_id');
            $table->timestamps();
            
            $table->index('trip_id');
            $table->index('expense_type');
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
        Schema::dropIfExists('trip_expenses');
    }
};
