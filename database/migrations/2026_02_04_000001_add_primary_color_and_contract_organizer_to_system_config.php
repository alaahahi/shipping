<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_config', function (Blueprint $table) {
            $table->string('primary_color', 20)->default('#c00')->after('contract_currency')
                ->comment('اللون الأساسي للعقود والواجهات');
            $table->string('contract_organizer_name', 255)->nullable()->after('primary_color')
                ->comment('اسم منظم العقد');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_config', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'contract_organizer_name']);
        });
    }
};
