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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->text('license_key')->nullable(); // المفتاح المشفر
            $table->string('domain', 255)->nullable(); // Domain أو IP
            $table->string('fingerprint', 255)->nullable(); // Server Fingerprint
            $table->enum('type', ['trial', 'standard', 'premium'])->default('standard');
            $table->integer('max_installations')->default(1); // عدد التثبيتات المسموحة
            $table->timestamp('activated_at')->nullable(); // تاريخ التفعيل
            $table->timestamp('expires_at')->nullable(); // تاريخ الانتهاء (null = دائم)
            $table->boolean('is_active')->default(true); // حالة الترخيص
            $table->timestamp('last_verified_at')->nullable(); // آخر تحقق
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
            
            $table->unique('domain', 'unique_domain');
            $table->index('fingerprint', 'idx_fingerprint');
            $table->index('expires_at', 'idx_expires_at');
            $table->index('is_active', 'idx_is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('licenses');
    }
};
