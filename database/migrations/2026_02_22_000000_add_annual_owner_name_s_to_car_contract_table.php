<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->string('annual_owner_name_s', 255)->nullable()->after('vin_s');
        });
    }

    public function down(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->dropColumn('annual_owner_name_s');
        });
    }
};
