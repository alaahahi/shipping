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
    public function up(): void
    {
        Schema::create('car_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->enum('action', ['create', 'update', 'delete', 'restore']);
            $table->json('old_data')->nullable(); // البيانات القديمة
            $table->json('new_data')->nullable(); // البيانات الجديدة
            $table->json('changes')->nullable(); // التغييرات المحددة
            $table->string('field_changed')->nullable(); // الحقل الذي تغير (اختياري)
            $table->text('description')->nullable(); // وصف التغيير
            $table->unsignedBigInteger('user_id')->nullable(); // المستخدم الذي قام بالتغيير
            $table->string('user_name')->nullable(); // اسم المستخدم للأرشفة
            $table->string('ip_address')->nullable(); // IP المستخدم
            $table->timestamps();

            // Indexes
            $table->index(['car_id', 'created_at']);
            $table->index('action');
            $table->index('user_id');
            $table->index('field_changed');

            // Foreign keys
            $table->foreign('car_id')->references('id')->on('car')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_history');
    }
};
