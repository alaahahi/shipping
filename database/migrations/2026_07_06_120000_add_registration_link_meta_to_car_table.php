<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car', function (Blueprint $table) {
            $table->unsignedInteger('registration_exchange_rate')->nullable()->after('car_have_expenses');
            $table->unsignedInteger('registration_pre_expenses')->nullable()->after('registration_exchange_rate');
            $table->unsignedInteger('registration_pre_expenses_s')->nullable()->after('registration_pre_expenses');
            $table->boolean('registration_sync_sales')->default(false)->after('registration_pre_expenses_s');
        });
    }

    public function down(): void
    {
        Schema::table('car', function (Blueprint $table) {
            $table->dropColumn([
                'registration_exchange_rate',
                'registration_pre_expenses',
                'registration_pre_expenses_s',
                'registration_sync_sales',
            ]);
        });
    }
};
