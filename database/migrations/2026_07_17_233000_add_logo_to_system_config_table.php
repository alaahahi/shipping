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

        // SQLite-safe: no after()/comment(); check column outside Blueprint.
        if (! Schema::hasColumn('system_config', 'logo')) {
            Schema::table('system_config', function (Blueprint $table) {
                $table->string('logo', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_config') || ! Schema::hasColumn('system_config', 'logo')) {
            return;
        }

        Schema::table('system_config', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
