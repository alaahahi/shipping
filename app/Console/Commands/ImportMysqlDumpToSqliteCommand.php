<?php

namespace App\Console\Commands;

use App\Services\MysqlToSqliteConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportMysqlDumpToSqliteCommand extends Command
{
    protected $signature = 'db:import-mysql-dump
        {--file=database/intellij_ain.sql : Path to MySQL dump file}
        {--target= : SQLite file path (defaults to DB_DATABASE from .env)}
        {--force : Overwrite existing SQLite file}
        {--verify : Compare row counts for key tables after import}';

    protected $description = 'One-time import of a MySQL phpMyAdmin dump into SQLite';

    protected MysqlToSqliteConverter $converter;

    public function __construct(MysqlToSqliteConverter $converter)
    {
        parent::__construct();
        $this->converter = $converter;
    }

    public function handle(): int
    {
        $file = (string) $this->option('file');
        if (!str_starts_with($file, DIRECTORY_SEPARATOR) && !preg_match('#^[A-Za-z]:[/\\\\]#', $file)) {
            $file = base_path($file);
        }

        if (!File::exists($file)) {
            $this->error("Dump file not found: {$file}");

            return self::FAILURE;
        }

        $target = $this->option('target') ?: env('DB_DATABASE', database_path('database.sqlite'));
        if (!str_starts_with($target, DIRECTORY_SEPARATOR) && !preg_match('#^[A-Za-z]:[/\\\\]#', $target)) {
            $target = base_path($target);
        }

        if (File::exists($target) && !$this->option('force')) {
            $this->error("Target SQLite file already exists: {$target}");
            $this->line('Use --force to overwrite.');

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

        Config::set('database.connections.import_sqlite', [
            'driver' => 'sqlite',
            'database' => $target,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('import_sqlite');

        $pdo = DB::connection('import_sqlite')->getPdo();
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('PRAGMA foreign_keys = OFF');

        $this->info('Importing MySQL dump → SQLite');
        $this->line("  Source: {$file}");
        $this->line('  Target: '.$target);
        $this->line('  Size: '.$this->formatBytes(File::size($file)));

        $stats = ['create' => 0, 'insert' => 0, 'skip' => 0, 'errors' => []];
        $tables = [];

        foreach ($this->parseStatements($file) as $statement) {
            $type = $this->statementType($statement);

            if ($type === 'skip') {
                $stats['skip']++;
                continue;
            }

            if ($type === 'create') {
                if (!preg_match('/CREATE TABLE\s+`([^`]+)`/i', $statement, $m)) {
                    continue;
                }
                $table = $m[1];
                $sqliteDdl = $this->converter->createTableToSqlite($statement, $table);
                try {
                    $pdo->exec('DROP TABLE IF EXISTS "'.$table.'"');
                    $pdo->exec($sqliteDdl);
                    $tables[] = $table;
                    $stats['create']++;
                    $this->line("  ✓ CREATE {$table}");
                } catch (\Throwable $e) {
                    $stats['errors'][] = "CREATE {$table}: ".$e->getMessage();
                    $this->warn("  ✗ CREATE {$table}: ".$e->getMessage());
                }
                continue;
            }

            if ($type === 'insert') {
                $sqliteInsert = $this->converter->insertToSqlite($statement);
                try {
                    $pdo->exec($sqliteInsert);
                    $stats['insert']++;
                } catch (\Throwable $e) {
                    if (preg_match('/INSERT INTO\s+`([^`]+)`/i', $statement, $m)) {
                        $stats['errors'][] = "INSERT {$m[1]}: ".$e->getMessage();
                        $this->warn("  ✗ INSERT {$m[1]}: ".$e->getMessage());
                    } else {
                        $stats['errors'][] = 'INSERT: '.$e->getMessage();
                    }
                }
            }
        }

        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');

        $tableCount = (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchColumn();
        $fileSize = File::size($target);

        $this->newLine();
        $this->info("Import complete.");
        $this->line("  Tables created: {$stats['create']} (sqlite reports {$tableCount})");
        $this->line("  INSERT statements: {$stats['insert']}");
        $this->line("  Skipped statements: {$stats['skip']}");
        $this->line('  SQLite size: '.$this->formatBytes($fileSize));

        if (!empty($stats['errors'])) {
            $this->warn('  Errors: '.count($stats['errors']));
            foreach (array_slice($stats['errors'], 0, 10) as $err) {
                $this->line("    - {$err}");
            }
        }

        if ($this->option('verify')) {
            $this->verifyCounts($pdo);
        }

        $this->newLine();
        $this->line('Note: Laravel migrations remain separate; re-run with --force to refresh local data from dump.');

        return empty($stats['errors']) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return \Generator<int, string>
     */
    protected function parseStatements(string $path): \Generator
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return;
        }

        $buffer = '';
        $inString = false;
        $stringChar = '';

        try {
            while (($chunk = fread($handle, 65536)) !== false) {
                if ($chunk === '') {
                    break;
                }

                $len = strlen($chunk);
                for ($i = 0; $i < $len; $i++) {
                    $char = $chunk[$i];
                    $next = ($i + 1 < $len) ? $chunk[$i + 1] : '';

                    if (!$inString && $char === '-' && $next === '-') {
                        while ($i < $len && $chunk[$i] !== "\n") {
                            $i++;
                        }
                        continue;
                    }

                    if (!$inString && $char === '/' && $next === '*') {
                        $i += 2;
                        while ($i < $len - 1) {
                            if ($chunk[$i] === '*' && $chunk[$i + 1] === '/') {
                                $i += 2;
                                break;
                            }
                            $i++;
                        }
                        continue;
                    }

                    if ($inString) {
                        $buffer .= $char;
                        if ($char === '\\' && $next !== '') {
                            $buffer .= $next;
                            $i++;
                        } elseif ($char === $stringChar) {
                            $inString = false;
                            $stringChar = '';
                        }
                        continue;
                    }

                    if ($char === "'" || $char === '"') {
                        $inString = true;
                        $stringChar = $char;
                        $buffer .= $char;
                        continue;
                    }

                    if ($char === ';') {
                        $stmt = trim($buffer);
                        $buffer = '';
                        if ($stmt !== '') {
                            yield $stmt;
                        }
                        continue;
                    }

                    $buffer .= $char;
                }
            }

            $stmt = trim($buffer);
            if ($stmt !== '') {
                yield $stmt;
            }
        } finally {
            fclose($handle);
        }
    }

    protected function statementType(string $statement): string
    {
        $upper = strtoupper(ltrim($statement));

        if (str_starts_with($upper, 'CREATE TABLE')) {
            return 'create';
        }
        if (str_starts_with($upper, 'INSERT INTO')) {
            return 'insert';
        }

        return 'skip';
    }

    protected function verifyCounts(\PDO $pdo): void
    {
        $this->info('Verifying key table row counts…');
        $focus = ['users', 'car', 'transactions', 'wallets', 'driving'];

        foreach ($focus as $table) {
            try {
                $count = (int) $pdo->query('SELECT COUNT(*) FROM "'.$table.'"')->fetchColumn();
                $this->line("  {$table}: {$count} rows");
            } catch (\Throwable $e) {
                $this->warn("  {$table}: not found or error");
            }
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
