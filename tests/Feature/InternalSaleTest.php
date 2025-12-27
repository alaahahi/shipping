<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Car;
use App\Models\InternalSale;
use Illuminate\Support\Facades\Auth;

class InternalSaleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $client;
    protected $car;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء مستخدم ومالك
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'type_id' => 1,
            'owner_id' => 1,
        ]);
        
        // إنشاء عميل
        $this->client = User::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'password' => bcrypt('password'),
            'type_id' => 2,
            'owner_id' => $this->user->owner_id,
            'has_internal_sales' => true,
        ]);
        
        // إنشاء سيارة
        $this->car = Car::create([
            'client_id' => $this->client->id,
            'car_type' => 'Toyota',
            'year' => 2020,
            'vin' => 'TESTVIN123456789',
            'total_s' => 10000,
            'paid' => 0,
            'discount' => 0,
            'results' => 0,
            'date' => now()->format('Y-m-d'),
        ]);
        
        Auth::login($this->user);
    }

    /** @test */
    public function test_toggle_internal_sales_status()
    {
        $response = $this->postJson('/api/toggleInternalSales', [
            'client_id' => $this->client->id,
            'has_internal_sales' => true,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($this->client->fresh()->has_internal_sales);
    }

    /** @test */
    public function test_get_unsold_cars()
    {
        $response = $this->getJson("/api/getUnsoldCars?client_id={$this->client->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cars' => [
                '*' => ['id', 'car_type', 'year', 'vin', 'car_number']
            ]
        ]);
    }

    /** @test */
    public function test_get_internal_sales()
    {
        // إنشاء مبيعة داخلية
        InternalSale::create([
            'client_id' => $this->client->id,
            'car_id' => $this->car->id,
            'sale_price' => 10000,
            'paid_amount' => 5000,
            'expenses' => 500,
        ]);

        $response = $this->getJson("/api/getInternalSales?client_id={$this->client->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'sales',
            'totals' => [
                'total_sales',
                'total_paid',
                'total_expenses',
                'total_profit',
            ],
            'has_internal_sales',
        ]);
    }

    /** @test */
    public function test_add_internal_sale()
    {
        $response = $this->postJson('/api/addInternalSale', [
            'client_id' => $this->client->id,
            'car_id' => $this->car->id,
            'sale_price' => 10000,
            'paid_amount' => 5000,
            'expenses' => 500,
            'note' => 'Test sale',
            'sale_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('internal_sales', [
            'client_id' => $this->client->id,
            'car_id' => $this->car->id,
            'sale_price' => 10000,
            'paid_amount' => 5000,
            'expenses' => 500,
        ]);
    }

    /** @test */
    public function test_update_internal_sale()
    {
        $sale = InternalSale::create([
            'client_id' => $this->client->id,
            'car_id' => $this->car->id,
            'sale_price' => 10000,
            'paid_amount' => 0,
            'expenses' => 0,
        ]);

        $response = $this->postJson('/api/updateInternalSale', [
            'id' => $sale->id,
            'sale_price' => 12000,
            'paid_amount' => 6000,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('internal_sales', [
            'id' => $sale->id,
            'sale_price' => 12000,
            'paid_amount' => 6000,
        ]);
    }

    /** @test */
    public function test_delete_internal_sale()
    {
        $sale = InternalSale::create([
            'client_id' => $this->client->id,
            'car_id' => $this->car->id,
            'sale_price' => 10000,
            'paid_amount' => 5000,
            'expenses' => 500,
        ]);

        $response = $this->postJson('/api/deleteInternalSale', [
            'id' => $sale->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('internal_sales', [
            'id' => $sale->id,
        ]);
    }

    /** @test */
    public function test_unsold_cars_excludes_sold_cars()
    {
        // إنشاء مبيعة داخلية
        InternalSale::create([
            'client_id' => $this->client->id,
            'car_id' => $this->car->id,
            'sale_price' => 10000,
            'paid_amount' => 5000,
            'expenses' => 500,
        ]);

        // إنشاء سيارة أخرى غير مباعة
        $unsoldCar = Car::create([
            'client_id' => $this->client->id,
            'car_type' => 'Honda',
            'year' => 2021,
            'vin' => 'UNSOLDVIN123456789',
            'total_s' => 15000,
            'paid' => 0,
            'discount' => 0,
            'results' => 0,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson("/api/getUnsoldCars?client_id={$this->client->id}");

        $response->assertStatus(200);
        $cars = $response->json('cars');
        
        // يجب أن تحتوي على السيارة غير المباعة فقط
        $carIds = collect($cars)->pluck('id')->toArray();
        $this->assertContains($unsoldCar->id, $carIds);
        $this->assertNotContains($this->car->id, $carIds);
    }

    /** @test */
    public function test_profit_calculation()
    {
        $sale = InternalSale::create([
            'client_id' => $this->client->id,
            'car_id' => $this->car->id,
            'sale_price' => 10000,
            'paid_amount' => 5000,
            'expenses' => 500,
        ]);

        // الربح = سعر البيع - المبلغ المدفوع - المصاريف
        $expectedProfit = 10000 - 5000 - 500; // 4500
        
        $sale->refresh(); // إعادة تحميل من قاعدة البيانات
        $this->assertEquals($expectedProfit, $sale->profit);
    }
}

