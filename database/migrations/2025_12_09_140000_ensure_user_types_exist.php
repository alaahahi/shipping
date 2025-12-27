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
        // إضافة السجلات الأساسية إذا لم تكن موجودة
        $userTypes = [
            ['name' => 'admin'],
            ['name' => 'client'],
            ['name' => 'account'],
            ['name' => 'selesKirkuk'],
        ];

        foreach ($userTypes as $userType) {
            $exists = DB::table('user_type')->where('name', $userType['name'])->exists();
            
            if (!$exists) {
                DB::table('user_type')->insert([
                    'name' => $userType['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا نحذف السجلات في حالة rollback
        // لأنها قد تكون مستخدمة في بيانات أخرى
    }
};
