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
        Schema::create('sync_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 100)->unique(); // 100 حرف كافي لأسماء الجداول
            $table->string('direction', 10)->default('down'); // 'down' = MySQL->SQLite, 'up' = SQLite->MySQL
            $table->bigInteger('last_synced_id')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_updated_at')->nullable(); // آخر updated_at تم مزامنته
            $table->integer('total_synced')->default(0);
            $table->timestamps();
            
            $table->index(['table_name', 'direction']);
            $table->index('last_synced_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_metadata');
    }
};

