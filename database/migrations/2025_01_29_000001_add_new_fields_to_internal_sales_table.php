<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('internal_sales', function (Blueprint $table) {
            $table->decimal('car_price', 15, 2)->default(0)->after('car_id')->comment('سعر السيارة');
            $table->decimal('shipping', 15, 2)->default(0)->after('car_price')->comment('النقل (total_s)');
            $table->decimal('additional_expenses', 15, 2)->default(0)->after('expenses')->comment('مصاريف إضافية');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_sales', function (Blueprint $table) {
            $table->dropColumn(['car_price', 'shipping', 'additional_expenses']);
        });
    }
};

