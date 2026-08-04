<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('car')) {
            return;
        }

        Schema::table('car', function (Blueprint $table) {
            if (! Schema::hasColumn('car', 'container_open')) {
                $table->integer('container_open')->default(0)->after('coc_dolar');
            }
            if (! Schema::hasColumn('car', 'container_open_s')) {
                $table->integer('container_open_s')->default(0)->after('coc_dolar_s');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('car')) {
            return;
        }

        Schema::table('car', function (Blueprint $table) {
            if (Schema::hasColumn('car', 'container_open')) {
                $table->dropColumn('container_open');
            }
            if (Schema::hasColumn('car', 'container_open_s')) {
                $table->dropColumn('container_open_s');
            }
        });
    }
};
