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
                $table->string('domain', 191)->unique();
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

