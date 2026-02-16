<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sync_queue')) {
            Schema::create('sync_queue', function (Blueprint $table) {
                $table->id();
                $table->string('table_name');
                $table->unsignedBigInteger('record_id');
                $table->string('action'); // insert, update, delete
                $table->json('data')->nullable();
                $table->json('changes')->nullable();
                $table->string('status')->default('pending'); // pending, syncing, synced, failed
                $table->unsignedInteger('retry_count')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
    }
};
