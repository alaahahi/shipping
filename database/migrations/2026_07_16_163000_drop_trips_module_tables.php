<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'trip_expenses',
            'consignee_payments',
            'internal_transport_payments',
            'trip_cars',
            'trip_companies',
            'trips',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        $pageIds = DB::table('app_pages')
            ->whereIn('slug', ['trips', 'consignee_balances', 'company_balances'])
            ->pluck('id');

        if ($pageIds->isNotEmpty() && Schema::hasTable('app_page_user_type')) {
            DB::table('app_page_user_type')->whereIn('app_page_id', $pageIds)->delete();
        }

        DB::table('app_pages')
            ->whereIn('slug', ['trips', 'consignee_balances', 'company_balances'])
            ->delete();

        if (Schema::hasTable('user_type')) {
            $typeIds = DB::table('user_type')->where('name', 'shipping_trips_admin')->pluck('id');
            if ($typeIds->isNotEmpty() && Schema::hasTable('app_page_user_type')) {
                DB::table('app_page_user_type')->whereIn('user_type_id', $typeIds)->delete();
            }
            DB::table('user_type')->where('name', 'shipping_trips_admin')->delete();
        }
    }

    public function down(): void
    {
        // Irreversible cleanup of unused trips module.
    }
};
