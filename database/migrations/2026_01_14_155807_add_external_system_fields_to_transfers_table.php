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
        Schema::table('transfers', function (Blueprint $table) {
            $table->boolean('is_external')->default(false)->after('is_archived');
            $table->unsignedBigInteger('external_system_id')->nullable()->after('is_external');
            $table->unsignedBigInteger('external_transfer_id')->nullable()->after('external_system_id');
            $table->string('external_system_domain')->nullable()->after('external_transfer_id');
            
            $table->foreign('external_system_id')->references('id')->on('connected_systems')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropForeign(['external_system_id']);
            $table->dropColumn(['is_external', 'external_system_id', 'external_transfer_id', 'external_system_domain']);
        });
    }
};
