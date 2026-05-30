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
        Schema::create('iran_invoice_attachments', function (Blueprint $table) {
            $table->id();
            // Polymorphic owner: IranInvoice or IranInvoiceCar
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('file_name');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id'], 'iran_attachable_index');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iran_invoice_attachments');
    }
};
