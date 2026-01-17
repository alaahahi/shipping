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
        Schema::table('trip_companies', function (Blueprint $table) {
            $table->decimal('shipping_price_per_car', 10, 2)->nullable()->after('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trip_companies', function (Blueprint $table) {
            $table->dropColumn('shipping_price_per_car');
        });
    }
};
