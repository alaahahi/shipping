<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCarContractZeroIdCommand extends Command
{
    protected $signature = 'contracts:fix-zero-id';

    protected $description = 'Remove invalid car_contract rows with id=0 and reset SQLite autoincrement if needed';

    public function handle(): int
    {
        if (! Schema::hasTable('car_contract')) {
            $this->error('Table car_contract does not exist.');

            return self::FAILURE;
        }

        $count = DB::table('car_contract')->where('id', 0)->count();
        if ($count === 0) {
            $this->info('No car_contract rows with id=0.');

            return self::SUCCESS;
        }

        DB::table('car_contract')->where('id', 0)->delete();
        $this->warn("Deleted {$count} car_contract row(s) with id=0.");

        if (DB::getDriverName() === 'sqlite' && Schema::hasTable('sqlite_sequence')) {
            $maxId = (int) DB::table('car_contract')->max('id');
            DB::table('sqlite_sequence')
                ->where('name', 'car_contract')
                ->update(['seq' => $maxId]);
            $this->info("SQLite sequence reset to {$maxId}.");
        }

        $this->info('Done. New contracts should auto-increment correctly.');

        return self::SUCCESS;
    }
}
