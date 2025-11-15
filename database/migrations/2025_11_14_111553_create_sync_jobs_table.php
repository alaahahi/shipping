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
        $connection = config('sync.local_connection', 'sqlite');
        
        Schema::connection($connection)->create('sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('model')->index();
            $table->string('uuid')->nullable()->index();
            $table->string('operation'); // 'create', 'update', 'delete' - using string for SQLite compatibility
            $table->text('payload'); // JSON stored as text for SQLite compatibility
            $table->integer('attempts')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('synced_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('sync.local_connection', 'sqlite');
        Schema::connection($connection)->dropIfExists('sync_jobs');
    }
};
