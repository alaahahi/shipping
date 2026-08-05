<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('external_car_payments')) {
            Schema::create('external_car_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('external_car_id');
                $table->unsignedBigInteger('owner_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->integer('amount_dollar')->default(0);
                $table->integer('amount_dinar')->default(0);
                $table->string('note', 2000)->nullable();
                $table->date('created')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('external_car_id');
                $table->index('owner_id');
                $table->foreign('external_car_id')
                    ->references('id')
                    ->on('external_cars')
                    ->cascadeOnDelete();
            });
        }

        // Seed one payment row from existing totals so history is not lost.
        if (Schema::hasTable('external_cars') && Schema::hasTable('external_car_payments')) {
            $cars = DB::table('external_cars')
                ->where(function ($q) {
                    $q->where('paid_dollar', '>', 0)->orWhere('paid_dinar', '>', 0);
                })
                ->get(['id', 'owner_id', 'user_id', 'paid_dollar', 'paid_dinar', 'note', 'date', 'created_at']);

            foreach ($cars as $car) {
                $exists = DB::table('external_car_payments')
                    ->where('external_car_id', $car->id)
                    ->exists();
                if ($exists) {
                    continue;
                }

                DB::table('external_car_payments')->insert([
                    'external_car_id' => $car->id,
                    'owner_id' => $car->owner_id,
                    'user_id' => $car->user_id,
                    'amount_dollar' => (int) ($car->paid_dollar ?? 0),
                    'amount_dinar' => (int) ($car->paid_dinar ?? 0),
                    'note' => $car->note ?: 'رصيد سابق',
                    'created' => $car->date ?? now()->toDateString(),
                    'created_at' => $car->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('external_car_payments');
    }
};
