<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CarContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestContractsList extends Command
{
    protected $signature = 'contracts:test {--owner_id= : Filter by owner_id} {--from= : Date from (Y-m-d)} {--to= : Date to (Y-m-d)}';

    protected $description = 'Test: show car_contract counts and sample data (to debug getIndexContractCar)';

    public function handle()
    {
        $this->info('=== اختبار جدول العقود car_contract ===');

        $total = CarContract::count();
        $this->info("إجمالي العقود (بدون حذف): {$total}");

        $totalWithTrashed = CarContract::withTrashed()->count();
        $trashed = $totalWithTrashed - $total;
        if ($trashed > 0) {
            $this->warn("عقود محذوفة (soft): {$trashed}");
        }

        $byOwner = CarContract::select('owner_id', DB::raw('count(*) as cnt'))
            ->groupBy('owner_id')
            ->get();
        $this->info('عدد العقود حسب owner_id:');
        foreach ($byOwner as $row) {
            $this->line("  owner_id={$row->owner_id} => {$row->cnt} عقد");
        }

        $ownerId = $this->option('owner_id');
        $from = $this->option('from');
        $to = $this->option('to');

        $query = CarContract::with('user')->orderBy('id', 'desc');
        if ($ownerId !== null && $ownerId !== '') {
            $query->where('owner_id', $ownerId);
            $this->info("فلتر owner_id = {$ownerId}");
        }
        if ($from && $to) {
            $query->whereBetween('created', [$from, $to]);
            $this->info("فلتر تاريخ من {$from} إلى {$to}");
        }

        $count = $query->count();
        $this->info("عدد النتائج بعد الفلاتر: {$count}");

        $sample = $query->take(5)->get(['id', 'owner_id', 'user_id', 'created', 'name_seller', 'name_buyer', 'vin']);
        if ($sample->isEmpty()) {
            $this->warn('لا توجد عقود تطابق الفلاتر.');
        } else {
            $this->info('عينة (أحدث 5):');
            foreach ($sample as $c) {
                $this->line("  id={$c->id} owner_id={$c->owner_id} user_id={$c->user_id} created={$c->created} | البائع: {$c->name_seller} | المشتري: {$c->name_buyer}");
            }
        }

        $this->info('--- للمقارنة: المستخدمون و owner_id ---');
        $users = User::select('id', 'name', 'owner_id', 'type_id')->take(10)->get();
        foreach ($users as $u) {
            $this->line("  user_id={$u->id} name={$u->name} owner_id={$u->owner_id} type_id={$u->type_id}");
        }

        return 0;
    }
}
