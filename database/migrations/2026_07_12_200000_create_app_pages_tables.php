<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('route_name')->nullable()->index();
            $table->string('path')->nullable();
            $table->string('label');
            $table->string('nav_group')->default('main'); // main | more
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('app_page_user_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_page_id');
            $table->unsignedBigInteger('user_type_id');
            $table->timestamps();

            $table->unique(['app_page_id', 'user_type_id']);
            $table->foreign('app_page_id')->references('id')->on('app_pages')->cascadeOnDelete();
            $table->foreign('user_type_id')->references('id')->on('user_type')->cascadeOnDelete();
        });

        $this->seedPages();
    }

    public function down(): void
    {
        Schema::dropIfExists('app_page_user_type');
        Schema::dropIfExists('app_pages');
    }

    protected function resolveTypeIds(array $names = [], array $fallbackIds = []): array
    {
        $ids = [];

        if ($names) {
            $ids = array_merge($ids, DB::table('user_type')->whereIn('name', $names)->pluck('id')->all());
        }

        if ($fallbackIds) {
            $ids = array_merge($ids, DB::table('user_type')->whereIn('id', $fallbackIds)->pluck('id')->all());
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    protected function attachTypes(int $pageId, array $typeIds): void
    {
        foreach ($typeIds as $typeId) {
            DB::table('app_page_user_type')->insert([
                'app_page_id' => $pageId,
                'user_type_id' => $typeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function seedPages(): void
    {
        $adminTypes = $this->resolveTypeIds(['admin'], [1, 6]);
        $contractTypes = $this->resolveTypeIds(['car_contract', 'car_contract_user'], [8, 10]);
        $contractAdminTypes = $this->resolveTypeIds(['car_contract'], [8]);
        $shippingTypes = $this->resolveTypeIds(['shipping_trips_admin'], [15]);
        $allTypes = DB::table('user_type')->pluck('id')->map(fn ($id) => (int) $id)->all();

        $pages = [
            ['slug' => 'dashboard', 'route_name' => 'dashboard', 'path' => '/dashboard', 'label' => 'الرئيسية', 'nav_group' => 'main', 'sort_order' => 1, 'types' => $allTypes],
            ['slug' => 'purchases', 'route_name' => 'purchases', 'path' => '/purchases', 'label' => 'المشتريات', 'nav_group' => 'main', 'sort_order' => 2, 'types' => $adminTypes],
            ['slug' => 'sales', 'route_name' => 'sales', 'path' => '/sales', 'label' => 'المبيعات', 'nav_group' => 'main', 'sort_order' => 3, 'types' => $adminTypes],
            ['slug' => 'clients', 'route_name' => 'clients', 'path' => '/clients', 'label' => 'الزبائن', 'nav_group' => 'main', 'sort_order' => 4, 'types' => $adminTypes],
            ['slug' => 'accounting', 'route_name' => 'accounting', 'path' => '/accounting', 'label' => 'المحاسبة', 'nav_group' => 'main', 'sort_order' => 5, 'types' => $adminTypes],
            ['slug' => 'annual_information', 'route_name' => 'annual_information', 'path' => '/annual_information', 'label' => 'معلومات السنوية', 'nav_group' => 'main', 'sort_order' => 6, 'types' => $adminTypes],
            ['slug' => 'car_expenses', 'route_name' => 'car_expenses', 'path' => '/car_expenses', 'label' => 'تسجيل السيارات', 'nav_group' => 'main', 'sort_order' => 7, 'types' => $adminTypes],
            ['slug' => 'contract', 'route_name' => 'contract', 'path' => '/contract', 'label' => 'عقد جديد', 'nav_group' => 'main', 'sort_order' => 8, 'types' => $contractTypes],
            ['slug' => 'car_contract', 'route_name' => 'car_contract', 'path' => '/car_contract', 'label' => 'عقود البيع', 'nav_group' => 'main', 'sort_order' => 9, 'types' => $contractTypes],
            ['slug' => 'external_car_contract', 'route_name' => 'external_car_contract', 'path' => '/external_car_contract', 'label' => 'عقود خارجية', 'nav_group' => 'main', 'sort_order' => 10, 'types' => $contractTypes],
            ['slug' => 'contract_installments', 'route_name' => 'contract_installments', 'path' => '/contract_installments', 'label' => 'أقساط السيارات', 'nav_group' => 'main', 'sort_order' => 11, 'types' => $contractTypes],
            ['slug' => 'company_treasury', 'route_name' => 'company_treasury', 'path' => '/company_treasury', 'label' => 'قاصة الشركة', 'nav_group' => 'main', 'sort_order' => 12, 'types' => $contractAdminTypes],
            ['slug' => 'contract_account', 'route_name' => 'contract_account', 'path' => '/contract_account', 'label' => 'محاسبة عقود', 'nav_group' => 'main', 'sort_order' => 13, 'types' => $contractAdminTypes],
            ['slug' => 'trips', 'route_name' => 'trips', 'path' => '/trips', 'label' => 'الرحلات', 'nav_group' => 'main', 'sort_order' => 14, 'types' => $shippingTypes],
            ['slug' => 'consignee_balances', 'route_name' => 'consigneeBalances.index', 'path' => '/consignee-balances', 'label' => 'أرصدة الزبائن', 'nav_group' => 'main', 'sort_order' => 15, 'types' => $shippingTypes],
            ['slug' => 'company_balances', 'route_name' => 'companyBalances.index', 'path' => '/company-balances', 'label' => 'حسابات الشركات', 'nav_group' => 'main', 'sort_order' => 16, 'types' => $shippingTypes],
            ['slug' => 'dashboard_statistics', 'route_name' => 'dashboard.statistics', 'path' => '/dashboard/admin', 'label' => 'احصائات', 'nav_group' => 'more', 'sort_order' => 20, 'types' => $adminTypes],
            ['slug' => 'sync_monitor', 'route_name' => 'sync.monitor', 'path' => '/sync-monitor', 'label' => 'المزامنة', 'nav_group' => 'more', 'sort_order' => 21, 'types' => $adminTypes],
            ['slug' => 'online_contracts', 'route_name' => 'online_contracts', 'path' => '/online_contracts', 'label' => 'العقود الالكترونية', 'nav_group' => 'more', 'sort_order' => 22, 'types' => $adminTypes],
            ['slug' => 'car_check', 'route_name' => 'car_check', 'path' => '/car_check', 'label' => 'مراجعة السيارات', 'nav_group' => 'more', 'sort_order' => 23, 'types' => $adminTypes],
            ['slug' => 'damage_report', 'route_name' => 'damage_report.index', 'path' => '/damage_report', 'label' => 'تقارير الضرر', 'nav_group' => 'more', 'sort_order' => 24, 'types' => $adminTypes],
            ['slug' => 'hunter', 'route_name' => 'hunter', 'path' => '/hunter', 'label' => 'عاطل', 'nav_group' => 'more', 'sort_order' => 25, 'types' => $adminTypes],
            ['slug' => 'external_cars', 'route_name' => 'external_cars', 'path' => '/external_cars', 'label' => 'السيارات الخارجية', 'nav_group' => 'more', 'sort_order' => 26, 'types' => $adminTypes],
            ['slug' => 'system_settings', 'route_name' => 'systemSettings', 'path' => '/system-settings', 'label' => 'إعدادات النظام', 'nav_group' => 'more', 'sort_order' => 27, 'types' => $allTypes],
            ['slug' => 'log_viewer', 'route_name' => 'logViewer', 'path' => '/log-viewer', 'label' => 'لوغ الأخطاء', 'nav_group' => 'more', 'sort_order' => 28, 'types' => $allTypes],
            ['slug' => 'page_permissions', 'route_name' => 'pagePermissions', 'path' => '/page-permissions', 'label' => 'صلاحيات الصفحات', 'nav_group' => 'more', 'sort_order' => 29, 'types' => $adminTypes],
            ['slug' => 'users', 'route_name' => 'users.index', 'path' => '/users', 'label' => 'المستخدمون', 'nav_group' => 'more', 'sort_order' => 30, 'types' => $adminTypes],
        ];

        foreach ($pages as $page) {
            $pageId = DB::table('app_pages')->insertGetId([
                'slug' => $page['slug'],
                'route_name' => $page['route_name'],
                'path' => $page['path'],
                'label' => $page['label'],
                'nav_group' => $page['nav_group'],
                'sort_order' => $page['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->attachTypes($pageId, $page['types']);
        }
    }
};
