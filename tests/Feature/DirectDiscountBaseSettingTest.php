<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectDiscountBaseSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_read_and_update_direct_discount_base()
    {
        $admin = User::factory()->create([
            'owner_id' => 1,
            'type_id' => 1,
        ]);

        $this->actingAs($admin, 'sanctum');

        $this->getJson('/api/system-config')
            ->assertOk()
            ->assertJsonPath('direct_discount_base', 130000);

        $this->putJson('/api/system-config', [
            'direct_discount_base' => 140000,
        ])
            ->assertOk()
            ->assertJsonPath('config.direct_discount_base', 140000);

        $this->assertSame(140000, SystemConfig::directDiscountBase());
    }

    /** @test */
    public function non_admin_cannot_change_direct_discount_base()
    {
        SystemConfig::query()->update(['direct_discount_base' => 130000]);

        $sales = User::factory()->create([
            'owner_id' => 1,
            'type_id' => 5,
        ]);

        $this->actingAs($sales, 'sanctum');

        $this->putJson('/api/system-config', [
            'direct_discount_base' => 999999,
            'usd_to_dinar_rate' => 150,
        ])->assertOk();

        $this->assertSame(130000, SystemConfig::directDiscountBase());
    }

    /** @test */
    public function restricted_user_cannot_update_system_config()
    {
        $user = User::factory()->create([
            'owner_id' => 1,
            'type_id' => 10,
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/system-config', [
                'direct_discount_base' => 140000,
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function old_cars_keep_130000_and_new_cars_snapshot_the_setting()
    {
        $admin = User::factory()->create([
            'owner_id' => 1,
            'type_id' => 1,
        ]);
        $client = User::factory()->create([
            'owner_id' => 1,
            'type_id' => 2,
        ]);

        $this->actingAs($admin, 'sanctum');

        $oldCar = Car::factory()->create([
            'client_id' => $client->id,
            'owner_id' => 1,
            'direct_discount_base' => 130000,
            'vin' => 'OLDVIN00000000001',
        ]);

        $this->putJson('/api/system-config', [
            'direct_discount_base' => 140000,
        ])->assertOk();

        $this->assertSame(130000, (int) $oldCar->fresh()->direct_discount_base);
        $this->assertSame(140000, SystemConfig::directDiscountBase());

        $response = $this->postJson('/api/addCars', [
            'client_id' => $client->id,
            'vin' => 'NEWVIN00000000001',
            'car_type' => 'Toyota',
            'dinar' => 1400000,
            'dolar_price' => 140000,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertOk();

        $newCar = Car::where('vin', 'NEWVIN00000000001')->first();
        $this->assertNotNull($newCar);
        $this->assertSame(140000, (int) $newCar->direct_discount_base);

        $this->putJson('/api/system-config', [
            'direct_discount_base' => 150000,
        ])->assertOk();

        $this->assertSame(130000, (int) $oldCar->fresh()->direct_discount_base);
        $this->assertSame(140000, (int) $newCar->fresh()->direct_discount_base);
        $this->assertSame(150000, SystemConfig::directDiscountBase());

        $laterCar = Car::factory()->create([
            'client_id' => $client->id,
            'owner_id' => 1,
            'vin' => 'LATERVIN000000001',
        ]);

        $this->assertSame(150000, (int) $laterCar->direct_discount_base);
        $this->assertSame(130000, $oldCar->resolveDirectDiscountBase());
        $this->assertSame(140000, $newCar->fresh()->resolveDirectDiscountBase());
    }
}
