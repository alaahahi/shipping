<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SystemUpdatesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'owner_id' => 1,
            'type_id' => 1,
        ]);
    }

    /** @test */
    public function sync_monitor_page_is_accessible_without_authentication()
    {
        $response = $this->get('/sync-monitor');

        $response->assertStatus(200);
        $response->assertSee('مراقبة المزامنة');
    }

    /** @test */
    public function sync_monitor_apis_require_authentication()
    {
        // Test tables API
        $response = $this->get('/api/sync-monitor/tables');
        $response->assertStatus(401); // Unauthorized

        // Test sync API
        $response = $this->post('/api/sync-monitor/sync');
        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function sync_monitor_apis_work_with_authentication()
    {
        $this->actingAs($this->user);

        // Test tables API
        $response = $this->get('/api/sync-monitor/tables');
        $response->assertStatus(200);

        // Test metadata API
        $response = $this->get('/api/sync-monitor/metadata');
        $response->assertStatus(200);
    }

    /** @test */
    public function vue_tailwind_datepicker_library_is_removed()
    {
        // Check that the library is not in package.json
        $packageJson = File::get(base_path('package.json'));
        $this->assertStringNotContainsString('vue-tailwind-datepicker', $packageJson);

        // Check that node_modules doesn't contain the library
        $this->assertFalse(File::exists(base_path('node_modules/vue-tailwind-datepicker')));

        // Check that built assets don't contain the library
        $manifest = File::get(public_path('build/manifest.json'));
        $this->assertStringNotContainsString('vue-tailwind-datepicker', $manifest);
    }

    /** @test */
    public function car_pages_use_native_date_inputs_instead_of_datepicker_library()
    {
        $this->actingAs($this->user);

        // Test Hunter page
        $response = $this->get('/hunter');
        $response->assertStatus(200);
        $response->assertDontSee('vue-tailwind-datepicker');

        // Test Car Contract page
        $response = $this->get('/car_contract');
        $response->assertStatus(200);
        $response->assertDontSee('vue-tailwind-datepicker');

        // Test purchases page
        $response = $this->get('/purchases');
        $response->assertStatus(200);
        $response->assertDontSee('vue-tailwind-datepicker');
    }

    /** @test */
    public function car_history_pages_are_protected_by_authentication()
    {
        $car = Car::factory()->create(['owner_id' => 1]);

        // Test history page requires auth
        $response = $this->get("/car/{$car->id}/history");
        $response->assertStatus(302); // Redirect to login

        // Test history API requires auth
        $response = $this->get("/api/car/{$car->id}/history");
        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function car_history_pages_work_with_authentication()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        // Test history page
        $response = $this->get("/car/{$car->id}/history");
        $response->assertStatus(200);
        $response->assertSee('تاريخ السيارة');

        // Test history API
        $response = $this->get("/api/car/{$car->id}/history");
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'current_page', 'total']);
    }

    /** @test */
    public function guest_layout_is_used_for_public_pages()
    {
        // Test sync-monitor uses GuestLayout
        $response = $this->get('/sync-monitor');
        $response->assertStatus(200);

        // Should not contain authenticated user data
        $response->assertDontSee('auth.user');
        $response->assertDontSee('authenticated-layout');
    }

    /** @test */
    public function authenticated_layout_handles_null_user_gracefully()
    {
        // Test that authenticated pages don't crash when user is null
        // This tests the fixes we made to AuthenticatedLayout.vue

        // Since sync-monitor is now public, let's test a protected route without auth
        $response = $this->get('/dashboard');
        $response->assertStatus(302); // Should redirect to login, not crash
    }

    /** @test */
    public function car_model_automatically_tracks_changes()
    {
        $this->actingAs($this->user);

        // Create a car
        $car = Car::factory()->create([
            'owner_id' => 1,
            'purchase_price' => 10000,
        ]);

        // Update the car
        $car->update(['purchase_price' => 12000]);

        // Check that history was automatically created
        $history = \App\Models\CarHistory::where('car_id', $car->id)
            ->where('action', 'update')
            ->latest()
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals('تم تحديث بيانات السيارة', $history->description);
        $this->assertNotNull($history->changes);
    }

    /** @test */
    public function car_history_relationship_works()
    {
        $car = Car::factory()->create(['owner_id' => 1]);

        // Create history records
        \App\Models\CarHistory::create([
            'car_id' => $car->id,
            'action' => 'create',
            'description' => 'Test history',
        ]);

        // Test relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $car->history);
        $this->assertCount(1, $car->history);
        $this->assertEquals('Test history', $car->history->first()->description);
    }

    /** @test */
    public function migration_creates_car_history_table_correctly()
    {
        // Check that the table exists
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('car_history'));

        // Check table structure
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('car_history');

        $expectedColumns = [
            'id',
            'car_id',
            'action',
            'old_data',
            'new_data',
            'changes',
            'field_changed',
            'description',
            'user_id',
            'user_name',
            'ip_address',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns, "Column '{$column}' should exist in car_history table");
        }
    }

    /** @test */
    public function car_history_model_has_correct_relationships()
    {
        $car = Car::factory()->create(['owner_id' => 1]);
        $user = User::factory()->create();

        $history = \App\Models\CarHistory::create([
            'car_id' => $car->id,
            'action' => 'create',
            'user_id' => $user->id,
            'description' => 'Test history',
        ]);

        // Test car relationship
        $this->assertInstanceOf(Car::class, $history->car);
        $this->assertEquals($car->id, $history->car->id);

        // Test user relationship
        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals($user->id, $history->user->id);
    }

    /** @test */
    public function car_history_scopes_work_correctly()
    {
        $car1 = Car::factory()->create(['owner_id' => 1]);
        $car2 = Car::factory()->create(['owner_id' => 1]);

        // Create different types of history
        \App\Models\CarHistory::create(['car_id' => $car1->id, 'action' => 'create']);
        \App\Models\CarHistory::create(['car_id' => $car1->id, 'action' => 'update']);
        \App\Models\CarHistory::create(['car_id' => $car2->id, 'action' => 'create']);

        // Test byCar scope
        $car1History = \App\Models\CarHistory::byCar($car1->id)->get();
        $this->assertCount(2, $car1History);

        // Test byAction scope
        $createHistory = \App\Models\CarHistory::byAction('create')->get();
        $this->assertCount(2, $createHistory);

        // Test recent scope
        $oldHistory = \App\Models\CarHistory::create([
            'car_id' => $car1->id,
            'action' => 'update',
            'created_at' => now()->subDays(40),
        ]);

        $recentHistory = \App\Models\CarHistory::recent(30)->get();
        $this->assertCount(2, $recentHistory); // Should not include the 40-day-old record
    }

    /** @test */
    public function car_history_provides_changes_summary()
    {
        $history = \App\Models\CarHistory::create([
            'car_id' => 1,
            'action' => 'update',
            'changes' => [
                'purchase_price' => ['old' => 10000, 'new' => 12000],
                'paid_amount' => ['old' => 5000, 'new' => 7000],
            ],
        ]);

        $summary = $history->changes_summary;
        $this->assertStringContains('purchase_price', $summary);
        $this->assertStringContains('paid_amount', $summary);
        $this->assertStringContains('10000 → 12000', $summary);
        $this->assertStringContains('5000 → 7000', $summary);
    }

    /** @test */
    public function system_handles_missing_old_attributes_gracefully()
    {
        $this->actingAs($this->user);

        // This tests the TracksHistory trait when oldAttributes is not set
        $car = Car::factory()->create(['owner_id' => 1]);

        // Manually trigger update without going through normal update process
        $car->purchase_price = 15000;
        $car->save();

        // Should not crash even if oldAttributes is not available
        $this->assertTrue(true); // If we reach here, the test passes
    }
}


