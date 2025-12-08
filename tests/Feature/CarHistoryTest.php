<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarHistory;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'owner_id' => 1,
            'type_id' => 1,
        ]);
    }

    /** @test */
    public function it_tracks_car_creation_in_history()
    {
        $this->actingAs($this->user);

        $carData = [
            'company_id' => 1,
            'name_id' => 1,
            'model_id' => 1,
            'color_id' => 1,
            'pin' => 'TEST123',
            'purchase_price' => 10000,
            'paid_amount' => 5000,
            'purchase_data' => '2024-01-01',
            'note' => 'Test car',
            'user_id' => $this->user->id,
        ];

        $response = $this->post('/api/addCars', $carData);

        $response->assertStatus(200);

        // Check if history was created
        $car = Car::where('pin', 'TEST123')->first();
        $this->assertNotNull($car);

        $history = CarHistory::where('car_id', $car->id)
            ->where('action', 'create')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals('تم إضافة سيارة جديدة', $history->description);
        $this->assertEquals($this->user->id, $history->user_id);
    }

    /** @test */
    public function it_tracks_car_updates_in_history()
    {
        $this->actingAs($this->user);

        // Create a car first
        $car = Car::factory()->create([
            'owner_id' => 1,
            'purchase_price' => 10000,
        ]);

        // Update the car
        $updateData = [
            'id' => $car->id,
            'purchase_price' => 12000, // Changed from 10000 to 12000
        ];

        $response = $this->post('/api/updateCarsP', $updateData);

        $response->assertStatus(200);

        // Check if history was created
        $history = CarHistory::where('car_id', $car->id)
            ->where('action', 'update')
            ->latest()
            ->first();

        $this->assertNotNull($history);
        $this->assertNotNull($history->changes);
        $this->assertEquals($this->user->id, $history->user_id);
    }

    /** @test */
    public function it_can_retrieve_car_history_via_api()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        // Create some history records
        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'create',
            'description' => 'تم إضافة سيارة جديدة',
            'user_id' => $this->user->id,
        ]);

        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'update',
            'description' => 'تم تحديث السيارة',
            'user_id' => $this->user->id,
            'changes' => ['purchase_price' => ['old' => 10000, 'new' => 12000]],
        ]);

        $response = $this->get("/api/car/{$car->id}/history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'car_id',
                        'action',
                        'description',
                        'user',
                        'created_at',
                        'changes'
                    ]
                ],
                'current_page',
                'last_page',
                'total'
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_filters_history_by_action_type()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'create',
            'description' => 'تم إضافة سيارة جديدة',
            'user_id' => $this->user->id,
        ]);

        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'update',
            'description' => 'تم تحديث السيارة',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/api/car/{$car->id}/history?action=create");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('create', $response->json('data.0.action'));
    }

    /** @test */
    public function it_filters_history_by_date_range()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'create',
            'description' => 'تم إضافة سيارة جديدة',
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(10),
        ]);

        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'update',
            'description' => 'تم تحديث السيارة',
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->get("/api/car/{$car->id}/history?date_from=" . now()->subDays(5)->format('Y-m-d'));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('update', $response->json('data.0.action'));
    }

    /** @test */
    public function it_can_compare_history_versions()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        $history1 = CarHistory::create([
            'car_id' => $car->id,
            'action' => 'update',
            'old_data' => ['purchase_price' => 10000, 'paid_amount' => 5000],
            'new_data' => ['purchase_price' => 12000, 'paid_amount' => 5000],
            'user_id' => $this->user->id,
        ]);

        $history2 = CarHistory::create([
            'car_id' => $car->id,
            'action' => 'update',
            'old_data' => ['purchase_price' => 12000, 'paid_amount' => 5000],
            'new_data' => ['purchase_price' => 12000, 'paid_amount' => 8000],
            'user_id' => $this->user->id,
        ]);

        $response = $this->post("/api/car/{$car->id}/history/compare", [
            'history_id_1' => $history1->id,
            'history_id_2' => $history2->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'history1',
                'history2',
                'comparison' => [
                    'added',
                    'removed',
                    'changed'
                ]
            ]);
    }

    /** @test */
    public function it_migrates_transactions_to_history_via_api()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        // Create a transaction related to the car
        $transaction = Transactions::create([
            'type' => 'out',
            'wallet_id' => 1,
            'description' => 'إضافة سيارة جديدة - سعر الشراء 10000',
            'amount' => 10000,
            'is_pay' => 1,
            'morphed_id' => $car->id,
            'morphed_type' => 'App\Models\Car',
            'user_added' => $this->user->id,
        ]);

        // Run migration
        $response = $this->post('/api/car-history/migrate-transactions', [
            'limit' => 10,
            'confirm_delete' => false, // Don't delete for testing
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'stats' => [
                    'processed',
                    'migrated',
                    'skipped',
                    'errors',
                    'deleted'
                ]
            ]);

        // Check if history was created
        $history = CarHistory::where('car_id', $car->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals('create', $history->action);

        // Check that transaction still exists (since confirm_delete was false)
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    /** @test */
    public function it_provides_history_statistics()
    {
        $this->actingAs($this->user);

        $car1 = Car::factory()->create(['owner_id' => 1]);
        $car2 = Car::factory()->create(['owner_id' => 1]);

        CarHistory::create(['car_id' => $car1->id, 'action' => 'create', 'user_id' => $this->user->id]);
        CarHistory::create(['car_id' => $car1->id, 'action' => 'update', 'user_id' => $this->user->id]);
        CarHistory::create(['car_id' => $car2->id, 'action' => 'create', 'user_id' => $this->user->id]);

        $response = $this->get('/api/car-history/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_changes',
                'changes_by_action',
                'changes_by_user',
                'recent_activity'
            ]);

        $this->assertEquals(3, $response->json('total_changes'));
    }

    /** @test */
    public function it_requires_authentication_for_history_apis()
    {
        $car = Car::factory()->create(['owner_id' => 1]);

        $response = $this->get("/api/car/{$car->id}/history");

        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function it_validates_migration_request_parameters()
    {
        $this->actingAs($this->user);

        $response = $this->post('/api/car-history/migrate-transactions', [
            'limit' => 0, // Invalid: must be >= 1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);
    }

    /** @test */
    public function it_can_cleanup_old_history_records()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        // Create old history record
        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'create',
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(100),
        ]);

        // Create recent history record
        CarHistory::create([
            'car_id' => $car->id,
            'action' => 'update',
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->post('/api/car-history/cleanup', [
            'older_than_days' => 50,
        ]);

        $response->assertStatus(200);

        // Old record should be deleted, recent should remain
        $this->assertDatabaseMissing('car_history', [
            'car_id' => $car->id,
            'action' => 'create',
        ]);

        $this->assertDatabaseHas('car_history', [
            'car_id' => $car->id,
            'action' => 'update',
        ]);
    }

    /** @test */
    public function it_tracks_bulk_car_updates()
    {
        $this->actingAs($this->user);

        $cars = Car::factory()->count(3)->create(['owner_id' => 1]);

        // Perform bulk update
        $response = $this->post('/api/bulkUpdateCarsP', [
            'cars' => $cars->pluck('id')->toArray(),
            'paid_amount' => 1000,
        ]);

        $response->assertStatus(200);

        // Check that history was created for each car
        foreach ($cars as $car) {
            $history = CarHistory::where('car_id', $car->id)
                ->where('action', 'update')
                ->latest()
                ->first();

            $this->assertNotNull($history);
        }
    }

    /** @test */
    public function it_handles_car_deletion_in_history()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        // Delete the car
        $response = $this->post('/api/DelCar', ['id' => $car->id]);

        $response->assertStatus(200);

        // Check that deletion was tracked
        $history = CarHistory::where('car_id', $car->id)
            ->where('action', 'delete')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals('تم حذف السيارة', $history->description);
    }

    /** @test */
    public function it_provides_paginated_history_results()
    {
        $this->actingAs($this->user);

        $car = Car::factory()->create(['owner_id' => 1]);

        // Create many history records
        for ($i = 0; $i < 25; $i++) {
            CarHistory::create([
                'car_id' => $car->id,
                'action' => 'update',
                'description' => "تحديث رقم {$i}",
                'user_id' => $this->user->id,
            ]);
        }

        $response = $this->get("/api/car/{$car->id}/history?per_page=10");

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('total'));
        $this->assertEquals(3, $response->json('last_page'));
    }
}

