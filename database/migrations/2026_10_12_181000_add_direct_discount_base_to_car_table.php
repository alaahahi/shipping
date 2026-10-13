<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('car')) {
            return;
        }

        if (! Schema::hasColumn('car', 'direct_discount_base')) {
            Schema::table('car', function (Blueprint $table) {
                $table->unsignedInteger('direct_discount_base')->default(130000);
            });
        }

        DB::table('car')
            ->where(function ($query) {
                $query->whereNull('direct_discount_base')
                    ->orWhere('direct_discount_base', 0);
            })
            ->update(['direct_discount_base' => 130000]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('car')) {
            return;
        }

        if (Schema::hasColumn('car', 'direct_discount_base')) {
            Schema::table('car', function (Blueprint $table) {
                $table->dropColumn('direct_discount_base');
            });
        }
    }
};
