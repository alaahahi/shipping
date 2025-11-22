<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sync_queue')) {
            return;
        }
        
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 100)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->enum('action', ['insert', 'update', 'delete'])->index();
            $table->json('data')->nullable(); // البيانات الكاملة للسجل (للتحديث والإدراج)
            $table->json('changes')->nullable(); // فقط الحقول التي تغيرت (للتحديث)
            $table->enum('status', ['pending', 'syncing', 'synced', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            // فهرس مركب للبحث السريع
            $table->index(['table_name', 'record_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sync_queue');
    }
};

