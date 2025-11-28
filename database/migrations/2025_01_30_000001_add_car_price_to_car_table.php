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
        Schema::table('car', function (Blueprint $table) {
            $table->decimal('car_price', 15, 2)->default(0)->after('total_s')->comment('سعر السيارة للمبيعات الداخلية');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('car', function (Blueprint $table) {
            $table->dropColumn('car_price');
        });
    }
};








