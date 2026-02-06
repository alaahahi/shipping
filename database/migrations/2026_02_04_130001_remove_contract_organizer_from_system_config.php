<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إزالة منظم العقد من system_config - أصبح في جدول users
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE system_config DROP COLUMN contract_organizer_name');
        } elseif ($driver === 'sqlite') {
            // SQLite لا يدعم DROP COLUMN مباشرة - نترك العمود إن وُجد
            // أو نعيد إنشاء الجدول - للبساطة نتخطى
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE system_config ADD contract_organizer_name VARCHAR(255) NULL AFTER primary_color');
        }
    }
};
