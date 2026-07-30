<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_config')) {
            return;
        }

        Schema::table('system_config', function (Blueprint $table) {
            if (! Schema::hasColumn('system_config', 'wa_enabled')) {
                $table->boolean('wa_enabled')->default(false);
            }
            if (! Schema::hasColumn('system_config', 'wa_tenant')) {
                $table->string('wa_tenant', 100)->nullable();
            }
            if (! Schema::hasColumn('system_config', 'wa_base_url')) {
                $table->string('wa_base_url', 255)->nullable();
            }
            if (! Schema::hasColumn('system_config', 'wa_created_by')) {
                $table->string('wa_created_by', 100)->nullable();
            }
            if (! Schema::hasColumn('system_config', 'wa_notify_client_debt')) {
                $table->boolean('wa_notify_client_debt')->default(true);
            }
            if (! Schema::hasColumn('system_config', 'wa_notify_payment_receipt')) {
                $table->boolean('wa_notify_payment_receipt')->default(true);
            }
            if (! Schema::hasColumn('system_config', 'wa_notify_car_added')) {
                $table->boolean('wa_notify_car_added')->default(true);
            }
            if (! Schema::hasColumn('system_config', 'wa_msg_client_debt')) {
                $table->text('wa_msg_client_debt')->nullable();
            }
            if (! Schema::hasColumn('system_config', 'wa_msg_payment_receipt')) {
                $table->text('wa_msg_payment_receipt')->nullable();
            }
            if (! Schema::hasColumn('system_config', 'wa_msg_car_added')) {
                $table->text('wa_msg_car_added')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_config')) {
            return;
        }

        $columns = [
            'wa_enabled',
            'wa_tenant',
            'wa_base_url',
            'wa_created_by',
            'wa_notify_client_debt',
            'wa_notify_payment_receipt',
            'wa_notify_car_added',
            'wa_msg_client_debt',
            'wa_msg_payment_receipt',
            'wa_msg_car_added',
        ];

        Schema::table('system_config', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('system_config', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
