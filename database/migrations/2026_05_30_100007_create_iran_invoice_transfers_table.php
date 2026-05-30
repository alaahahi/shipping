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
        Schema::create('iran_invoice_transfers', function (Blueprint $table) {
            $table->id();
            $table->date('transfer_date')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('reference_no')->nullable();
            $table->string('from_text')->nullable();
            $table->string('to_text')->nullable();
            $table->text('notes')->nullable();
            // Optional link to an invoice (no FK to legacy transfers table)
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('transfer_date');
            $table->index('invoice_id');
            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iran_invoice_transfers');
    }
};
