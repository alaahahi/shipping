<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('route_name')->nullable()->index();
            $table->string('path')->nullable();
            $table->string('label');
            $table->string('nav_group')->default('main');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('app_page_user_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_page_id');
            $table->unsignedBigInteger('user_type_id');
            $table->timestamps();

            $table->unique(['app_page_id', 'user_type_id']);
            $table->foreign('app_page_id')->references('id')->on('app_pages')->cascadeOnDelete();
            $table->foreign('user_type_id')->references('id')->on('user_type')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_page_user_type');
        Schema::dropIfExists('app_pages');
    }
};
