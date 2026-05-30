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
        Schema::create('iran_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->unsignedBigInteger('consignee_id')->nullable();
            // Snapshot of carrier/consignee names at issue time (kept English on print)
            $table->string('carrier_name')->nullable();
            $table->string('consignee_name')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('invoice_no');
            $table->index('invoice_date');
            $table->index('is_archived');
            $table->index('carrier_id');
            $table->index('consignee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iran_invoices');
    }
};
