<?php

namespace Tests\Feature;

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
}
