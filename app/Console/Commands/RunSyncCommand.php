<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncService;
use Illuminate\Console\Command;

class RunSyncCommand extends Command
{
    protected $signature = 'sync:run';

    protected $description = 'Execute offline sync push and pull tasks';

    public function handle(SyncService $syncService): int
    {
        $this->info('Pushing local changes...');
        $syncService->pushLocalChangesToServer();

        $this->info('Pulling remote changes...');
        $syncService->pullRemoteChanges();

        $this->info('Sync completed.');

        return self::SUCCESS;
    }
}

