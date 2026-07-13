<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->string('contract_type', 20)->default('company')->index()->after('status');
        });

        DB::table('car_contract')->whereNull('contract_type')->update(['contract_type' => 'company']);
    }

    public function down(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->dropColumn('contract_type');
        });
    }
};
