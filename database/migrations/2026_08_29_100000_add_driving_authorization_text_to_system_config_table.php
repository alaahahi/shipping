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

        if (! Schema::hasColumn('system_config', 'driving_authorization_text')) {
            Schema::table('system_config', function (Blueprint $table) {
                $table->longText('driving_authorization_text')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('system_config', 'driving_authorization_text')) {
            Schema::table('system_config', function (Blueprint $table) {
                $table->dropColumn('driving_authorization_text');
            });
        }
    }
};
