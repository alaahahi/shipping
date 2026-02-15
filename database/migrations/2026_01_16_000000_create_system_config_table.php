<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إنشاء جدول إعدادات النظام (مطلوب للعمل المحلي مع SQLite)
     */
    public function up(): void
    {
        if (Schema::hasTable('system_config')) {
            return;
        }

        Schema::create('system_config', function (Blueprint $table) {
            $table->id();
            $table->string('first_title_ar', 255)->nullable();
            $table->string('first_title_kr', 255)->nullable();
            $table->string('second_title_ar', 255)->nullable();
            $table->string('second_title_kr', 255)->nullable();
            $table->string('third_title_ar', 255)->nullable();
            $table->string('third_title_kr', 255)->nullable();
            $table->json('default_price_s')->nullable();
            $table->json('default_price_p')->nullable();
            $table->decimal('usd_to_aed_rate', 10, 4)->default(3.6725);
            $table->decimal('usd_to_dinar_rate', 10, 2)->default(150.00);
            $table->json('contract_terms')->nullable();
            $table->json('contract_terms_2')->nullable();
            $table->unsignedTinyInteger('contract_template')->default(1);
            $table->string('contract_currency', 10)->default('usd');
            $table->string('primary_color', 20)->default('#c00');
        });

        // إدراج سجل افتراضي
        DB::table('system_config')->insert([
            'first_title_ar' => null,
            'first_title_kr' => null,
            'second_title_ar' => null,
            'second_title_kr' => null,
            'third_title_ar' => null,
            'third_title_kr' => null,
            'default_price_s' => null,
            'default_price_p' => null,
            'usd_to_aed_rate' => 3.6725,
            'usd_to_dinar_rate' => 150.00,
            'contract_terms' => null,
            'contract_terms_2' => null,
            'contract_template' => 1,
            'contract_currency' => 'usd',
            'primary_color' => '#c00',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_config');
    }
};
