<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['sqlite'], true)) {
            DB::statement('CREATE INDEX IF NOT EXISTS transactions_wallet_id_id_index ON transactions (wallet_id, id)');
            DB::statement('CREATE INDEX IF NOT EXISTS transactions_wallet_id_type_index ON transactions (wallet_id, type)');
            DB::statement('CREATE INDEX IF NOT EXISTS transactions_morphed_lookup_index ON transactions (morphed_type, type, morphed_id)');

            return;
        }

        // MySQL / MariaDB — ignore duplicate-index errors on re-run.
        try {
            Schema::table('transactions', function ($table) {
                $table->index(['wallet_id', 'id'], 'transactions_wallet_id_id_index');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('transactions', function ($table) {
                $table->index(['wallet_id', 'type'], 'transactions_wallet_id_type_index');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('transactions', function ($table) {
                $table->index(['morphed_type', 'type', 'morphed_id'], 'transactions_morphed_lookup_index');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS transactions_wallet_id_id_index');
            DB::statement('DROP INDEX IF EXISTS transactions_wallet_id_type_index');
            DB::statement('DROP INDEX IF EXISTS transactions_morphed_lookup_index');

            return;
        }

        try {
            Schema::table('transactions', function ($table) {
                $table->dropIndex('transactions_wallet_id_id_index');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('transactions', function ($table) {
                $table->dropIndex('transactions_wallet_id_type_index');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('transactions', function ($table) {
                $table->dropIndex('transactions_morphed_lookup_index');
            });
        } catch (\Throwable $e) {
        }
    }
};
