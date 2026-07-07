<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_cars', function (Blueprint $table) {
            $table->string('vin', 32)->nullable()->after('dealer_name');
        });
    }

    public function down(): void
    {
        Schema::table('external_cars', function (Blueprint $table) {
            $table->dropColumn('vin');
        });
    }
};
