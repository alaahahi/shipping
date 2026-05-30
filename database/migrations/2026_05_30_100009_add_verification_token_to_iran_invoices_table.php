<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::table('iran_invoices', function (Blueprint $table) {
            $table->uuid('verification_token')->nullable()->unique()->after('invoice_no');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('iran_invoices', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });
    }
};
