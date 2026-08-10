<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('car_expenses') && ! Schema::hasColumn('car_expenses', 'is_posted')) {
            Schema::table('car_expenses', function (Blueprint $table) {
                $table->boolean('is_posted')->default(false)->after('amount_dollar');
            });

            if (Schema::hasTable('car') && Schema::hasColumn('car', 'expenses_posted')) {
                $postedCarIds = DB::table('car')->where('expenses_posted', 1)->pluck('id');
                if ($postedCarIds->isNotEmpty()) {
                    DB::table('car_expenses')->whereIn('car_id', $postedCarIds)->update(['is_posted' => 1]);
                }
            }
        }

        if (Schema::hasTable('external_car_payments') && ! Schema::hasColumn('external_car_payments', 'is_posted')) {
            Schema::table('external_car_payments', function (Blueprint $table) {
                $table->boolean('is_posted')->default(false)->after('amount_dinar');
            });

            if (Schema::hasTable('external_cars') && Schema::hasColumn('external_cars', 'expenses_posted')) {
                $postedIds = DB::table('external_cars')->where('expenses_posted', 1)->pluck('id');
                if ($postedIds->isNotEmpty()) {
                    DB::table('external_car_payments')->whereIn('external_car_id', $postedIds)->update(['is_posted' => 1]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('car_expenses') && Schema::hasColumn('car_expenses', 'is_posted')) {
            Schema::table('car_expenses', function (Blueprint $table) {
                $table->dropColumn('is_posted');
            });
        }

        if (Schema::hasTable('external_car_payments') && Schema::hasColumn('external_car_payments', 'is_posted')) {
            Schema::table('external_car_payments', function (Blueprint $table) {
                $table->dropColumn('is_posted');
            });
        }
    }
};
