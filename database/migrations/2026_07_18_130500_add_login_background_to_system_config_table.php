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

        if (! Schema::hasColumn('system_config', 'login_background')) {
            Schema::table('system_config', function (Blueprint $table) {
                $table->string('login_background', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_config') || ! Schema::hasColumn('system_config', 'login_background')) {
            return;
        }

        Schema::table('system_config', function (Blueprint $table) {
            $table->dropColumn('login_background');
        });
    }
};
