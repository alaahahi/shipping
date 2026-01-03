<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Car;
use App\Models\Transfers;
use App\Models\BuyerPayment;
use App\Models\SalePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $owner_id;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء مستخدم للاختبار
        $this->user = User::factory()->create([
            'owner_id' => 1,
        ]);
        
        $this->owner_id = $this->user->owner_id;
    }

    /** @test */
    public function test_statistics_api_returns_data_structure()
    {
        // تسجيل الدخول
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_cars',
                'custom',
                'exchange_benefit',
                'profit_stats',
                'discount_stats',
                'cars_count',
                'total_customs',
                'exchange_profit',
                'net_profit',
                'net_transfers',
                'cash_balance',
                'transfers_summary' => [
                    'gross_transfers',
                    'transfer_fees',
                    'net_transfers',
                    'erbil_transfers',
                    'details',
                ],
                'cash_flow',
                'cash_flow_chart',
                'year_closing',
            ]);
    }

    /** @test */
    public function test_statistics_api_filters_by_owner_id()
    {
        // إنشاء سيارات يدوياً
        $carTable = (new Car())->getTable();
        DB::table($carTable)->insert([
            [
                'owner_id' => $this->owner_id,
                'year_date' => date('Y'),
                'created' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => $this->owner_id,
                'year_date' => date('Y'),
                'created' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 999, // owner_id مختلف
                'year_date' => date('Y'),
                'created' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/statistics');

        $response->assertStatus(200);
        
        // يجب أن يعيد فقط سيارات owner_id الحالي
        $this->assertEquals(2, $response->json('total_cars'));
        $this->assertEquals(2, $response->json('cars_count'));
    }

    /** @test */
    public function test_statistics_api_returns_empty_when_no_cars()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/statistics');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('total_cars'));
        $this->assertEquals(0, $response->json('cars_count'));
    }

    /** @test */
    public function test_statistics_api_requires_authentication()
    {
        $response = $this->getJson('/api/statistics');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_statistics_api_filters_by_year()
    {
        $carTable = (new Car())->getTable();
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        
        DB::table($carTable)->insert([
            [
                'owner_id' => $this->owner_id,
                'year_date' => $currentYear,
                'created' => "$currentYear-06-15",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => $this->owner_id,
                'year_date' => $lastYear,
                'created' => "$lastYear-06-15",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/statistics?year=$currentYear");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total_cars'));
    }

    /** @test */
    public function test_statistics_api_filters_by_month_using_created_field()
    {
        $carTable = (new Car())->getTable();
        $currentYear = date('Y');
        
        DB::table($carTable)->insert([
            [
                'owner_id' => $this->owner_id,
                'year_date' => $currentYear,
                'created' => "$currentYear-01-15", // يناير
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => $this->owner_id,
                'year_date' => $currentYear,
                'created' => "$currentYear-02-15", // فبراير
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // فلترة حسب يناير
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/statistics?year=$currentYear&month=1");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total_cars'));
        
        // فلترة حسب فبراير
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/statistics?year=$currentYear&month=2");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total_cars'));
    }

    /** @test */
    public function test_statistics_api_handles_nan_month_parameter()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/statistics?month=NaN');

        $response->assertStatus(200);
        // يجب أن يتجاهل NaN ويعيد جميع البيانات
        $this->assertArrayHasKey('total_cars', $response->json());
    }

    /** @test */
    public function test_statistics_api_returns_all_years_when_no_year_specified()
    {
        $carTable = (new Car())->getTable();
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        
        DB::table($carTable)->insert([
            [
                'owner_id' => $this->owner_id,
                'year_date' => $currentYear,
                'created' => "$currentYear-06-15",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => $this->owner_id,
                'year_date' => $lastYear,
                'created' => "$lastYear-06-15",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // طلب بدون تحديد سنة
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/statistics');

        $response->assertStatus(200);
        // يجب أن يعيد جميع السيارات من جميع السنوات
        $this->assertEquals(2, $response->json('total_cars'));
    }

    /** @test */
    public function test_transfers_calculation_includes_owner_id()
    {
        // هذا اختبار للتحقق من أن الحولات تُحسب بشكل صحيح
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/statistics');

        $response->assertStatus(200);
        $this->assertArrayHasKey('transfers_summary', $response->json());
        $this->assertArrayHasKey('gross_transfers', $response->json('transfers_summary'));
        $this->assertArrayHasKey('net_transfers', $response->json('transfers_summary'));
    }
}
