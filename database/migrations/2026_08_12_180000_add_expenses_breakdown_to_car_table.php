<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car', function (Blueprint $table) {
            if (! Schema::hasColumn('car', 'expenses_breakdown')) {
                $table->json('expenses_breakdown')->nullable()->after('expenses_s');
            }
        });
    }

    public function down(): void
    {
        Schema::table('car', function (Blueprint $table) {
            if (Schema::hasColumn('car', 'expenses_breakdown')) {
                $table->dropColumn('expenses_breakdown');
            }
        });
    }
};
