<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اسم منظم العقد - لكل مستخدم (يظهر في توقيع العقد حسب المسجل دخول)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('organizer_name', 255)->nullable()->after('name')
                ->comment('اسم منظم العقد - يظهر في توقيع العقد');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('organizer_name');
        });
    }
};
