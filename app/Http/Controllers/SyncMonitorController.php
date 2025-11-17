<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DatabaseSyncService;

class SyncMonitorController extends Controller
{
    /**
     * إرجاع قائمة الجداول مع عدد السجلات واتصال القاعدة الحالي.
     */
    public function tables(Request $request): JsonResponse
    {
        try {
            // السماح باختيار الاتصال يدوياً (force_connection)
            $forceConnection = $request->get('force_connection');
            
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
     * عرض تفاصيل جدول معين (الأعمدة + بيانات محدودة).
     */
    public function tableDetails(string $tableName, Request $request): JsonResponse
    {
        try {
            // السماح باختيار الاتصال يدوياً (force_connection)
            $forceConnection = $request->get('force_connection');
            
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
     * جلب أسماء الجداول من الاتصال الحالي (MySQL أو SQLite).
     */
    protected function fetchTableNames($db, bool $isUsingFallback): array
    {
        if ($isUsingFallback) {
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

        if ($isUsingFallback) {
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
            if ($isUsingFallback) {
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
            $sessionKey = 'sync_progress_' . $request->user()->id;
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
                // من MySQL إلى SQLite (آمن دائماً)
                $results = $syncService->syncFromMySQLToSQLite(
                    $tablesArray,
                    filter_var($forceFullSync, FILTER_VALIDATE_BOOLEAN) // Force Full Sync
                );
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
            $sessionKey = 'sync_progress_' . ($request->user()->id ?? 'guest');
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
            $sessionKey = 'sync_progress_' . ($request->user()->id ?? 'guest');
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
                    'user' => $request->user()->id ?? 'guest'
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
                'user' => $request->user()->id ?? 'guest'
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
                'user' => $request->user()->id ?? 'guest'
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
    protected function tableExists($db, string $tableName, bool $isSQLite): bool
    {
        try {
            if ($isSQLite) {
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


