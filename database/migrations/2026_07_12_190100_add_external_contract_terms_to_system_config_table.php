<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_config', function (Blueprint $table) {
            $table->json('external_contract_terms')->nullable()->after('contract_terms_2');
            $table->json('external_contract_terms_2')->nullable()->after('external_contract_terms');
        });
    }

    public function down(): void
    {
        Schema::table('system_config', function (Blueprint $table) {
            $table->dropColumn(['external_contract_terms', 'external_contract_terms_2']);
        });
    }
};
