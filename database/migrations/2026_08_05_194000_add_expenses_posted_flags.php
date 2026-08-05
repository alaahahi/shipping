<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('car') && ! Schema::hasColumn('car', 'expenses_posted')) {
            Schema::table('car', function (Blueprint $table) {
                $table->boolean('expenses_posted')->default(false)->after('car_have_expenses');
                $table->timestamp('expenses_posted_at')->nullable()->after('expenses_posted');
            });
        }

        if (Schema::hasTable('external_cars') && ! Schema::hasColumn('external_cars', 'expenses_posted')) {
            Schema::table('external_cars', function (Blueprint $table) {
                $table->boolean('expenses_posted')->default(false)->after('paid_dinar');
                $table->timestamp('expenses_posted_at')->nullable()->after('expenses_posted');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('car') && Schema::hasColumn('car', 'expenses_posted')) {
            Schema::table('car', function (Blueprint $table) {
                $table->dropColumn(['expenses_posted', 'expenses_posted_at']);
            });
        }

        if (Schema::hasTable('external_cars') && Schema::hasColumn('external_cars', 'expenses_posted')) {
            Schema::table('external_cars', function (Blueprint $table) {
                $table->dropColumn(['expenses_posted', 'expenses_posted_at']);
            });
        }
    }
};
