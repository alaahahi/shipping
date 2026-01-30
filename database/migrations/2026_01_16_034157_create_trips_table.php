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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->date('sailing_date');
            $table->string('captain')->nullable();
            $table->string('pol'); // Port of Loading
            $table->string('pod'); // Port of Discharge
            $table->string('flag')->nullable();
            $table->string('ship_name');
            $table->string('voy_no')->nullable(); // Voyage Number
            $table->decimal('total_expenses', 15, 2)->default(0); // مجموع المصاريف
            $table->enum('expenses_currency', ['dinar', 'dollar'])->default('dollar');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->timestamps();
            
            $table->index('sailing_date');
            $table->index('ship_name');
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
        Schema::dropIfExists('trips');
    }
};
