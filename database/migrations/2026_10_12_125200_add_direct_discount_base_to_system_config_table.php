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

        if (! Schema::hasColumn('system_config', 'direct_discount_base')) {
            Schema::table('system_config', function (Blueprint $table) {
                $table->unsignedInteger('direct_discount_base')->default(130000);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_config')) {
            return;
        }

        if (Schema::hasColumn('system_config', 'direct_discount_base')) {
            Schema::table('system_config', function (Blueprint $table) {
                $table->dropColumn('direct_discount_base');
            });
        }
    }
};
