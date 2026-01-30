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
            $table->unsignedTinyInteger('contract_template')->default(1)->after('contract_terms')->comment('1=قالب العقد الأول، 2=قالب العقد الثاني');
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
            $table->dropColumn('contract_template');
        });
    }
};
