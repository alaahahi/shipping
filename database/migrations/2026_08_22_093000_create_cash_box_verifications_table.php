<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cash_box_verifications')) {
            return;
        }

        Schema::create('cash_box_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedBigInteger('wallet_id')->index();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->decimal('ledger_balance_at_confirm', 18, 2)->default(0);
            $table->decimal('ledger_balance_dinar_at_confirm', 18, 2)->default(0);
            $table->string('note', 1000)->nullable();
            $table->unsignedBigInteger('verified_by')->nullable()->index();
            $table->timestamp('verified_at')->nullable()->index();
            $table->timestamps();

            $table->index(['owner_id', 'wallet_id', 'verified_at']);
            $table->index(['wallet_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_box_verifications');
    }
};
