<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('system_config', function (Blueprint $table) {
            $table->json('contract_terms_2')->nullable()->after('contract_terms')->comment('شروط العقد لقالب 2 (JSON array)');
        });
    }

    public function down()
    {
        Schema::table('system_config', function (Blueprint $table) {
            $table->dropColumn('contract_terms_2');
        });
    }
};
