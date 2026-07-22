<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        DB::table('users')
            ->where('id', 588)
            ->update(['type_id' => 4]);
    }

    public function down(): void
    {
        // Previous type_id is unknown — no automatic rollback.
    }
};
