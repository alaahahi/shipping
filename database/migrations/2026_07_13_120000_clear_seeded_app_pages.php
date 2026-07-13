<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_pages')) {
            return;
        }

        DB::table('app_page_user_type')->delete();
        DB::table('app_pages')->delete();
    }

    public function down(): void
    {
        // لا استرجاع تلقائي
    }
};
