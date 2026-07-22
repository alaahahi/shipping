<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accounting_migration_logs')) {
            return;
        }

        Schema::create('accounting_migration_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->string('legacy_key', 64);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->decimal('balance_dollar_before', 16, 2)->default(0);
            $table->decimal('balance_dinar_before', 16, 2)->default(0);
            $table->unsignedInteger('transactions_count')->default(0);
            $table->unsignedInteger('expenses_count')->default(0);
            $table->string('display_name', 191)->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('migrated_by')->nullable();
            $table->boolean('dry_run')->default(false);
            $table->timestamps();

            $table->index(['owner_id', 'legacy_key']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_migration_logs');
    }
};
