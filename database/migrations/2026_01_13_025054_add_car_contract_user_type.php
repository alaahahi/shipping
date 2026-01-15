<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the user type already exists
        $exists = DB::table('user_type')->where('name', 'car_contract_user')->exists();
        
        if (!$exists) {
            // Insert the new user type
            DB::table('user_type')->insert([
                'name' => 'car_contract_user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the user type (optional - you may want to keep it)
        DB::table('user_type')->where('name', 'car_contract_user')->delete();
    }
};
