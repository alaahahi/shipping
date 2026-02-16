<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Services\DatabaseSyncService;

class SyncMonitorController extends Controller
{
    /**
     * هل يجب إعادة توجيه طلب MySQL إلى السيرفر الموجود في ONLINE_URL؟
     * (عند العمل محلياً لا يوجد MySQL حقيقي - نطلب من الباك إند للسيرفر)
     */
    protected function shouldProxyToOnline(?string $forceConnection): bool
    {
        if ($forceConnection !== 'mysql') {
            return false;
        }
        return env('LOCAL_NO_REMOTE', false) || app()->environment('local');
    }

    /**
     * إعادة توجيه الطلب إلى API السيرفر (ONLINE_URL)
     */
    protected function proxyToOnlineServer(Request $request, string $path, string $method = 'GET'): JsonResponse
    {
        $baseUrl = rtrim(env('ONLINE_URL', ''), '/');
        if (empty($baseUrl)) {
            return response()->json(['error' => 'ONLINE_URL غير محدد في .env'], 500);
        }

        $url = $baseUrl . '/api/' . ltrim($path, '/');
        $queryParams = $request->query();
        if (!empty($queryParams)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($queryParams);
        }

        $headers = [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
        if ($apiKey = env('API_KEY')) {
            $headers['X-API-Key'] = $apiKey;
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        try {
            if ($method === 'GET') {
                $response = Http::timeout(30)->withHeaders($headers)->get($url);
            } else {
                $response = Http::timeout(30)->withHeaders($headers)->{$method}($url, $request->all());
            }

            $status = $response->successful() ? 200 : $response->status();
            return response()->json($response->json() ?? ['error' => $response->body()], $status);
        } catch (\Throwable $e) {
            Log::error('Proxy to online server failed', ['url' => $url, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'فشل الاتصال بالسيرفر: ' . $e->getMessage()], 502);
        }
    }

    /**
     * مزامنة من السيرفر (ONLINE_URL) إلى SQLite المحلي عبر API - عند LOCAL_NO_REMOTE
     * public للسماح للـ Scheduler باستدعائها
     */
    public function syncFromOnlineServer(?array $tablesArray, bool $forceFullSync): array
    {
        $baseUrl = rtrim(env('ONLINE_URL', ''), '/');
        if (empty($baseUrl)) {
            throw new \Exception('ONLINE_URL غير محدد في .env');
        }

        $headers = ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'];
        if ($apiKey = env('API_KEY')) {
            $headers['X-API-Key'] = $apiKey;
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $excluded = [
            'migrations', 'jobs', 'job_batches', 'failed_jobs', 'sessions', 'cache', 'cache_locks',
            'sqlite_sequence', 'sqlite_master', 'sync_metadata', 'sync_jobs', 'sync_queue',
            'password_resets', 'personal_access_tokens', 'licenses',
            'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring',
        ];

        $results = ['success' => [], 'failed' => [], 'total_synced' => 0];

        $tablesResponse = Http::timeout(30)->withHeaders($headers)->get($baseUrl . '/api/sync-monitor/tables', ['force_connection' => 'mysql']);
        if (!$tablesResponse->successful()) {
            throw new \Exception('فشل جلب قائمة الجداول من السيرفر: ' . ($tablesResponse->body() ?: $tablesResponse->status()));
        }

        $data = $tablesResponse->json();
        $tableInfos = $data['tables'] ?? [];
        $tables = $tablesArray ?? array_column($tableInfos, 'name');
        $tables = array_filter($tables, fn ($t) => !in_array($t, $excluded));

        $sqliteDb = DB::connection('sync_sqlite');
        $sqliteDb->statement('PRAGMA foreign_keys = OFF');

        foreach ($tables as $tableName) {
            try {
                $synced = $this->syncTableFromOnline($baseUrl, $headers, $sqliteDb, $tableName, $forceFullSync);
                $results['success'][$tableName] = $synced;
                $results['total_synced'] += $synced;
            } catch (\Throwable $e) {
                $results['failed'][$tableName] = $e->getMessage();
                Log::error("Sync from online failed: {$tableName}", ['error' => $e->getMessage()]);
            }
        }

        $sqliteDb->statement('PRAGMA foreign_keys = ON');
        return $results;
    }

    protected function syncTableFromOnline(string $baseUrl, array $headers, $sqliteDb, string $tableName, bool $forceFullSync): int
    {
        $limit = 2000;
        $offset = 0;
        $totalSynced = 0;
        $tableCreated = false;
        $localColumns = null;

        do {
            $url = "{$baseUrl}/api/sync-monitor/table/{$tableName}?force_connection=mysql&limit={$limit}&offset={$offset}";
            $resp = Http::timeout(60)->withHeaders($headers)->get($url);
            if (!$resp->successful()) {
                throw new \Exception('فشل جلب البيانات: ' . $resp->status());
            }

            $data = $resp->json();
            $rows = $data['data'] ?? [];
            $total = (int) ($data['total'] ?? 0);

            if (empty($rows) && $offset === 0) {
                break;
            }

            if (!empty($rows) && !$tableCreated) {
                $columns = array_keys($rows[0]);
                $this->ensureSqliteTableExists($sqliteDb, $tableName, $columns);
                $tableCreated = true;
            }

            $localColumns = $localColumns ?? $this->getSqliteTableColumns($sqliteDb, $tableName);

            if ($forceFullSync && $offset === 0) {
                try {
                    $sqliteDb->statement("DELETE FROM \"{$tableName}\"");
                } catch (\Throwable $e) {
                    // جدول فارغ أو غير موجود
                }
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                $filteredChunk = array_map(fn ($row) => $this->filterRowToColumns($row, $localColumns), $chunk);
                try {
                    $sqliteDb->table($tableName)->insert($filteredChunk);
                    $totalSynced += count($filteredChunk);
                } catch (\Throwable $e) {
                    foreach ($filteredChunk as $row) {
                        try {
                            if (isset($row['id'])) {
                                $sqliteDb->table($tableName)->updateOrInsert(['id' => $row['id']], $row);
                            } else {
                                $sqliteDb->table($tableName)->insert($row);
                            }
                            $totalSynced++;
                        } catch (\Throwable $e2) {
                            Log::debug("Skip row in {$tableName}", ['error' => $e2->getMessage()]);
                        }
                    }
                }
            }

            $offset += $limit;
        } while ($offset < $total);

        return $totalSynced;
    }

    /**
     * جلب أعمدة جدول SQLite المحلي
     */
    protected function getSqliteTableColumns($db, string $tableName): array
    {
        try {
            $result = $db->select("PRAGMA table_info(\"{$tableName}\")");
            return array_map(fn ($c) => $c->name, $result);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * تصفية الصف ليشمل فقط الأعمدة الموجودة في الجدول المحلي (لتجنب التعارض مع الأعمدة الناقصة)
     */
    protected function filterRowToColumns(array $row, array $allowedColumns): array
    {
        if (empty($allowedColumns)) {
            return $row;
        }
        $allowed = array_flip($allowedColumns);
        return array_intersect_key($row, $allowed);
    }

    protected function ensureSqliteTableExists($db, string $tableName, array $columnNames): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            return;
        }
        $exists = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);
        if (!empty($exists)) {
            return;
        }
        $cols = [];
        $hasId = false;
        foreach ($columnNames as $i => $name) {
            $safe = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $name) ?: 'col_' . $i;
            if (strtolower($safe) === 'id') {
                $cols[] = "\"{$safe}\" INTEGER PRIMARY KEY";
                $hasId = true;
            } else {
                $cols[] = "\"{$safe}\" TEXT";
            }
        }
        if (empty($cols)) {
            $cols[] = '"id" INTEGER PRIMARY KEY';
        }
        $db->statement('CREATE TABLE IF NOT EXISTS "' . $tableName . '" (' . implode(', ', $cols) . ')');
    }

    /**
     * إرجاع قائمة الجداول مع عدد السجلات واتصال القاعدة الحالي.
     */
    public function tables(Request $request): JsonResponse
    {
        try {
            $forceConnection = $request->get('force_connection');
            if ($this->shouldProxyToOnline($forceConnection)) {
                return $this->proxyToOnlineServer($request, 'sync-monitor/tables', 'GET');
            }

            if ($forceConnection === 'sync_sqlite') {
                $connection = 'sync_sqlite';
                $isUsingFallback = true;
            } elseif ($forceConnection === 'mysql') {
                $connection = 'mysql';
                $isUsingFallback = false;
            } else {
                $connection = config('database.default');
                $isUsingFallback = $connection === 'sync_sqlite';
            }
            
            $db = DB::connection($isUsingFallback ? 'sync_sqlite' : 'mysql');

            $tableNames = $this->fetchTableNames($db, $isUsingFallback);

            $tableInfo = [];
            foreach ($tableNames as $tableName) {
                try {
                    $tableInfo[] = [
                        'name' => $tableName,
                        'count' => $db->table($tableName)->count(),
                        'connection' => $isUsingFallback ? 'sync_sqlite' : 'mysql',
                    ];
                } catch (\Throwable $th) {
                    Log::debug('Failed reading table for sync monitor', [
                        'table' => $tableName,
                        'error' => $th->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'tables' => $tableInfo,
                'connection' => $connection,
                'is_fallback' => $isUsingFallback,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * جداول MySQL الموجودة وغير الموجودة في SQLite
     * عند LOCAL_NO_REMOTE: يقارن MySQL البعيد (السيرفر) مع SQLite المحلي
     */
    public function tablesComparison(Request $request): JsonResponse
    {
        try {
            $mysqlTables = [];
            $mysqlTableCounts = [];
            $sqliteDb = DB::connection('sync_sqlite');
            $sqliteTables = $this->fetchTableNames($sqliteDb, true);

            if ($this->shouldProxyToOnline('mysql')) {
                // محلياً: جلب جداول MySQL من السيرفر البعيد ومقارنتها مع SQLite المحلي
                $baseUrl = rtrim(env('ONLINE_URL', ''), '/');
                if (empty($baseUrl)) {
                    return response()->json(['error' => 'ONLINE_URL غير محدد في .env'], 500);
                }
                $url = $baseUrl . '/api/sync-monitor/tables?force_connection=mysql';
                $headers = ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'];
                if ($apiKey = env('API_KEY')) {
                    $headers['X-API-Key'] = $apiKey;
                    $headers['Authorization'] = 'Bearer ' . $apiKey;
                }
                $response = Http::timeout(30)->withHeaders($headers)->get($url);
                if (!$response->successful()) {
                    return response()->json(['error' => 'فشل جلب جداول MySQL من السيرفر'], 502);
                }
                $data = $response->json();
                $tableInfos = $data['tables'] ?? [];
                foreach ($tableInfos as $t) {
                    $mysqlTables[] = $t['name'] ?? $t;
                    $mysqlTableCounts[$t['name'] ?? $t] = $t['count'] ?? 0;
                }
            } else {
                $mysqlDb = DB::connection('mysql');
                $mysqlTables = $this->fetchTableNames($mysqlDb, false);
                foreach ($mysqlTables as $table) {
                    try {
                        $mysqlTableCounts[$table] = $mysqlDb->table($table)->count();
                    } catch (\Throwable $e) {
                        $mysqlTableCounts[$table] = 0;
                    }
                }
            }

            $mysqlOnly = array_values(array_diff($mysqlTables, $sqliteTables));
            $sqliteOnly = array_values(array_diff($sqliteTables, $mysqlTables));
            $inBoth = array_values(array_intersect($mysqlTables, $sqliteTables));

            $mysqlOnlyWithCount = [];
            foreach ($mysqlOnly as $table) {
                $mysqlOnlyWithCount[] = [
                    'name' => $table,
                    'count' => $mysqlTableCounts[$table] ?? 0,
                ];
            }

            return response()->json([
                'mysql_tables' => $mysqlTables,
                'sqlite_tables' => $sqliteTables,
                'mysql_only' => $mysqlOnlyWithCount,
                'sqlite_only' => $sqliteOnly,
                'in_both' => $inBoth,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * عرض تفاصيل جدول معين (الأعمدة + بيانات محدودة).
     */
    public function tableDetails(string $tableName, Request $request): JsonResponse
    {
        try {
            $forceConnection = $request->get('force_connection');
            if ($this->shouldProxyToOnline($forceConnection)) {
                return $this->proxyToOnlineServer($request, "sync-monitor/table/{$tableName}", 'GET');
            }

            if ($forceConnection === 'sync_sqlite') {
                $connection = 'sync_sqlite';
                $isUsingFallback = true;
            } elseif ($forceConnection === 'mysql') {
                $connection = 'mysql';
                $isUsingFallback = false;
            } else {
                $connection = config('database.default');
                $isUsingFallback = $connection === 'sync_sqlite';
            }
            
            $db = DB::connection($isUsingFallback ? 'sync_sqlite' : 'mysql');

            $limit = (int) $request->get('limit', 50);
            $offset = (int) $request->get('offset', 0);

            $columns = $this->fetchTableColumns($db, $tableName, $isUsingFallback);

            // بناء الاستعلام مع ترتيب من الأحدث للأقدم
            $query = $db->table($tableName);
            
            // محاولة الترتيب حسب created_at أو updated_at أو id
            if ($this->hasColumn($db, $tableName, 'updated_at', $isUsingFallback)) {
                $query->orderBy('updated_at', 'desc');
            } elseif ($this->hasColumn($db, $tableName, 'created_at', $isUsingFallback)) {
                $query->orderBy('created_at', 'desc');
            } elseif ($this->hasColumn($db, $tableName, 'id', $isUsingFallback)) {
                $query->orderBy('id', 'desc');
            }
            
            $data = $query
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->map(function ($item) {
                    // تحويل كل سجل إلى array وإزالة أي أعمدة غير موجودة
                    return (array) $item;
                })
                ->values()
                ->toArray();

            $total = $db->table($tableName)->count();

            // Debug logging
            Log::debug('Table details response', [
                'table' => $tableName,
                'columns_count' => count($columns),
                'data_count' => count($data),
                'total' => $total,
                'connection' => $isUsingFallback ? 'sync_sqlite' : 'mysql',
                'columns' => $columns,
                'first_row_sample' => !empty($data) ? array_keys($data[0]) : []
            ]);

            return response()->json([
                'table' => $tableName,
                'columns' => $columns,
                'data' => $data,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'connection' => $isUsingFallback ? 'sync_sqlite' : 'mysql',
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * التحقق مما إذا كان الاتصال فعلياً SQLite (حتى لو كان اسمه mysql)
     */
    protected function isConnectionSqlite($db): bool
    {
        return ($db->getDriverName() ?? '') === 'sqlite';
    }

    /**
     * جلب أسماء الجداول من الاتصال الحالي (MySQL أو SQLite).
     * يستخدم السائق الفعلي للاتصال وليس اسمه - لأن mysql قد يشير إلى SQLite في الوضع المحلي.
     */
    protected function fetchTableNames($db, bool $isUsingFallback): array
    {
        if ($this->isConnectionSqlite($db)) {
            $tables = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            return array_map(fn ($table) => $table->name, $tables);
        }

        $database = config('database.connections.mysql.database');
        $tables = $db->select(
            "SELECT TABLE_NAME as name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME",
            [$database]
        );

        return array_map(fn ($table) => $table->name, $tables);
    }

    /**
     * جلب أسماء الأعمدة لجدول معين.
     */
    protected function fetchTableColumns($db, string $tableName, bool $isUsingFallback): array
    {
        // التحقق من أن اسم الجدول آمن (يحتوي فقط على أحرف وأرقام وشرطة سفلية)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            return [];
        }

        if ($this->isConnectionSqlite($db)) {
            // SQLite: استخدام PRAGMA table_info مع التحقق من الاسم
            try {
                $columns = $db->select("PRAGMA table_info(`{$tableName}`)");
                $columnNames = array_map(fn ($column) => $column->name, $columns);
                
                Log::debug('SQLite columns fetched', [
                    'table' => $tableName,
                    'columns_count' => count($columnNames),
                    'columns' => $columnNames
                ]);
                
                return $columnNames;
            } catch (\Exception $e) {
                Log::error('Failed to fetch SQLite columns', [
                    'table' => $tableName,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        }

        // MySQL: استخدام prepared statements
        $database = config('database.connections.mysql.database');
        $columns = $db->select(
            "SELECT COLUMN_NAME as name FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            [$database, $tableName]
        );

        return array_map(fn ($column) => $column->name, $columns);
    }

    /**
     * التحقق من وجود عمود في الجدول
     */
    protected function hasColumn($db, string $tableName, string $columnName, bool $isUsingFallback): bool
    {
        // التحقق من أن اسم الجدول والعمود آمن
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $columnName)) {
            return false;
        }

        try {
            if ($this->isConnectionSqlite($db)) {
                // SQLite: استخدام PRAGMA table_info
                $columns = $db->select("PRAGMA table_info(`{$tableName}`)");
                return collect($columns)->contains('name', $columnName);
            }

            // MySQL: استخدام information_schema
            $database = config('database.connections.mysql.database');
            $result = $db->select(
                "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                [$database, $tableName, $columnName]
            );
            
            return ($result[0]->count ?? 0) > 0;
        } catch (\Exception $e) {
            Log::warning('Failed to check column existence', [
                'table' => $tableName,
                'column' => $columnName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * بدء عملية المزامنة من MySQL إلى SQLite
     */
    public function sync(Request $request, DatabaseSyncService $syncService): JsonResponse
    {
        // تعطيل المزامنة على السيرفر - تعمل فقط في البيئة المحلية
        if (env('APP_ENV') === 'server' || env('APP_ENV') === 'production') {
            return response()->json([
                'success' => false,
                'message' => 'المزامنة معطلة على السيرفر. تعمل فقط في البيئة المحلية.',
                'error' => 'Sync is disabled on server environment'
            ], 403);
        }

        try {
            $direction = $request->get('direction', 'down'); // down = MySQL->SQLite, up = SQLite->MySQL
            $tables = $request->get('tables'); // comma-separated list
            $safeMode = $request->get('safe_mode', true); // Safe Mode: إضافة فقط، لا تحديث
            $createBackup = $request->get('create_backup', true); // إنشاء نسخة احتياطية
            $forceFullSync = $request->get('force_full_sync', false); // مزامنة كاملة (تجاهل metadata)

            $tablesArray = null;
            if ($tables) {
                $tablesArray = explode(',', $tables);
                $tablesArray = array_map('trim', $tablesArray);
            }

            // حفظ حالة المزامنة في Session للتتبع
            $userId = optional($request->user())->id ?? 'guest';
            $sessionKey = 'sync_progress_' . $userId;
            cache()->put($sessionKey, [
                'status' => 'running',
                'direction' => $direction,
                'current_table' => null,
                'completed_tables' => 0,
                'total_tables' => 0,
                'started_at' => now()->toDateTimeString(),
                'message' => 'بدء المزامنة...'
            ], 300); // 5 دقائق

            if ($direction === 'down') {
                // عند LOCAL_NO_REMOTE: جلب البيانات من ONLINE_URL عبر API (لا اتصال MySQL محلي)
                if (env('LOCAL_NO_REMOTE', false) || app()->environment('local')) {
                    $results = $this->syncFromOnlineServer($tablesArray, filter_var($forceFullSync, FILTER_VALIDATE_BOOLEAN));
                } else {
                    $results = $syncService->syncFromMySQLToSQLite(
                        $tablesArray,
                        filter_var($forceFullSync, FILTER_VALIDATE_BOOLEAN)
                    );
                }
            } else {
                // من SQLite إلى MySQL (يحتاج حماية)
                $results = $syncService->syncFromSQLiteToMySQL(
                    $tablesArray, 
                    filter_var($safeMode, FILTER_VALIDATE_BOOLEAN), // Safe Mode
                    filter_var($createBackup, FILTER_VALIDATE_BOOLEAN), // Create Backup
                    filter_var($forceFullSync, FILTER_VALIDATE_BOOLEAN) // Force Full Sync
                );
            }

            // تحديث حالة المزامنة عند الانتهاء
            cache()->put($sessionKey, [
                'status' => 'completed',
                'direction' => $direction,
                'current_table' => null,
                'completed_tables' => count($results['success'] ?? []),
                'total_tables' => count($results['success'] ?? []) + count($results['failed'] ?? []),
                'completed_at' => now()->toDateTimeString(),
                'message' => 'تمت المزامنة بنجاح',
                'results' => $results
            ], 300);

            return response()->json([
                'success' => true,
                'message' => 'تمت المزامنة بنجاح',
                'results' => $results,
                'safe_mode' => $direction === 'up' ? filter_var($safeMode, FILTER_VALIDATE_BOOLEAN) : null,
                'backup_file' => $results['backup_file'] ?? null
            ]);

        } catch (\Exception $e) {
            // تحديث حالة المزامنة عند الفشل
            $userId = optional($request->user())->id ?? 'guest';
            $sessionKey = 'sync_progress_' . $userId;
            cache()->put($sessionKey, [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'completed_at' => now()->toDateTimeString(),
                'message' => 'فشلت المزامنة'
            ], 300);

            Log::error('Sync API failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'فشلت المزامنة - تم Rollback وحماية البيانات'
            ], 500);
        }
    }

    /**
     * الحصول على حالة تقدم المزامنة
     */
    public function syncProgress(Request $request): JsonResponse
    {
        try {
            $userId = optional($request->user())->id ?? 'guest';
            $sessionKey = 'sync_progress_' . $userId;
            $progress = cache()->get($sessionKey);

            if (!$progress) {
                return response()->json([
                    'status' => 'idle',
                    'message' => 'لا توجد عملية مزامنة نشطة'
                ]);
            }

            return response()->json($progress);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * اختبار المزامنة - مقارنة البيانات بين MySQL و SQLite
     */
    public function testSync(string $tableName): JsonResponse
    {
        try {
            $mysqlDb = DB::connection('mysql');
            $sqliteDb = DB::connection('sync_sqlite');
            
            if (!$this->tableExists($mysqlDb, $tableName, false)) {
                return response()->json([
                    'error' => "Table {$tableName} does not exist in MySQL"
                ], 404);
            }
            
            if (!$this->tableExists($sqliteDb, $tableName, true)) {
                return response()->json([
                    'error' => "Table {$tableName} does not exist in SQLite"
                ], 404);
            }
            
            // جلب جميع IDs من كلا الجانبين
            $mysqlIds = $mysqlDb->table($tableName)->pluck('id')->sort()->values()->toArray();
            $sqliteIds = $sqliteDb->table($tableName)->pluck('id')->sort()->values()->toArray();
            
            // العثور على السجلات المفقودة
            $missingInSQLite = array_diff($mysqlIds, $sqliteIds);
            $missingInMySQL = array_diff($sqliteIds, $mysqlIds);
            
            // جلب metadata
            $metadata = DB::connection('mysql')->table('sync_metadata')
                ->where('table_name', $tableName)
                ->get()
                ->map(function ($item) {
                    return [
                        'direction' => $item->direction,
                        'last_synced_id' => $item->last_synced_id,
                        'last_synced_at' => $item->last_synced_at,
                        'last_updated_at' => $item->last_updated_at,
                        'total_synced' => $item->total_synced,
                    ];
                })
                ->toArray();
            
            // إحصائيات
            $stats = [
                'mysql_count' => count($mysqlIds),
                'sqlite_count' => count($sqliteIds),
                'missing_in_sqlite' => count($missingInSQLite),
                'missing_in_mysql' => count($missingInMySQL),
                'difference' => count($mysqlIds) - count($sqliteIds),
            ];
            
            // جلب أكبر ID من كلا الجانبين
            $mysqlMaxId = !empty($mysqlIds) ? max($mysqlIds) : 0;
            $sqliteMaxId = !empty($sqliteIds) ? max($sqliteIds) : 0;
            
            return response()->json([
                'table' => $tableName,
                'stats' => $stats,
                'mysql_max_id' => $mysqlMaxId,
                'sqlite_max_id' => $sqliteMaxId,
                'missing_in_sqlite_ids' => array_values(array_slice($missingInSQLite, 0, 50)), // أول 50 فقط
                'missing_in_mysql_ids' => array_values(array_slice($missingInMySQL, 0, 50)), // أول 50 فقط
                'metadata' => $metadata,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync test failed', [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على بيانات جدول sync_metadata لمتابعة حالة المزامنة
     */
    public function syncMetadata(): JsonResponse
    {
        try {
            $mysqlDb = DB::connection('mysql');
            
            // التحقق من وجود جدول sync_metadata
            if (!$this->tableExists($mysqlDb, 'sync_metadata', false)) {
                return response()->json([
                    'metadata' => [],
                    'message' => 'جدول sync_metadata غير موجود - قم بتشغيل migration أولاً'
                ]);
            }

            $metadata = $mysqlDb->table('sync_metadata')
                ->orderBy('table_name', 'asc')
                ->orderBy('direction', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'table_name' => $item->table_name,
                        'direction' => $item->direction,
                        'direction_label' => $item->direction === 'down' ? 'MySQL → SQLite' : 'SQLite → MySQL',
                        'last_synced_id' => $item->last_synced_id ?? 0,
                        'last_synced_at' => $item->last_synced_at ? date('Y-m-d H:i:s', strtotime($item->last_synced_at)) : null,
                        'last_updated_at' => $item->last_updated_at ? date('Y-m-d H:i:s', strtotime($item->last_updated_at)) : null,
                        'total_synced' => $item->total_synced ?? 0,
                        'created_at' => $item->created_at ? date('Y-m-d H:i:s', strtotime($item->created_at)) : null,
                        'updated_at' => $item->updated_at ? date('Y-m-d H:i:s', strtotime($item->updated_at)) : null,
                    ];
                })
                ->values()
                ->toArray();

            // جلب إحصائيات
            $stats = [
                'total_tables' => count(array_unique(array_column($metadata, 'table_name'))),
                'total_records' => count($metadata),
                'total_synced_records' => array_sum(array_column($metadata, 'total_synced')),
            ];

            return response()->json([
                'metadata' => $metadata,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch sync metadata', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'metadata' => []
            ], 500);
        }
    }

    /**
     * تشغيل المهام المجدولة مرة واحدة (schedule:run)
     * يُنفّذ المزامنة التلقائية فوراً دون انتظار الـ cron
     */
    public function runSchedule(): JsonResponse
    {
        try {
            $exitCode = Artisan::call('schedule:run');
            $output = trim(Artisan::output());

            return response()->json([
                'success' => $exitCode === 0,
                'message' => $exitCode === 0 ? 'تم تشغيل المهام المجدولة' : 'انتهى مع أخطاء',
                'output' => $output,
                'exit_code' => $exitCode,
            ]);
        } catch (\Exception $e) {
            Log::error('Run schedule failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'فشل تشغيل المهام المجدولة',
            ], 500);
        }
    }

    /**
     * حالة المزامنة التلقائية (من scheduler)
     */
    public function autoSyncStatus(): JsonResponse
    {
        $file = storage_path('app/sync_auto_status.json');
        if (!File::exists($file)) {
            return response()->json([
                'ok' => null,
                'last_run' => null,
                'message' => 'لم يتم تشغيل المزامنة التلقائية بعد - تأكد من تشغيل run-scheduler.bat',
                'pull_synced' => 0,
                'push_synced' => 0,
                'error' => null,
            ]);
        }
        try {
            $data = json_decode(File::get($file), true) ?: [];
            return response()->json([
                'ok' => $data['ok'] ?? null,
                'last_run' => $data['last_run'] ?? null,
                'pull_synced' => $data['pull_synced'] ?? 0,
                'push_synced' => $data['push_synced'] ?? 0,
                'error' => $data['error'] ?? null,
                'running' => $data['running'] ?? false,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => null,
                'last_run' => null,
                'error' => 'فشل قراءة الحالة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب قائمة المايجريشنز المتاحة
     */
    public function getMigrations(Request $request): JsonResponse
    {
        try {
            $showExecuted = filter_var($request->get('show_executed', false), FILTER_VALIDATE_BOOLEAN);
            
            $migrationsPath = database_path('migrations');
            $files = File::files($migrationsPath);
            
            // جلب قائمة المايجريشنز المنفذة من قاعدة البيانات
            $executedMigrations = [];
            try {
                if ($this->tableExists(DB::connection(), 'migrations', false)) {
                    $executed = DB::table('migrations')->pluck('migration')->toArray();
                    $executedMigrations = array_map(function($migration) {
                        // إزالة التاريخ من اسم المايجريشن للحصول على الاسم فقط
                        if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_(.+)$/', $migration, $matches)) {
                            return $matches[1];
                        }
                        return $migration;
                    }, $executed);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to get executed migrations', [
                    'error' => $e->getMessage()
                ]);
            }
            
            $migrations = [];
            foreach ($files as $file) {
                $fileName = $file->getFilename();
                // استخراج اسم المايجريشن من اسم الملف (بعد التاريخ)
                if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_(.+?)\.php$/', $fileName, $matches)) {
                    $migrationName = $matches[1];
                    $isExecuted = in_array($migrationName, $executedMigrations);
                    
                    // إخفاء المايجريشنز المنفذة إذا لم يكن show_executed = true
                    if ($isExecuted && !$showExecuted) {
                        continue;
                    }
                    
                    $migrations[] = [
                        'file' => $fileName,
                        'name' => $migrationName,
                        'path' => $file->getPathname(),
                        'date' => date('Y-m-d H:i:s', $file->getMTime()),
                        'executed' => $isExecuted
                    ];
                }
            }
            
            // ترتيب حسب التاريخ (الأحدث أولاً)
            usort($migrations, function($a, $b) {
                return strcmp($b['file'], $a['file']);
            });
            
            return response()->json([
                'migrations' => $migrations,
                'total_executed' => count($executedMigrations),
                'total_pending' => count($migrations)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get migrations', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => $e->getMessage(),
                'migrations' => []
            ], 500);
        }
    }

    /**
     * فحص المايجريشن للتحقق من وجود بيانات قبل التنفيذ
     */
    public function checkMigration(Request $request): JsonResponse
    {
        try {
            $migrationName = $request->get('migration_name');
            
            if (!$migrationName) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم المايجريشن مطلوب'
                ], 422);
            }
            
            // البحث عن ملف المايجريشن
            $migrationsPath = database_path('migrations');
            $files = File::files($migrationsPath);
            
            $migrationFile = null;
            $migrationPath = null;
            foreach ($files as $file) {
                $fileName = $file->getFilename();
                if (str_contains($fileName, $migrationName)) {
                    $migrationFile = $fileName;
                    $migrationPath = $file->getPathname();
                    break;
                }
            }
            
            if (!$migrationFile || !$migrationPath) {
                return response()->json([
                    'success' => false,
                    'error' => 'المايجريشن غير موجود: ' . $migrationName
                ], 404);
            }
            
            // قراءة محتوى المايجريشن للتحقق من الجداول المتأثرة
            $migrationContent = File::get($migrationPath);
            
            // استخراج أسماء الجداول من المايجريشن
            $tables = [];
            
            // البحث عن dropIfExists أو drop
            if (preg_match_all('/dropIfExists\([\'"]([^\'"]+)[\'"]\)|drop\([\'"]([^\'"]+)[\'"]\)/i', $migrationContent, $matches)) {
                $tables = array_merge($tables, array_filter(array_merge($matches[1], $matches[2])));
            }
            
            // البحث عن Schema::drop
            if (preg_match_all('/Schema::drop\([\'"]([^\'"]+)[\'"]\)/i', $migrationContent, $matches)) {
                $tables = array_merge($tables, $matches[1]);
            }
            
            // البحث عن table() في down method
            if (preg_match('/function\s+down\([^)]*\)\s*\{([^}]+)\}/is', $migrationContent, $downMatch)) {
                if (preg_match_all('/table\([\'"]([^\'"]+)[\'"]\)/i', $downMatch[1], $tableMatches)) {
                    $tables = array_merge($tables, $tableMatches[1]);
                }
            }
            
            $tables = array_unique(array_filter($tables));
            
            // التحقق من وجود بيانات في الجداول
            $tablesWithData = [];
            $totalRecords = 0;
            
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    if ($count > 0) {
                        $tablesWithData[] = [
                            'name' => $table,
                            'count' => $count
                        ];
                        $totalRecords += $count;
                    }
                } catch (\Exception $e) {
                    // الجدول غير موجود - لا مشكلة
                }
            }
            
            return response()->json([
                'success' => true,
                'has_data' => count($tablesWithData) > 0,
                'tables_with_data' => $tablesWithData,
                'total_records' => $totalRecords,
                'affected_tables' => $tables,
                'migration_file' => $migrationFile
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check migration', [
                'migration_name' => $request->get('migration_name'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تنفيذ migration محدد حسب الاسم
     */
    public function runMigration(Request $request): JsonResponse
    {
        try {
            $migrationName = $request->get('migration_name');
            $force = filter_var($request->get('force', false), FILTER_VALIDATE_BOOLEAN);
            
            if (!$migrationName) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم المايجريشن مطلوب'
                ], 422);
            }
            
            // البحث عن ملف المايجريشن
            $migrationsPath = database_path('migrations');
            $files = File::files($migrationsPath);
            
            $migrationFile = null;
            $migrationPath = null;
            foreach ($files as $file) {
                $fileName = $file->getFilename();
                if (str_contains($fileName, $migrationName)) {
                    $migrationFile = $fileName;
                    $migrationPath = $file->getPathname();
                    break;
                }
            }
            
            if (!$migrationFile || !$migrationPath) {
                return response()->json([
                    'success' => false,
                    'error' => 'المايجريشن غير موجود: ' . $migrationName
                ], 404);
            }
            
            // فحص وجود بيانات إذا لم يكن force
            if (!$force) {
                $migrationContent = File::get($migrationPath);
                $tables = [];
                
                // البحث عن dropIfExists أو drop
                if (preg_match_all('/dropIfExists\([\'"]([^\'"]+)[\'"]\)|drop\([\'"]([^\'"]+)[\'"]\)/i', $migrationContent, $matches)) {
                    $tables = array_merge($tables, array_filter(array_merge($matches[1], $matches[2])));
                }
                
                // البحث عن Schema::drop
                if (preg_match_all('/Schema::drop\([\'"]([^\'"]+)[\'"]\)/i', $migrationContent, $matches)) {
                    $tables = array_merge($tables, $matches[1]);
                }
                
                // البحث عن table() في down method
                if (preg_match('/function\s+down\([^)]*\)\s*\{([^}]+)\}/is', $migrationContent, $downMatch)) {
                    if (preg_match_all('/table\([\'"]([^\'"]+)[\'"]\)/i', $downMatch[1], $tableMatches)) {
                        $tables = array_merge($tables, $tableMatches[1]);
                    }
                }
                
                $tables = array_unique(array_filter($tables));
                
                // التحقق من وجود بيانات
                foreach ($tables as $table) {
                    try {
                        $count = DB::table($table)->count();
                        if ($count > 0) {
                            return response()->json([
                                'success' => false,
                                'error' => 'يوجد بيانات في الجداول المتأثرة. لا يمكن تنفيذ المايجريشن حفاظاً على البيانات.',
                                'warning' => true,
                                'table' => $table,
                                'record_count' => $count,
                                'message' => "يوجد {$count} سجل في جدول '{$table}'. استخدم force=true إذا كنت متأكداً من الحذف."
                            ], 400);
                        }
                    } catch (\Exception $e) {
                        // الجدول غير موجود - لا مشكلة
                    }
                }
            }
            
            // تنفيذ المايجريشن
            $exitCode = Artisan::call('migrate', [
                '--path' => 'database/migrations/' . $migrationFile,
                '--force' => true
            ]);
            
            $output = Artisan::output();
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تنفيذ المايجريشن بنجاح',
                    'output' => $output,
                    'migration' => $migrationFile
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'فشل تنفيذ المايجريشن',
                    'output' => $output
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to run migration', [
                'migration_name' => $request->get('migration_name'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على قائمة النسخ الاحتياطية
     */
    public function backups(): JsonResponse
    {
        try {
            $backupDir = storage_path('app/backups');
            
            if (!is_dir($backupDir)) {
                return response()->json([
                    'backups' => []
                ]);
            }

            $files = glob($backupDir . '/*.{sql,json}', GLOB_BRACE);
            $backups = [];

            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'date' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }

            // ترتيب حسب التاريخ (الأحدث أولاً)
            usort($backups, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return response()->json([
                'backups' => $backups
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to list backups', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * استعادة نسخة احتياطية
     */
    public function restoreBackup(Request $request, DatabaseSyncService $syncService): JsonResponse
    {
        try {
            $backupFile = $request->get('backup_file');
            
            if (!$backupFile) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم الملف مطلوب'
                ], 400);
            }

            $backupPath = storage_path('app/backups/' . basename($backupFile));
            
            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'النسخة الاحتياطية غير موجودة'
                ], 404);
            }

            // استخدام دالة الاستعادة من DatabaseSyncService
            $success = $syncService->restoreBackup($backupPath);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'تمت استعادة النسخة الاحتياطية بنجاح'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'فشلت استعادة النسخة الاحتياطية'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to restore backup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحميل نسخة احتياطية
     */
    public function downloadBackup(Request $request)
    {
        try {
            $backupFile = $request->get('file');
            
            if (!$backupFile) {
                abort(400, 'اسم الملف مطلوب');
            }

            $backupPath = storage_path('app/backups/' . basename($backupFile));
            
            if (!file_exists($backupPath)) {
                abort(404, 'النسخة الاحتياطية غير موجودة');
            }

            return response()->download($backupPath, basename($backupFile));
        } catch (\Exception $e) {
            Log::error('Failed to download backup', [
                'error' => $e->getMessage()
            ]);

            abort(500, 'فشل تحميل النسخة الاحتياطية');
        }
    }

    /**
     * قراءة محتوى ملف النسخة الاحتياطية لاستخراج أسماء الجداول
     */
    public function getBackupContent(Request $request): JsonResponse
    {
        try {
            $backupFile = $request->get('file');

            if (!$backupFile) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم الملف مطلوب'
                ], 400);
            }

            $backupPath = storage_path('app/backups/' . basename($backupFile));

            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'النسخة الاحتياطية غير موجودة'
                ], 404);
            }

            // قراءة محتوى الملف
            $backupData = json_decode(file_get_contents($backupPath), true);

            if (!$backupData || !isset($backupData['tables'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ملف النسخة الاحتياطية تالف أو غير صحيح'
                ], 400);
            }

            $tables = array_keys($backupData['tables']);

            return response()->json([
                'success' => true,
                'tables' => $tables,
                'total_records' => $backupData['total_records'] ?? 0,
                'tables_count' => count($tables)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to read backup content', [
                'error' => $e->getMessage(),
                'file' => $backupFile ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'فشل في قراءة محتوى النسخة الاحتياطية'
            ], 500);
        }
    }

    /**
     * استعادة جداول محددة من النسخة الاحتياطية
     */
    public function restoreSelectedTables(Request $request, DatabaseSyncService $syncService): JsonResponse
    {
        try {
            $backupFile = $request->get('backup_file');
            $tables = $request->get('tables');

            if (!$backupFile) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم الملف مطلوب'
                ], 400);
            }

            if (!$tables) {
                return response()->json([
                    'success' => false,
                    'error' => 'يجب تحديد الجداول المراد استعادتها'
                ], 400);
            }

            $backupPath = storage_path('app/backups/' . basename($backupFile));

            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'النسخة الاحتياطية غير موجودة'
                ], 404);
            }

            // قراءة محتوى الملف
            $backupData = json_decode(file_get_contents($backupPath), true);

            if (!$backupData || !isset($backupData['tables'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ملف النسخة الاحتياطية تالف أو غير صحيح'
                ], 400);
            }

            $tablesArray = explode(',', $tables);
            $tablesArray = array_map('trim', $tablesArray);

            // التحقق من وجود الجداول المطلوبة
            $availableTables = array_keys($backupData['tables']);
            $missingTables = array_diff($tablesArray, $availableTables);

            if (!empty($missingTables)) {
                return response()->json([
                    'success' => false,
                    'error' => 'الجداول التالية غير موجودة في النسخة الاحتياطية: ' . implode(', ', $missingTables)
                ], 400);
            }

            // إنشاء بيانات استعادة محدودة بالجداول المحددة
            $restoreData = [
                'created_at' => $backupData['created_at'] ?? now()->toDateTimeString(),
                'database' => $backupData['database'] ?? config('database.connections.mysql.database'),
                'tables' => array_intersect_key($backupData['tables'], array_flip($tablesArray))
            ];

            // كتابة ملف مؤقت للاستعادة
            $tempBackupPath = storage_path('app/backups/temp_restore_' . time() . '.json');
            file_put_contents($tempBackupPath, json_encode($restoreData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // استعادة البيانات المحددة
            $success = $syncService->restoreBackup($tempBackupPath);

            // حذف الملف المؤقت
            if (file_exists($tempBackupPath)) {
                unlink($tempBackupPath);
            }

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "تمت استعادة " . count($tablesArray) . " جدول بنجاح",
                    'tables_restored' => $tablesArray
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'فشلت عملية الاستعادة'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to restore selected tables', [
                'error' => $e->getMessage(),
                'backup_file' => $backupFile ?? 'unknown',
                'tables' => $tables ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'فشلت استعادة الجداول المحددة'
            ], 500);
        }
    }

    /**
     * حذف نسخة احتياطية
     */
    public function deleteBackup(Request $request): JsonResponse
    {
        try {
            $backupFile = $request->get('file');
            
            if (!$backupFile) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم الملف مطلوب'
                ], 400);
            }

            $backupPath = storage_path('app/backups/' . basename($backupFile));
            
            if (!file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'النسخة الاحتياطية غير موجودة'
                ], 404);
            }

            // حذف الملف
            if (unlink($backupPath)) {
                Log::info('تم حذف النسخة الاحتياطية', [
                    'file' => $backupFile,
                    'user' => optional($request->user())->id ?? 'guest'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "تم حذف النسخة الاحتياطية {$backupFile} بنجاح"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'فشل حذف الملف'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete backup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تفريغ جدول في SQLite (حذف جميع السجلات)
     */
    public function truncateTable(string $tableName, Request $request): JsonResponse
    {
        try {
            // التحقق من أن اسم الجدول آمن
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم الجدول غير صالح'
                ], 400);
            }

            // فقط SQLite مسموح (لحماية MySQL)
            $db = DB::connection('sync_sqlite');

            // التحقق من وجود الجدول
            if (!$this->tableExists($db, $tableName, true)) {
                return response()->json([
                    'success' => false,
                    'error' => 'الجدول غير موجود'
                ], 404);
            }

            // التحقق من أن الجدول ليس جدول نظام
            $systemTables = ['migrations', 'sync_metadata', 'sqlite_master', 'sqlite_sequence'];
            if (in_array($tableName, $systemTables)) {
                return response()->json([
                    'success' => false,
                    'error' => 'لا يمكن تفريغ جداول النظام'
                ], 403);
            }

            // تفريغ الجدول
            $db->table($tableName)->truncate();

            // إعادة تعيين AUTO_INCREMENT في SQLite
            $db->statement("DELETE FROM sqlite_sequence WHERE name = '{$tableName}'");

            Log::info('تم تفريغ الجدول', [
                'table' => $tableName,
                'connection' => 'sync_sqlite',
                'user' => optional($request->user())->id ?? 'guest'
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تفريغ الجدول {$tableName} بنجاح"
            ]);

        } catch (\Exception $e) {
            Log::error('فشل تفريغ الجدول', [
                'table' => $tableName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف جدول من SQLite
     */
    public function deleteTable(string $tableName, Request $request): JsonResponse
    {
        try {
            // التحقق من أن اسم الجدول آمن
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                return response()->json([
                    'success' => false,
                    'error' => 'اسم الجدول غير صالح'
                ], 400);
            }

            // فقط SQLite مسموح (لحماية MySQL)
            $db = DB::connection('sync_sqlite');

            // التحقق من وجود الجدول
            if (!$this->tableExists($db, $tableName, true)) {
                return response()->json([
                    'success' => false,
                    'error' => 'الجدول غير موجود'
                ], 404);
            }

            // التحقق من أن الجدول ليس جدول نظام
            $systemTables = ['migrations', 'sync_metadata', 'sqlite_master', 'sqlite_sequence'];
            if (in_array($tableName, $systemTables)) {
                return response()->json([
                    'success' => false,
                    'error' => 'لا يمكن حذف جداول النظام'
                ], 403);
            }

            // حذف الجدول
            $db->statement("DROP TABLE IF EXISTS `{$tableName}`");

            // حذف من sqlite_sequence إذا كان موجوداً
            $db->statement("DELETE FROM sqlite_sequence WHERE name = '{$tableName}'");

            Log::warning('تم حذف الجدول', [
                'table' => $tableName,
                'connection' => 'sync_sqlite',
                'user' => optional($request->user())->id ?? 'guest'
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم حذف الجدول {$tableName} بنجاح"
            ]);

        } catch (\Exception $e) {
            Log::error('فشل حذف الجدول', [
                'table' => $tableName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من وجود جدول
     */
    protected function tableExists($db, string $tableName, bool $isSQLite = null): bool
    {
        try {
            if ($this->isConnectionSqlite($db)) {
                $result = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);
                return !empty($result);
            }

            $database = config('database.connections.mysql.database');
            $result = $db->select(
                "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [$database, $tableName]
            );
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }
}


