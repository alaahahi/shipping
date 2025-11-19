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
        if (!Schema::hasTable('licenses')) {
            Schema::create('licenses', function (Blueprint $table) {
                $table->id();
                $table->text('license_key')->nullable();
                $table->string('domain', 191)->unique(); // تقليل الطول لتجنب مشكلة المفتاح
                $table->string('fingerprint', 191)->index();
                $table->enum('type', ['trial', 'standard', 'premium'])->default('standard');
                $table->integer('max_installations')->default(1);
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            // إذا كان الجدول موجوداً، نضيف الأعمدة المفقودة فقط
            Schema::table('licenses', function (Blueprint $table) {
                if (!Schema::hasColumn('licenses', 'license_key')) {
                    $table->text('license_key')->nullable();
                }
                if (!Schema::hasColumn('licenses', 'domain')) {
                    $table->string('domain', 191)->unique();
                }
                if (!Schema::hasColumn('licenses', 'fingerprint')) {
                    $table->string('fingerprint', 191)->index();
                }
                if (!Schema::hasColumn('licenses', 'type')) {
                    $table->enum('type', ['trial', 'standard', 'premium'])->default('standard');
                }
                if (!Schema::hasColumn('licenses', 'max_installations')) {
                    $table->integer('max_installations')->default(1);
                }
                if (!Schema::hasColumn('licenses', 'activated_at')) {
                    $table->timestamp('activated_at')->nullable();
                }
                if (!Schema::hasColumn('licenses', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->index();
                }
                if (!Schema::hasColumn('licenses', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('licenses', 'last_verified_at')) {
                    $table->timestamp('last_verified_at')->nullable();
                }
                if (!Schema::hasColumn('licenses', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });
        }
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
