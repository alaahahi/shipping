<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اسم منظم العقد - يظهر في توقيع العقد (منفصل عن user_id المنشئ)
     */
    public function up(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->string('organizer_name', 255)->nullable()->after('user_id')
                ->comment('اسم منظم العقد / كاتب العقد في التوقيع');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_contract', function (Blueprint $table) {
            $table->dropColumn('organizer_name');
        });
    }
};
