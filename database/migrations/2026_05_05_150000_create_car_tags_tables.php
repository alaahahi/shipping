<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->string('name');
            $table->timestamps();

            $table->unique(['owner_id', 'name']);
            $table->index('owner_id');
        });

        Schema::create('car_tag_pivot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->unique(['car_id', 'tag_id']);
            $table->index('car_id');
            $table->index('tag_id');

            $table->foreign('car_id')->references('id')->on('car')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('car_tags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_tag_pivot');
        Schema::dropIfExists('car_tags');
    }
};
