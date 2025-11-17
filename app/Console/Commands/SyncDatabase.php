<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseSyncService;
use Illuminate\Support\Facades\Log;

class SyncDatabase extends Command
{
    protected $signature = 'db:sync 
                            {--direction=down : Direction of sync (down=MySQL->SQLite, up=SQLite->MySQL)}
                            {--tables= : Comma-separated list of tables to sync}
                            {--all : Sync all tables}';

    protected $description = 'مزامنة البيانات بين MySQL و SQLite';

    protected $syncService;

    public function __construct(DatabaseSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    public function handle()
    {
        $direction = $this->option('direction');
        $tables = $this->option('tables');
        $all = $this->option('all');

        $tablesArray = null;
        if ($tables) {
            $tablesArray = explode(',', $tables);
            $tablesArray = array_map('trim', $tablesArray);
        }

        $this->info("🔄 بدء عملية المزامنة...");
        $this->newLine();

        try {
            if ($direction === 'down' || $direction === 'down') {
                // من MySQL إلى SQLite
                $this->info("📥 مزامنة من MySQL إلى SQLite...");
                $results = $this->syncService->syncFromMySQLToSQLite($tablesArray);
            } else {
                // من SQLite إلى MySQL
                $this->info("📤 مزامنة من SQLite إلى MySQL...");
                $results = $this->syncService->syncFromSQLiteToMySQL($tablesArray);
            }

            $this->displayResults($results);

        } catch (\Exception $e) {
            $this->error("❌ فشلت عملية المزامنة: " . $e->getMessage());
            Log::error('Database sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    protected function displayResults(array $results)
    {
        $this->newLine();
        $this->info("✅ الجداول المزامنة بنجاح:");
        
        foreach ($results['success'] as $table => $count) {
            $this->line("  ✓ {$table}: {$count} سجل");
        }

        if (!empty($results['failed'])) {
            $this->newLine();
            $this->error("❌ الجداول التي فشلت:");
            foreach ($results['failed'] as $table => $error) {
                $this->line("  ✗ {$table}: {$error}");
            }
        }

        $this->newLine();
        $this->info("📊 إجمالي السجلات المزامنة: " . $results['total_synced']);
    }
}

