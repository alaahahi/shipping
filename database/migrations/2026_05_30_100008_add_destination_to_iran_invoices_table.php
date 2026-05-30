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
        Schema::table('iran_invoices', function (Blueprint $table) {
            // Destination used on the printed invoice (TRANSIT TO / Destination CIP)
            $table->string('destination')->nullable()->after('consignee_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('iran_invoices', function (Blueprint $table) {
            $table->dropColumn('destination');
        });
    }
};
