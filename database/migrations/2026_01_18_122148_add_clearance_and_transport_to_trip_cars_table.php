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
        // إضافة الحقول لجدول trip_companies (على مستوى الشحنة)
        Schema::table('trip_companies', function (Blueprint $table) {
            $table->decimal('clearance_per_car', 10, 2)->default(40.00)->after('shipping_price_aed')->comment('رسوم التخليص لكل سيارة بالدولار');
            $table->decimal('internal_transport_total', 10, 2)->nullable()->after('clearance_per_car')->comment('إجمالي رسوم النقل الداخلي بالدولار');
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
            $table->dropColumn(['clearance_per_car', 'internal_transport_total']);
        });
    }
};
