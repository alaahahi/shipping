<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('car_contract')) {
            return;
        }

        if (! Schema::hasColumn('car_contract', 'verification_token')) {
            Schema::table('car_contract', function (Blueprint $table) {
                $table->string('verification_token', 64)->nullable()->unique()->after('status');
            });
        }

        if (! Schema::hasColumn('car_contract', 'seller_id_number')) {
            Schema::table('car_contract', function (Blueprint $table) {
                $table->string('seller_id_number', 100)->nullable()->after('address_seller');
            });
        }

        if (! Schema::hasColumn('car_contract', 'buyer_id_number')) {
            Schema::table('car_contract', function (Blueprint $table) {
                $table->string('buyer_id_number', 100)->nullable()->after('address_buyer');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('car_contract')) {
            return;
        }

        if (Schema::hasColumn('car_contract', 'verification_token')) {
            Schema::table('car_contract', function (Blueprint $table) {
                $table->dropUnique(['verification_token']);
                $table->dropColumn('verification_token');
            });
        }

        if (Schema::hasColumn('car_contract', 'seller_id_number')) {
            Schema::table('car_contract', function (Blueprint $table) {
                $table->dropColumn('seller_id_number');
            });
        }

        if (Schema::hasColumn('car_contract', 'buyer_id_number')) {
            Schema::table('car_contract', function (Blueprint $table) {
                $table->dropColumn('buyer_id_number');
            });
        }
    }
};
