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
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('cost_per_car_aed', 10, 2)->nullable()->after('total_expenses')->comment('سعر الكلفة لكل سيارة بالدرهم الإماراتي');
            $table->decimal('captain_commission_aed', 10, 2)->nullable()->after('cost_per_car_aed')->comment('عمولة القبطان لكل سيارة بالدرهم الإماراتي');
            $table->decimal('purchase_price_aed', 10, 2)->nullable()->after('captain_commission_aed')->comment('سعر الشراء لكل سيارة بالدرهم الإماراتي (الكلفة - العمولة)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['cost_per_car_aed', 'captain_commission_aed', 'purchase_price_aed']);
        });
    }
};
