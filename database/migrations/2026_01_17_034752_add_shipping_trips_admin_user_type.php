<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the user type already exists
        $exists = DB::table('user_type')->where('name', 'shipping_trips_admin')->exists();
        
        if (!$exists) {
            // Insert the new user type
            DB::table('user_type')->insert([
                'name' => 'shipping_trips_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the user type (optional - you may want to keep it)
        DB::table('user_type')->where('name', 'shipping_trips_admin')->delete();
    }
};
