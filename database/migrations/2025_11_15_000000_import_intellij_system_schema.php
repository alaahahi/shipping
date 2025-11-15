<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    protected string $schemaConnection;

    public function __construct()
    {
        $this->schemaConnection = config('sync.local_connection', config('database.default'));
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = $this->schemaConnection;
        $driver = config("database.connections.{$connection}.driver", 'mysql');

        $sqlFile = database_path($driver === 'sqlite'
            ? 'sql/intellij_system_sqlite.sql'
            : 'sql/intellij_system_schema.sql'
        );

        if (! File::exists($sqlFile)) {
            throw new RuntimeException("Schema file not found: {$sqlFile}");
        }

        $sql = File::get($sqlFile);
        DB::connection($connection)->unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = $this->schemaConnection;
        $tables = [
            'transactions_images',
            'transactions_contract',
            'transactions',
            'warehouse',
            'wallets',
            'user_type',
            'users',
            'transfers',
            'results',
            'system_config',
            'personal_access_tokens',
            'password_resets',
            'owner',
            'oauth_personal_access_clients',
            'oauth_clients',
            'name',
            'migrations',
            'massage',
            'info',
            'failed_jobs',
            'expenses_type',
            'expenses',
            'exit_car',
            'driving',
            'contract_img',
            'contract',
            'car_model',
            'car_images',
            'car_expenses',
            'car_contract',
            'car',
        ];

        Schema::connection($connection)->disableForeignKeyConstraints();

        foreach ($tables as $table) {
            Schema::connection($connection)->dropIfExists($table);
        }

        Schema::connection($connection)->enableForeignKeyConstraints();
    }
};

