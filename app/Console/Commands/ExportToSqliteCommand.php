<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportToSqliteCommand extends Command
{
    protected $signature = 'db:export-to-sqlite
        {--source=mysql_live : Source connection name (must be real MySQL)}
        {--target= : Absolute/relative path for the new sqlite file}
        {--verify : Compare row counts after copy}
        {--chunk=500 : Insert chunk size}';

    protected $description = 'Read-only export from MySQL into a new SQLite database file';

    /** @var list<string> */
    protected array $skipTables = [
        'migrations',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'cache',
        'cache_locks',
        'personal_access_tokens',
        'sync_queue',
        'sync_jobs',
    ];

    public function handle(): int
    {
        $source = (string) $this->option('source');
        $target = $this->option('target') ?: database_path('shipping_export_'.date('Ymd_His').'.sqlite');
        $chunk = max(50, (int) $this->option('chunk'));

        if (!str_starts_with($target, DIRECTORY_SEPARATOR) && !preg_match('#^[A-Za-z]:[/\\\\]#', $target)) {
            $target = base_path($target);
        }

        $sourceConn = DB::connection($source);
        if (!in_array($sourceConn->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->error("Source connection [{$source}] is not MySQL/MariaDB (got {$sourceConn->getDriverName()}).");
            $this->line('Tip: use --source=mysql_live (always MySQL) or set APP_ENV=production so `mysql` is not remapped to sqlite.');

            return self::FAILURE;
        }

        $dir = dirname($target);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        if (File::exists($target)) {
            File::delete($target);
        }
        File::put($target, '');

        Config::set('database.connections.export_sqlite', [
            'driver' => 'sqlite',
            'database' => $target,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('export_sqlite');

        $targetConn = DB::connection('export_sqlite');
        $targetConn->getPdo()->exec('PRAGMA foreign_keys = OFF');
        $targetConn->getPdo()->exec('PRAGMA journal_mode = WAL');
        $targetConn->getPdo()->exec('PRAGMA busy_timeout = 5000');

        $tables = $this->listMysqlTables($sourceConn);
        $tables = array_values(array_diff($tables, $this->skipTables));

        $this->info('Exporting '.count($tables).' tables from ['.$source.'] → '.$target);

        foreach ($tables as $table) {
            $this->exportTable($sourceConn, $targetConn, $table, $chunk);
        }

        $targetConn->getPdo()->exec('PRAGMA foreign_keys = ON');

        if ($this->option('verify')) {
            return $this->verify($sourceConn, $targetConn, $tables) ? self::SUCCESS : self::FAILURE;
        }

        $this->info('Done. SQLite file: '.$target);

        return self::SUCCESS;
    }

    protected function listMysqlTables($sourceConn): array
    {
        $dbName = $sourceConn->getDatabaseName();
        $rows = $sourceConn->select(
            'SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ? ORDER BY TABLE_NAME',
            [$dbName, 'BASE TABLE']
        );

        return array_map(fn ($r) => $r->name, $rows);
    }

    protected function exportTable($sourceConn, $targetConn, string $table, int $chunk): void
    {
        $this->line("→ {$table}");

        $create = $sourceConn->selectOne('SHOW CREATE TABLE `'.$table.'`');
        $createSql = $create->{'Create Table'} ?? null;
        if (!$createSql) {
            $this->warn("  skip (no CREATE TABLE): {$table}");

            return;
        }

        $sqliteDdl = $this->mysqlCreateToSqlite($createSql, $table);
        $targetConn->statement("DROP TABLE IF EXISTS \"{$table}\"");
        $targetConn->statement($sqliteDdl);

        $columns = $sourceConn->getSchemaBuilder()->getColumnListing($table);
        if (empty($columns)) {
            return;
        }

        $colList = implode(', ', array_map(fn ($c) => '"'.$c.'"', $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $insertSql = "INSERT INTO \"{$table}\" ({$colList}) VALUES ({$placeholders})";

        $count = 0;
        $orderCol = in_array('id', $columns, true) ? 'id' : $columns[0];
        $sourceConn->table($table)->orderBy($orderCol)->chunk($chunk, function ($rows) use ($targetConn, $insertSql, $columns, &$count) {
            $targetConn->beginTransaction();
            try {
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($columns as $col) {
                        $val = $row->$col ?? null;
                        if (is_bool($val)) {
                            $val = $val ? 1 : 0;
                        } elseif (is_array($val) || is_object($val)) {
                            $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                        }
                        $values[] = $val;
                    }
                    $targetConn->insert($insertSql, $values);
                    $count++;
                }
                $targetConn->commit();
            } catch (\Throwable $e) {
                $targetConn->rollBack();
                throw $e;
            }
        });

        $this->line("  copied {$count} rows");
    }

    protected function mysqlCreateToSqlite(string $mysqlCreate, string $table): string
    {
        if (!preg_match('/\((.*)\)\s*ENGINE=/is', $mysqlCreate, $m)) {
            return "CREATE TABLE \"{$table}\" (id INTEGER PRIMARY KEY AUTOINCREMENT)";
        }

        $body = $m[1];
        $lines = preg_split('/\r\n|\n|\r/', $body) ?: [];
        $colDefs = [];
        $primary = null;

        foreach ($lines as $line) {
            $line = trim($line);
            $line = rtrim($line, ',');
            if ($line === '' || stripos($line, 'KEY ') === 0 || stripos($line, 'UNIQUE KEY') === 0
                || stripos($line, 'CONSTRAINT') === 0 || stripos($line, 'FULLTEXT') === 0
                || stripos($line, 'SPATIAL') === 0) {
                if (preg_match('/^PRIMARY KEY\s*\((.+)\)/i', $line, $pk)) {
                    $primary = trim(str_replace('`', '', $pk[1]));
                }
                continue;
            }

            if (!preg_match('/^`([^`]+)`\s+(.+)$/i', $line, $cm)) {
                continue;
            }

            $name = $cm[1];
            $rest = $cm[2];
            $type = 'TEXT';
            if (preg_match('/\b(tinyint|smallint|mediumint|int|bigint|integer)\b/i', $rest)) {
                $type = 'INTEGER';
            } elseif (preg_match('/\b(decimal|numeric|float|double|real)\b/i', $rest)) {
                $type = 'REAL';
            } elseif (preg_match('/\b(blob|binary|varbinary)\b/i', $rest)) {
                $type = 'BLOB';
            }

            $notNull = stripos($rest, 'NOT NULL') !== false ? ' NOT NULL' : '';
            $auto = stripos($rest, 'AUTO_INCREMENT') !== false;
            $def = "\"{$name}\" {$type}{$notNull}";
            if ($auto) {
                $def = "\"{$name}\" INTEGER PRIMARY KEY AUTOINCREMENT";
                $primary = $name;
            }
            $colDefs[] = $def;
        }

        if ($primary && !preg_match('/PRIMARY KEY/i', implode(' ', $colDefs))) {
            $colDefs[] = "PRIMARY KEY (\"{$primary}\")";
        }

        return 'CREATE TABLE "'.$table.'" ('.implode(', ', $colDefs).')';
    }

    protected function verify($sourceConn, $targetConn, array $tables): bool
    {
        $this->info('Verifying counts…');
        $ok = true;
        $focus = array_values(array_intersect($tables, ['car', 'transactions', 'car_contract', 'wallets', 'users']));

        foreach ($tables as $table) {
            $src = (int) $sourceConn->table($table)->count();
            $dst = (int) $targetConn->table($table)->count();
            $mark = $src === $dst ? 'OK' : 'MISMATCH';
            if ($src !== $dst) {
                $ok = false;
            }
            if (in_array($table, $focus, true) || $src !== $dst) {
                $this->line("  [{$mark}] {$table}: mysql={$src} sqlite={$dst}");
            }
        }

        if ($ok && in_array('wallets', $tables, true)) {
            $srcSum = (float) $sourceConn->table('wallets')->sum('balance');
            $dstSum = (float) $targetConn->table('wallets')->sum('balance');
            $this->line(sprintf('  wallets.balance sum: mysql=%.4f sqlite=%.4f', $srcSum, $dstSum));
            if (abs($srcSum - $dstSum) > 0.01) {
                $ok = false;
                $this->error('Wallet balance checksum mismatch');
            }
        }

        $this->info($ok ? 'Verify passed.' : 'Verify FAILED.');

        return $ok;
    }
}
