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
        Schema::table('internal_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_sales', 'payments')) {
                $table->json('payments')->nullable()->after('paid_amount')->comment('سجل الدفعات الفردية');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internal_sales', function (Blueprint $table) {
            if (Schema::hasColumn('internal_sales', 'payments')) {
                $table->dropColumn('payments');
            }
        });
    }
};
