<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_vin_search_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedInteger('search_year')->nullable()->index();
            $table->longText('vins_text');
            $table->unsignedInteger('vins_count')->default(0);
            $table->unsignedInteger('matched_count')->default(0);
            $table->unsignedInteger('ambiguous_count')->default(0);
            $table->unsignedInteger('missing_count')->default(0);
            $table->json('results_payload')->nullable();
            $table->json('missing_vins')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_vin_search_archives');
    }
};
