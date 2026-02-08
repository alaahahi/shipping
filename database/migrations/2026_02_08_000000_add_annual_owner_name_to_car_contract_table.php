<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * صاحب السنوية (نص) - يظهر في قالب العقد الثاني
     */
    public function up(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->string('annual_owner_name', 255)
                ->nullable()
                ->after('no')
                ->comment('صاحب السنوية (نص)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->dropColumn('annual_owner_name');
        });
    }
};

