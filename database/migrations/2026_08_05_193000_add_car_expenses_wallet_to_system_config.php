<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_config')) {
            return;
        }

        Schema::table('system_config', function (Blueprint $table) {
            if (! Schema::hasColumn('system_config', 'car_expenses_wallet_user_id')) {
                $table->unsignedBigInteger('car_expenses_wallet_user_id')->nullable()->after('wa_msg_car_added');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_config')) {
            return;
        }

        Schema::table('system_config', function (Blueprint $table) {
            if (Schema::hasColumn('system_config', 'car_expenses_wallet_user_id')) {
                $table->dropColumn('car_expenses_wallet_user_id');
            }
        });
    }
};
