<?php

namespace Tests\Feature;

use App\Models\IranInvoice;
use App\Models\IranInvoiceCar;
use App\Models\IranInvoiceTransfer;
use App\Models\User;
use App\Services\IranPageAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IranPageAccessTest extends TestCase
{
    protected User $admin;
    protected User $staff;
    protected User $otherStaff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpIranSchema();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'type_id' => 1,
            'owner_id' => 1,
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff One',
            'type_id' => 2,
            'owner_id' => 1,
        ]);

        $this->otherStaff = User::factory()->create([
            'name' => 'Staff Two',
            'type_id' => 2,
            'owner_id' => 1,
        ]);
    }

    protected function setUpIranSchema(): void
    {
        Schema::dropIfExists('iran_invoice_attachments');
        Schema::dropIfExists('iran_invoice_transfers');
        Schema::dropIfExists('iran_invoice_cars');
        Schema::dropIfExists('iran_invoice_items');
        Schema::dropIfExists('iran_invoices');
        Schema::dropIfExists('iran_invoice_carriers');
        Schema::dropIfExists('iran_invoice_consignees');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->boolean('is_band')->default(0);
            $table->boolean('has_wallet_tags')->default(0);
            $table->boolean('show_in_dashboard')->default(0);
            $table->boolean('has_internal_sales')->default(0);
            $table->integer('percentage')->default(0);
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        Schema::create('iran_invoice_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('iran_invoice_consignees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('iran_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->unsignedBigInteger('consignee_id')->nullable();
            $table->string('carrier_name')->nullable();
            $table->string('consignee_name')->nullable();
            $table->string('destination')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('iran_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('car_id')->nullable();
            $table->string('chassis_no')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('weight')->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('iran_invoice_cars', function (Blueprint $table) {
            $table->id();
            $table->string('chassis_no')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('iran_invoice_transfers', function (Blueprint $table) {
            $table->id();
            $table->date('transfer_date')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency')->nullable();
            $table->string('reference_no')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('iran_invoice_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('file_name');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function guest_cannot_unlock_iran_page_without_token()
    {
        $this->postJson('/api/iran/unlock', ['pin' => '134679'])
            ->assertStatus(403);
    }

    /** @test */
    public function guest_with_token_can_open_page_and_list_after_pin()
    {
        $token = config('iran.page_token');

        $this->get('/iran?token=' . $token)
            ->assertOk();

        $this->withHeaders(['X-Iran-Token' => $token])
            ->postJson('/api/iran/unlock', ['pin' => '134679'])
            ->assertOk()
            ->assertJsonPath('unlocked', true);

        IranInvoice::create([
            'invoice_no' => 'IR-GUEST',
            'owner_id' => 1,
            'created_by' => null,
        ]);

        $list = $this->withHeader('X-Iran-Token', $token)
            ->getJson('/api/iran-invoices')
            ->assertOk()
            ->json('data');

        $this->assertNotEmpty($list);
        $this->assertSame('IR-GUEST', $list[0]['invoice_no']);
    }

    /** @test */
    public function guest_without_token_is_redirected_to_login()
    {
        $this->get('/iran')
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function staff_cannot_list_invoices_before_pin()
    {
        $this->actingAs($this->staff)
            ->getJson('/api/iran-invoices')
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'يجب إدخال رمز PIN لصفحة إيران']);
    }

    /** @test */
    public function wrong_pin_is_rejected_in_arabic()
    {
        $this->actingAs($this->staff)
            ->postJson('/api/iran/unlock', ['pin' => '000000'])
            ->assertStatus(422)
            ->assertJsonFragment(['رمز PIN غير صحيح']);
    }

    /** @test */
    public function staff_can_unlock_with_pin_and_list_without_header_token()
    {
        $this->actingAs($this->staff)
            ->postJson('/api/iran/unlock', ['pin' => '134679'])
            ->assertOk()
            ->assertJsonPath('unlocked', true)
            ->assertJsonMissing(['token']);

        $this->actingAs($this->staff)
            ->getJson('/api/iran-invoices')
            ->assertOk();
    }

    /** @test */
    public function admin_skips_pin_and_needs_no_header_token()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/iran-invoices')
            ->assertOk();
    }

    /** @test */
    public function staff_can_create_invoice_and_created_by_is_recorded()
    {
        $this->actingAs($this->staff)
            ->postJson('/api/iran/unlock', ['pin' => '134679'])
            ->assertOk();

        $this->actingAs($this->staff)
            ->postJson('/api/iran-invoices', [
                'invoice_date' => '2026-11-12',
                'items' => [
                    ['chassis_no' => 'TESTCHASSIS1', 'make' => 'Toyota'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('iran_invoices', [
            'owner_id' => 1,
            'created_by' => $this->staff->id,
        ]);
    }

    /** @test */
    public function pin_user_sees_only_own_invoices_admin_sees_all()
    {
        IranInvoice::create([
            'invoice_no' => 'IR-OWN',
            'owner_id' => 1,
            'created_by' => $this->staff->id,
        ]);
        IranInvoice::create([
            'invoice_no' => 'IR-OTHER',
            'owner_id' => 1,
            'created_by' => $this->otherStaff->id,
        ]);

        $this->actingAs($this->staff)
            ->postJson('/api/iran/unlock', ['pin' => '134679'])
            ->assertOk();

        $staffList = $this->actingAs($this->staff)
            ->getJson('/api/iran-invoices')
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $staffList);
        $this->assertSame('IR-OWN', $staffList[0]['invoice_no']);
        $this->assertSame('Staff One', $staffList[0]['created_by_name'] ?? $staffList[0]['creator']['name'] ?? null);

        $adminList = $this->actingAs($this->admin)
            ->getJson('/api/iran-invoices')
            ->assertOk()
            ->json('data');

        $this->assertCount(2, $adminList);
    }

    /** @test */
    public function staff_cannot_delete_invoice()
    {
        $invoice = IranInvoice::create([
            'invoice_no' => 'IR-TEST-0001',
            'owner_id' => 1,
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->postJson('/api/iran/unlock', ['pin' => '134679'])
            ->assertOk();

        $this->actingAs($this->staff)
            ->postJson("/api/iran-invoices/{$invoice->id}/delete")
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'الحذف متاح للمدير فقط']);

        $this->assertNull($invoice->fresh()->deleted_at);
    }

    /** @test */
    public function admin_can_soft_delete_invoice()
    {
        $invoice = IranInvoice::create([
            'invoice_no' => 'IR-TEST-0002',
            'owner_id' => 1,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->postJson("/api/iran-invoices/{$invoice->id}/delete")
            ->assertOk();

        $this->assertSoftDeleted('iran_invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function staff_cannot_delete_car_or_transfer()
    {
        $car = IranInvoiceCar::create([
            'chassis_no' => 'CAR-1',
            'owner_id' => 1,
            'created_by' => $this->staff->id,
        ]);

        $transfer = IranInvoiceTransfer::create([
            'amount' => 10,
            'owner_id' => 1,
            'created_by' => $this->staff->id,
        ]);

        $this->actingAs($this->staff)
            ->postJson('/api/iran/unlock', ['pin' => '134679'])
            ->assertOk();

        $this->actingAs($this->staff)
            ->postJson("/api/iran-invoice-cars/{$car->id}/delete")
            ->assertStatus(403);

        $this->actingAs($this->staff)
            ->postJson("/api/iran-invoice-transfers/{$transfer->id}/delete")
            ->assertStatus(403);
    }

    /** @test */
    public function page_props_do_not_include_the_pin_or_token()
    {
        $request = \Illuminate\Http\Request::create('/iran', 'GET');
        $request->setUserResolver(fn () => $this->staff);
        $request->setLaravelSession($this->app['session.store']);

        $props = app(IranPageAccessService::class)->pageProps($request);

        $this->assertArrayNotHasKey('iran_page_pin', $props);
        $this->assertArrayNotHasKey('iran_page_token', $props);
        $this->assertFalse($props['iran_unlocked']);
        $this->assertFalse($props['iran_can_delete']);
        $this->assertFalse($props['iran_is_admin']);
    }
}
