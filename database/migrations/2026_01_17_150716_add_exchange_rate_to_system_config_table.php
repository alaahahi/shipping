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
        Schema::table('system_config', function (Blueprint $table) {
            $table->decimal('usd_to_aed_rate', 10, 4)->default(3.6725)->after('default_price_p')->comment('سعر صرف الدولار مقابل الدرهم الإماراتي');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_config', function (Blueprint $table) {
            $table->dropColumn('usd_to_aed_rate');
        });
    }
};
