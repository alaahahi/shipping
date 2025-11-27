<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Exception;

class DatabaseSyncService
{
    /**
     * قائمة الجداول المستثناة من المزامنة
     * هذه الجداول لا تحتاج إلى مزامنة لأنها:
     * - جداول نظام Laravel (migrations, jobs, etc.)
     * - جداول أدوات التطوير (telescope, debugbar, etc.)
     * - جداول مؤقتة أو للـ logging فقط
     */
    protected array $excludedTables = [
        // جداول Laravel النظامية
        'migrations',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'cache',
        'cache_locks',
        'password_resets', // جدول مؤقت لإعادة تعيين كلمات المرور
        
        // جداول Telescope (أدوات التطوير)
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
        
        // جداول SQLite النظامية
        'sqlite_sequence',
        'sqlite_master',
        
        // جداول أخرى للـ logging والتطوير
        'sync_metadata', // جدول metadata المزامنة نفسه
        'sync_jobs', // جدول مهام المزامنة
    ];

    /**
     * إنشاء نسخة احتياطية من قاعدة البيانات قبل المزامنة
     */
    protected function createBackup(): ?string
    {
        // استخدام النسخ الاحتياطي اليدوي دائماً (أكثر موثوقية)
        // لأن mysqldump قد لا يكون متاحاً في جميع البيئات
        return $this->createManualBackup();
    }
    
    /**
     * نسخ احتياطي يدوي للجداول المهمة
     */
    protected function createManualBackup(): ?string
    {
        try {
            $mysqlDb = DB::connection('mysql');
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $backupFile = $backupDir . '/backup_' . date('Y-m-d_His') . '.json';
            
            // نسخ احتياطي للجداول المهمة فقط
            $importantTables = ['car_contract', 'car', 'users', 'transactions', 'wallets', 'accounting_journals', 'accounting_ledgers'];
            $backupData = [
                'created_at' => now()->toDateTimeString(),
                'database' => config('database.connections.mysql.database'),
                'tables' => []
            ];
            
            $totalRecords = 0;
            foreach ($importantTables as $table) {
                try {
                    $records = $mysqlDb->table($table)->get()->map(function ($item) {
                        return (array) $item;
                    })->toArray();
                    
                    $backupData['tables'][$table] = $records;
                    $totalRecords += count($records);
                    
                    Log::debug("Backed up table: {$table}", ['count' => count($records)]);
                } catch (Exception $e) {
                    Log::warning("Failed to backup table: {$table}", ['error' => $e->getMessage()]);
                    $backupData['tables'][$table] = [];
                }
            }
            
            $backupData['total_records'] = $totalRecords;
            $backupData['tables_count'] = count($backupData['tables']);
            
            // كتابة البيانات إلى الملف
            $jsonData = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if (file_put_contents($backupFile, $jsonData) === false) {
                throw new Exception("Failed to write backup file");
            }
            
            // التحقق من أن الملف تم إنشاؤه بشكل صحيح
            if (!file_exists($backupFile) || filesize($backupFile) === 0) {
                throw new Exception("Backup file is empty or not created");
            }
            
            Log::info('✅ تم إنشاء نسخة احتياطية يدوية', [
                'file' => $backupFile, 
                'tables' => array_keys($backupData['tables']),
                'total_records' => $totalRecords,
                'file_size' => filesize($backupFile)
            ]);
            
            return $backupFile;
        } catch (Exception $e) {
            Log::error('❌ فشل النسخ الاحتياطي اليدوي', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * استعادة النسخة الاحتياطية
     */
    public function restoreBackup(string $backupFile): bool
    {
        try {
            if (!file_exists($backupFile)) {
                Log::error('❌ ملف النسخة الاحتياطية غير موجود', ['file' => $backupFile]);
                return false;
            }
            
            // إذا كان ملف SQL
            if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'sql') {
                $mysqlDb = DB::connection('mysql');
                $database = config('database.connections.mysql.database');
                $host = config('database.connections.mysql.host');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');
                
                $command = sprintf(
                    'mysql -h %s -u %s -p%s %s < %s 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($backupFile)
                );
                
                exec($command, $output, $returnVar);
                
                if ($returnVar === 0) {
                    Log::info('✅ تم استعادة النسخة الاحتياطية من SQL', ['file' => $backupFile]);
                    return true;
                }
            } else {
                // استعادة من JSON
                $backupData = json_decode(file_get_contents($backupFile), true);
                $mysqlDb = DB::connection('mysql');
                
                DB::transaction(function () use ($mysqlDb, $backupData) {
                    foreach ($backupData as $table => $rows) {
                        // حذف البيانات الحالية
                        $mysqlDb->table($table)->truncate();
                        // إعادة إدراج البيانات
                        if (!empty($rows)) {
                            $mysqlDb->table($table)->insert($rows);
                        }
                    }
                });
                
                Log::info('✅ تم استعادة النسخة الاحتياطية من JSON', ['file' => $backupFile]);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('❌ فشل استعادة النسخة الاحتياطية', ['error' => $e->getMessage(), 'file' => $backupFile]);
            return false;
        }
    }
    
    /**
     * مزامنة جميع الجداول من MySQL إلى SQLite
     * 
     * @param array|null $tables الجداول المطلوب مزامنتها
     * @param bool $forceFullSync مزامنة كاملة (تجاهل metadata)
     */
    public function syncFromMySQLToSQLite(array $tables = null, bool $forceFullSync = false): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total_synced' => 0
        ];

        try {
            $mysqlDb = DB::connection('mysql');
            $sqliteDb = DB::connection('sync_sqlite');

            // تعطيل foreign key constraints في بداية العملية
            $sqliteDb->statement('PRAGMA foreign_keys = OFF');

            // الحصول على قائمة الجداول
            if ($tables === null) {
                $tables = $this->getAllTables($mysqlDb, false);
            }

            // تصفية الجداول المستثناة
            $tables = $this->filterExcludedTables($tables);

            foreach ($tables as $tableName) {
                try {
                    $synced = $this->syncTable($mysqlDb, $sqliteDb, $tableName, $forceFullSync);
                    $results['success'][$tableName] = $synced;
                    $results['total_synced'] += $synced;
                } catch (Exception $e) {
                    $results['failed'][$tableName] = $e->getMessage();
                    Log::error("Failed to sync table {$tableName}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // إعادة تفعيل foreign key constraints في نهاية العملية
            $sqliteDb->statement('PRAGMA foreign_keys = ON');
        } catch (Exception $e) {
            // التأكد من إعادة تفعيل foreign keys حتى في حالة الخطأ
            try {
                $sqliteDb = DB::connection('sync_sqlite');
                $sqliteDb->statement('PRAGMA foreign_keys = ON');
            } catch (\Exception $e2) {
                // تجاهل الخطأ
            }

            Log::error('Database sync failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        return $results;
    }

    /**
     * مزامنة جدول واحد من MySQL إلى SQLite (ذكية - فقط السجلات الجديدة والمحدثة)
     */
    protected function syncTable($mysqlDb, $sqliteDb, string $tableName, bool $forceFullSync = false): int
    {
        // التحقق من وجود الجدول في MySQL
        if (!$this->tableExists($mysqlDb, $tableName, false)) {
            throw new Exception("Table {$tableName} does not exist in MySQL");
        }

        // إنشاء الجدول في SQLite إذا لم يكن موجوداً
        $this->ensureTableExists($mysqlDb, $sqliteDb, $tableName);

        // التحقق من وجود عمود id و updated_at
        $hasId = $this->hasColumn($mysqlDb, $tableName, 'id');
        $hasUpdatedAt = $this->hasColumn($mysqlDb, $tableName, 'updated_at');

        // جلب أكبر ID و last updated_at من SQLite المحلي (البيانات الفعلية)
        $localMaxId = 0;
        $localMaxUpdatedAt = null;
        
        if ($hasId && $this->tableExists($sqliteDb, $tableName, true)) {
            try {
                $localMaxIdRecord = $sqliteDb->table($tableName)->max('id');
                $localMaxId = $localMaxIdRecord ? (int)$localMaxIdRecord : 0;
            } catch (\Exception $e) {
                Log::warning("Failed to get max ID from local table", ['table' => $tableName, 'error' => $e->getMessage()]);
            }
        }

        if ($hasUpdatedAt && $this->tableExists($sqliteDb, $tableName, true)) {
            try {
                $localMaxUpdatedAtRecord = $sqliteDb->table($tableName)->max('updated_at');
                $localMaxUpdatedAt = $localMaxUpdatedAtRecord ? $localMaxUpdatedAtRecord : null;
            } catch (\Exception $e) {
                Log::warning("Failed to get max updated_at from local table", ['table' => $tableName, 'error' => $e->getMessage()]);
            }
        }

        // جلب معلومات المزامنة السابقة من metadata
        $metadata = $this->getSyncMetadata($tableName, 'down');
        $metadataLastSyncedId = $forceFullSync ? 0 : ($metadata['last_synced_id'] ?? 0);
        $metadataLastUpdatedAt = $forceFullSync ? null : ($metadata['last_updated_at'] ?? null);

        // استخدام القيمة الأكبر بين metadata والبيانات الفعلية المحلية (لضمان عدم فقدان البيانات)
        $lastSyncedId = max($localMaxId, $metadataLastSyncedId);
        $lastUpdatedAt = null;
        
        if ($localMaxUpdatedAt && $metadataLastUpdatedAt) {
            // استخدام التاريخ الأكبر
            $lastUpdatedAt = strtotime($localMaxUpdatedAt) > strtotime($metadataLastUpdatedAt) 
                ? $localMaxUpdatedAt 
                : $metadataLastUpdatedAt;
        } elseif ($localMaxUpdatedAt) {
            $lastUpdatedAt = $localMaxUpdatedAt;
        } elseif ($metadataLastUpdatedAt) {
            $lastUpdatedAt = $metadataLastUpdatedAt;
        }

        Log::info("Smart sync metadata", [
            'table' => $tableName,
            'local_max_id' => $localMaxId,
            'metadata_last_synced_id' => $metadataLastSyncedId,
            'using_last_synced_id' => $lastSyncedId,
            'local_max_updated_at' => $localMaxUpdatedAt,
            'metadata_last_updated_at' => $metadataLastUpdatedAt,
            'using_last_updated_at' => $lastUpdatedAt,
            'force_full_sync' => $forceFullSync
        ]);

        // بناء الاستعلام الذكي - فقط السجلات الجديدة أو المحدثة
        $query = $mysqlDb->table($tableName);
        
        if (!$forceFullSync && ($hasId && $lastSyncedId > 0 || $hasUpdatedAt && $lastUpdatedAt)) {
            // التحقق من السجلات المفقودة (IDs موجودة في MySQL لكن غير موجودة في SQLite)
            $missingIds = [];
            if ($hasId && $this->tableExists($sqliteDb, $tableName, true)) {
                try {
                    // جلب جميع IDs من MySQL
                    $mysqlIds = $mysqlDb->table($tableName)->pluck('id')->toArray();
                    // جلب جميع IDs من SQLite
                    $sqliteIds = $sqliteDb->table($tableName)->pluck('id')->toArray();
                    // العثور على IDs المفقودة في SQLite
                    $missingIds = array_diff($mysqlIds, $sqliteIds);
                    
                    if (!empty($missingIds)) {
                        Log::info("Found missing IDs in SQLite", [
                            'table' => $tableName,
                            'missing_count' => count($missingIds),
                            'missing_ids' => array_slice($missingIds, 0, 10) // أول 10 فقط للـ log
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to check missing IDs", [
                        'table' => $tableName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $query->where(function($q) use ($hasId, $lastSyncedId, $hasUpdatedAt, $lastUpdatedAt, $missingIds) {
                // 1. السجلات الجديدة (ID > last_synced_id)
                if ($hasId && $lastSyncedId > 0) {
                    $q->where('id', '>', $lastSyncedId);
                }
                
                // 2. السجلات المفقودة (موجودة في MySQL لكن غير موجودة في SQLite)
                if ($hasId && !empty($missingIds)) {
                    $q->orWhereIn('id', $missingIds);
                }
                
                // 3. السجلات التي تم تحديثها بعد آخر مزامنة (مع ID <= last_synced_id)
                if ($hasUpdatedAt && $lastUpdatedAt) {
                    $q->orWhere(function($subQ) use ($lastUpdatedAt, $lastSyncedId, $hasId) {
                        $subQ->where('updated_at', '>', $lastUpdatedAt);
                        // تحديث السجلات الموجودة فقط (ID <= last_synced_id)
                        if ($hasId && $lastSyncedId > 0) {
                            $subQ->where('id', '<=', $lastSyncedId);
                        }
                    });
                }
            });
        }

        // جلب البيانات (بحد أقصى 10000 سجل في كل مرة لتجنب timeout)
        $data = $query->limit(10000)->get()->map(function ($item) {
            return (array) $item;
        })->toArray();
        
        if (!empty($data)) {
            Log::info("Smart sync: Found records to sync", [
                'table' => $tableName,
                'count' => count($data),
                'first_id' => $hasId ? ($data[0]['id'] ?? null) : null,
                'last_id' => $hasId ? (end($data)['id'] ?? null) : null
            ]);
        }

        if (empty($data)) {
            return 0;
        }

        // استخدام upsert (insert or update) بدلاً من truncate
        // تحسين الأداء: استخدام upsert مباشرة بدلاً من فحص كل سجل
        $chunkSize = 500;
        $chunks = array_chunk($data, $chunkSize);
        $totalUpserted = 0;
        $totalUpdated = 0;
        $totalInserted = 0;
        $maxId = $lastSyncedId;
        $maxUpdatedAt = $lastUpdatedAt;

        foreach ($chunks as $chunkIndex => $chunk) {
            // التأكد من تحويل جميع العناصر إلى arrays
            $insertData = [];
            foreach ($chunk as $item) {
                $insertData[] = is_array($item) ? $item : (array) $item;
            }

            // التحقق من السجلات الموجودة مسبقاً (batch check)
            $existingIds = [];
            if ($hasId) {
                $ids = array_filter(array_column($insertData, 'id'));
                if (!empty($ids)) {
                    $existingIds = $sqliteDb->table($tableName)
                        ->whereIn('id', $ids)
                        ->pluck('id')
                        ->toArray();
                }
            }

            foreach ($insertData as $row) {
                try {
                    // التأكد من أن $row هو array
                    if (!is_array($row)) {
                        $row = (array) $row;
                    }
                    
                    // استخدام upsert بناءً على ID
                    if ($hasId && isset($row['id'])) {
                        $rowId = (int)$row['id'];
                        $isExisting = in_array($rowId, $existingIds);
                        
                        if ($isExisting) {
                            // تحديث السجل الموجود
                            $updateData = $row;
                            unset($updateData['id']);
                            
                            // التحقق من updated_at قبل التحديث (تحديث فقط إذا كان أحدث)
                            if ($hasUpdatedAt && isset($row['updated_at'])) {
                                $currentRecord = $sqliteDb->table($tableName)
                                    ->where('id', $rowId)
                                    ->first();
                                
                                if ($currentRecord) {
                                    // تحويل $currentRecord إلى array إذا كان object
                                    $currentRecordArray = is_array($currentRecord) ? $currentRecord : (array) $currentRecord;
                                    
                                    if (isset($currentRecordArray['updated_at'])) {
                                        $currentUpdatedAt = strtotime($currentRecordArray['updated_at']);
                                        $newUpdatedAt = strtotime($row['updated_at']);
                                        
                                        // تحديث فقط إذا كان السجل الجديد أحدث
                                        if ($newUpdatedAt <= $currentUpdatedAt) {
                                            continue; // تخطي السجل القديم
                                        }
                                    }
                                }
                            }
                            
                            $sqliteDb->table($tableName)
                                ->where('id', $rowId)
                                ->update($updateData);
                            $totalUpdated++;
                        } else {
                            // إدراج سجل جديد
                            $sqliteDb->table($tableName)->insert($row);
                            $totalInserted++;
                            $existingIds[] = $rowId; // إضافة للقائمة لتجنب التحقق مرة أخرى
                        }
                        
                        // تتبع أكبر ID
                        if ($rowId > $maxId) {
                            $maxId = $rowId;
                        }
                    } else {
                        // إذا لم يكن هناك ID، إدراج مباشر
                        $sqliteDb->table($tableName)->insert($row);
                        $totalInserted++;
                    }
                    
                    // تتبع آخر updated_at
                    if ($hasUpdatedAt && isset($row['updated_at'])) {
                        $rowUpdatedAt = strtotime($row['updated_at']);
                        $maxUpdatedAtTimestamp = $maxUpdatedAt ? strtotime($maxUpdatedAt) : 0;
                        if ($rowUpdatedAt > $maxUpdatedAtTimestamp) {
                            $maxUpdatedAt = $row['updated_at'];
                        }
                    }
                    
                    $totalUpserted++;
                } catch (Exception $e) {
                    Log::warning("Failed to upsert row in syncTable", [
                        'table' => $tableName,
                        'row_id' => $row['id'] ?? 'no_id',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
            
            // Log progress every 5 chunks
            if (($chunkIndex + 1) % 5 == 0) {
                Log::info("Sync progress", [
                    'table' => $tableName,
                    'chunks_processed' => $chunkIndex + 1,
                    'total_chunks' => count($chunks),
                    'total_upserted' => $totalUpserted
                ]);
            }
        }

        // تحديث metadata بالنتائج النهائية
        if ($totalUpserted > 0 || $lastSyncedId > 0) {
            // استخدام أكبر ID فعلي من SQLite المحلي أو من المزامنة
            $finalMaxId = max($localMaxId, $maxId);
            $finalMaxUpdatedAt = null;
            
            if ($maxUpdatedAt) {
                $finalMaxUpdatedAt = $maxUpdatedAt;
            } elseif ($localMaxUpdatedAt) {
                $finalMaxUpdatedAt = $localMaxUpdatedAt;
            }
            
            $this->updateSyncMetadata($tableName, 'down', $finalMaxId, $finalMaxUpdatedAt, $totalUpserted);
            
            Log::info("Smart sync completed", [
                'table' => $tableName,
                'inserted' => $totalInserted,
                'updated' => $totalUpdated,
                'total' => $totalUpserted,
                'final_max_id' => $finalMaxId,
                'final_max_updated_at' => $finalMaxUpdatedAt
            ]);
        } else {
            Log::info("Smart sync: No new records", ['table' => $tableName]);
        }

        return $totalUpserted;
    }

    /**
     * التأكد من وجود الجدول في SQLite مع نفس البنية
     */
    protected function ensureTableExists($mysqlDb, $sqliteDb, string $tableName): void
    {
        if ($this->tableExists($sqliteDb, $tableName, true)) {
            return;
        }

        // جلب بنية الجدول من MySQL
        $columns = $this->getTableStructure($mysqlDb, $tableName, false);

        // إنشاء الجدول في SQLite
        $this->createTableFromStructure($sqliteDb, $tableName, $columns);
    }

    /**
     * التأكد من وجود الجدول في MySQL مع نفس البنية من SQLite
     */
    protected function ensureTableExistsReverse($sqliteDb, $mysqlDb, string $tableName): void
    {
        if ($this->tableExists($mysqlDb, $tableName, false)) {
            return;
        }

        Log::info("Creating new table in MySQL from SQLite structure", ['table' => $tableName]);

        // جلب بنية الجدول من SQLite
        $columns = $this->getTableStructureFromSQLite($sqliteDb, $tableName);

        // إنشاء الجدول في MySQL
        $this->createTableInMySQLFromSQLite($mysqlDb, $tableName, $columns);
    }

    /**
     * جلب بنية الجدول من SQLite
     */
    protected function getTableStructureFromSQLite($sqliteDb, string $tableName): array
    {
        return $sqliteDb->select("PRAGMA table_info(`{$tableName}`)");
    }

    /**
     * إنشاء جدول في MySQL من بنية SQLite
     */
    protected function createTableInMySQLFromSQLite($mysqlDb, string $tableName, array $columns): void
    {
        // التحقق من أن اسم الجدول آمن
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new Exception("Invalid table name: {$tableName}");
        }

        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (";
        $columnDefs = [];
        $primaryKeyFound = false;

        foreach ($columns as $column) {
            // تحويل إلى array إذا كان object لتسهيل الوصول
            $col = is_array($column) ? $column : (array) $column;
            $name = $col['name'] ?? $column->name ?? null;
            if (!$name) continue;
            
            $sqliteType = $col['type'] ?? $column->type ?? 'TEXT';
            $type = $this->mapSQLiteTypeToMySQL($sqliteType);
            $notnull = $col['notnull'] ?? $column->notnull ?? 0;
            $nullable = $notnull == 0 ? ' NULL' : ' NOT NULL';
            
            // معالجة default values
            $default = '';
            $dfltValue = $col['dflt_value'] ?? $column->dflt_value ?? null;
            if ($dfltValue !== null && strtoupper(trim($dfltValue)) !== 'NULL') {
                $defaultValue = trim($dfltValue, "'\"");
                // التحقق إذا كان رقم
                if (is_numeric($defaultValue)) {
                    $default = " DEFAULT {$defaultValue}";
                } elseif (in_array(strtoupper($defaultValue), ['CURRENT_TIMESTAMP', 'CURRENT_DATE', 'CURRENT_TIME'])) {
                    $default = " DEFAULT " . strtoupper($defaultValue);
                } else {
                    $defaultValue = str_replace("'", "''", $defaultValue);
                    $default = " DEFAULT '{$defaultValue}'";
                }
            }
            
            // إضافة PRIMARY KEY إذا كان pk = 1
            $pk = $col['pk'] ?? $column->pk ?? 0;
            if ($pk == 1 && !$primaryKeyFound) {
                // في MySQL، نستخدم AUTO_INCREMENT للـ PRIMARY KEY إذا كان INTEGER
                if (strtoupper($type) === 'INT' || strtoupper($type) === 'BIGINT') {
                    $columnDefs[] = "`{$name}` {$type} PRIMARY KEY AUTO_INCREMENT{$nullable}{$default}";
                } else {
                    $columnDefs[] = "`{$name}` {$type} PRIMARY KEY{$nullable}{$default}";
                }
                $primaryKeyFound = true;
            } else {
                $columnDefs[] = "`{$name}` {$type}{$nullable}{$default}";
            }
        }

        $sql .= implode(', ', $columnDefs);
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $mysqlDb->statement($sql);
            Log::info("Successfully created table in MySQL", ['table' => $tableName]);
        } catch (Exception $e) {
            Log::error("Failed to create table in MySQL", [
                'table' => $tableName,
                'error' => $e->getMessage(),
                'sql' => $sql
            ]);
            throw $e;
        }
    }

    /**
     * تحويل أنواع SQLite إلى MySQL
     */
    protected function mapSQLiteTypeToMySQL(string $sqliteType): string
    {
        // SQLite قد يخزن الأنواع بأحرف صغيرة أو كبيرة، أو مع معلومات إضافية
        $type = strtoupper(trim($sqliteType));
        
        // إزالة أي معلومات إضافية مثل (255) من النوع
        $baseType = preg_replace('/\([^)]*\)/', '', $type);
        $baseType = trim($baseType);

        // أنواع رقمية
        if (in_array($baseType, ['INTEGER', 'INT'])) {
            // محاولة استخراج الحجم إذا كان موجوداً
            if (preg_match('/\((\d+)\)/', $type, $matches)) {
                $size = (int)$matches[1];
                if ($size <= 11) {
                    return 'INT';
                } elseif ($size <= 20) {
                    return 'BIGINT';
                }
            }
            return 'INT';
        }
        if ($baseType === 'BIGINT') {
            return 'BIGINT';
        }
        if (in_array($baseType, ['TINYINT', 'SMALLINT', 'MEDIUMINT'])) {
            return $baseType;
        }

        // أنواع فاصلة عائمة
        if (in_array($baseType, ['REAL', 'FLOAT', 'DOUBLE'])) {
            return 'DOUBLE';
        }
        if (in_array($baseType, ['NUMERIC', 'DECIMAL'])) {
            // محاولة استخراج الدقة
            if (preg_match('/\((\d+),(\d+)\)/', $type, $matches)) {
                return "DECIMAL({$matches[1]},{$matches[2]})";
            }
            return 'DECIMAL(10,2)';
        }

        // أنواع نصية
        if ($baseType === 'TEXT') {
            return 'TEXT';
        }
        if ($baseType === 'VARCHAR' || $baseType === 'CHAR') {
            // محاولة استخراج الحجم
            if (preg_match('/\((\d+)\)/', $type, $matches)) {
                $length = min((int)$matches[1], 65535);
                if ($length <= 255) {
                    return "VARCHAR({$length})";
                } else {
                    return 'TEXT';
                }
            }
            return 'VARCHAR(255)';
        }

        // أنواع تاريخ ووقت
        if (in_array($baseType, ['DATE', 'TIME', 'DATETIME', 'TIMESTAMP'])) {
            return $baseType;
        }

        // أنواع ثنائية
        if ($baseType === 'BLOB') {
            return 'BLOB';
        }

        // افتراضي - نص (SQLite يستخدم TEXT كافتراضي)
        return 'TEXT';
    }

    /**
     * جلب بنية الجدول
     */
    protected function getTableStructure($db, string $tableName, bool $isSQLite): array
    {
        $database = config('database.connections.mysql.database');
        
        if ($isSQLite) {
            return $db->select("PRAGMA table_info(`{$tableName}`)");
        }

        return $db->select("
            SELECT 
                COLUMN_NAME as name,
                DATA_TYPE as type,
                IS_NULLABLE as nullable,
                COLUMN_DEFAULT as default_value,
                CHARACTER_MAXIMUM_LENGTH as length
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$database, $tableName]);
    }

    /**
     * إنشاء جدول في SQLite من البنية
     */
    protected function createTableFromStructure($sqliteDb, string $tableName, array $columns): void
    {
        // التحقق من أن اسم الجدول آمن
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new Exception("Invalid table name: {$tableName}");
        }

        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (";
        $columnDefs = [];
        $primaryKeyFound = false;

        foreach ($columns as $column) {
            $name = $column->name;
            $type = $this->mapMySQLTypeToSQLite($column->type, $column->length ?? null);
            $nullable = $column->nullable === 'YES' ? '' : ' NOT NULL';
            
            // معالجة default values
            $default = '';
            if ($column->default_value !== null) {
                if (is_numeric($column->default_value)) {
                    $default = " DEFAULT {$column->default_value}";
                } else {
                    $defaultValue = str_replace("'", "''", $column->default_value);
                    $default = " DEFAULT '{$defaultValue}'";
                }
            }
            
            // إضافة PRIMARY KEY إذا كان اسم العمود هو 'id'
            if ($name === 'id' && !$primaryKeyFound) {
                $columnDefs[] = "`{$name}` {$type} PRIMARY KEY{$nullable}{$default}";
                $primaryKeyFound = true;
            } else {
                $columnDefs[] = "`{$name}` {$type}{$nullable}{$default}";
            }
        }

        $sql .= implode(', ', $columnDefs);
        $sql .= ")";

        $sqliteDb->statement($sql);
    }

    /**
     * تحويل أنواع MySQL إلى SQLite
     */
    protected function mapMySQLTypeToSQLite(string $mysqlType, ?int $length = null): string
    {
        $type = strtolower($mysqlType);

        // أنواع رقمية
        if (in_array($type, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint'])) {
            return 'INTEGER';
        }

        // أنواع فاصلة عائمة
        if (in_array($type, ['float', 'double', 'decimal', 'numeric'])) {
            return 'REAL';
        }

        // أنواع نصية
        if (in_array($type, ['varchar', 'char', 'text', 'tinytext', 'mediumtext', 'longtext'])) {
            return 'TEXT';
        }

        // أنواع تاريخ ووقت
        if (in_array($type, ['date', 'time', 'datetime', 'timestamp'])) {
            return 'TEXT';
        }

        // أنواع ثنائية
        if (in_array($type, ['blob', 'tinyblob', 'mediumblob', 'longblob', 'binary', 'varbinary'])) {
            return 'BLOB';
        }

        // افتراضي
        return 'TEXT';
    }

    /**
     * التحقق من وجود جدول
     */
    protected function tableExists($db, string $tableName, bool $isSQLite): bool
    {
        if ($isSQLite) {
            $result = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name = ?", [$tableName]);
            return !empty($result);
        }

        $database = config('database.connections.mysql.database');
        $result = $db->select(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            [$database, $tableName]
        );
        return !empty($result);
    }

    /**
     * الحصول على جميع الجداول
     */
    protected function getAllTables($db, bool $isSQLite): array
    {
        if ($isSQLite) {
            $tables = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            return array_map(fn($t) => $t->name, $tables);
        }

        $database = config('database.connections.mysql.database');
        $tables = $db->select(
            "SELECT TABLE_NAME as name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME",
            [$database]
        );
        return array_map(fn($t) => $t->name, $tables);
    }

    /**
     * التحقق من وجود عمود في الجدول
     */
    protected function hasColumn($db, string $tableName, string $columnName): bool
    {
        try {
            $driverName = $db->getConfig('driver') ?? $db->getDriverName();
            if ($driverName === 'sqlite') {
                $columns = $db->select("PRAGMA table_info({$tableName})");
                foreach ($columns as $column) {
                    if ($column->name === $columnName) {
                        return true;
                    }
                }
                return false;
            } else {
                $database = config('database.connections.mysql.database');
                $result = $db->select(
                    "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                    [$database, $tableName, $columnName]
                );
                return !empty($result);
            }
        } catch (Exception $e) {
            Log::warning("Failed to check column existence", [
                'table' => $tableName,
                'column' => $columnName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * الحصول على metadata المزامنة
     */
    protected function getSyncMetadata(string $tableName, string $direction): array
    {
        try {
            // استخدام MySQL كقاعدة بيانات للـ metadata
            $metadata = DB::connection('mysql')->table('sync_metadata')
                ->where('table_name', $tableName)
                ->where('direction', $direction)
                ->first();

            if ($metadata) {
                return [
                    'last_synced_id' => $metadata->last_synced_id ?? 0,
                    'last_synced_at' => $metadata->last_synced_at,
                    'last_updated_at' => $metadata->last_updated_at,
                    'total_synced' => $metadata->total_synced ?? 0
                ];
            }
        } catch (Exception $e) {
            // إذا لم يكن الجدول موجوداً بعد، نعيد القيم الافتراضية
            Log::debug("Sync metadata table not found or error", [
                'table' => $tableName,
                'direction' => $direction,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'last_synced_id' => 0,
            'last_synced_at' => null,
            'last_updated_at' => null,
            'total_synced' => 0
        ];
    }

    /**
     * تحديث metadata المزامنة
     */
    protected function updateSyncMetadata(string $tableName, string $direction, int $lastSyncedId, ?string $lastUpdatedAt, int $syncedCount): void
    {
        try {
            DB::connection('mysql')->table('sync_metadata')->updateOrInsert(
                [
                    'table_name' => $tableName,
                    'direction' => $direction
                ],
                [
                    'last_synced_id' => $lastSyncedId,
                    'last_synced_at' => now(),
                    'last_updated_at' => $lastUpdatedAt ?: null,
                    'total_synced' => DB::raw("COALESCE(total_synced, 0) + {$syncedCount}"),
                    'updated_at' => now()
                ]
            );
        } catch (Exception $e) {
            // إذا لم يكن الجدول موجوداً، نحاول إنشاؤه
            Log::warning("Failed to update sync metadata", [
                'table' => $tableName,
                'direction' => $direction,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * مزامنة عكسية: من SQLite إلى MySQL (عند عودة الاتصال)
     * 
     * @param array|null $tables الجداول المطلوب مزامنتها
     * @param bool $safeMode إذا كان true، إضافة فقط (لا تحديث السجلات الموجودة)
     * @param bool $createBackup إنشاء نسخة احتياطية قبل المزامنة
     * @param bool $forceFullSync مزامنة كاملة (تجاهل metadata)
     */
    public function syncFromSQLiteToMySQL(array $tables = null, bool $safeMode = true, bool $createBackup = true, bool $forceFullSync = false): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total_synced' => 0,
            'backup_file' => null,
            'safe_mode' => $safeMode
        ];

        $backupFile = null;
        
        try {
            // 1. إنشاء نسخة احتياطية قبل المزامنة
            if ($createBackup) {
                Log::info('🔄 بدء إنشاء نسخة احتياطية قبل المزامنة...');
                $backupFile = $this->createBackup();
                $results['backup_file'] = $backupFile;
                
                if (!$backupFile) {
                    Log::warning('⚠️ فشل إنشاء النسخة الاحتياطية - سيتم المتابعة بحذر');
                } else {
                    Log::info('✅ تم إنشاء النسخة الاحتياطية بنجاح', ['file' => $backupFile]);
                }
            }

            $mysqlDb = DB::connection('mysql');
            $sqliteDb = DB::connection('sync_sqlite');

            if ($tables === null) {
                $tables = $this->getAllTables($sqliteDb, true);
            }

            // تصفية الجداول المستثناة
            $tables = $this->filterExcludedTables($tables);

            // 2. استخدام Transaction لحماية البيانات
            DB::beginTransaction();
            
            try {
                foreach ($tables as $tableName) {
                    try {
                        $synced = $this->syncTableReverse($mysqlDb, $sqliteDb, $tableName, $safeMode, $forceFullSync);
                        $results['success'][$tableName] = $synced;
                        $results['total_synced'] += $synced;
                    } catch (Exception $e) {
                        $results['failed'][$tableName] = $e->getMessage();
                        Log::error("Failed to sync table {$tableName} from SQLite to MySQL", [
                            'error' => $e->getMessage()
                        ]);
                        
                        // في Safe Mode، نتابع حتى لو فشل جدول
                        if (!$safeMode) {
                            throw $e; // في الوضع العادي، نرمي الخطأ
                        }
                    }
                }
                
                // 3. إذا نجحت المزامنة، نؤكد Transaction
                DB::commit();
                Log::info('✅ تمت المزامنة بنجاح وتم تأكيد Transaction');
                
            } catch (Exception $e) {
                // 4. في حالة الخطأ، Rollback
                DB::rollBack();
                Log::error('❌ فشلت المزامنة - تم Rollback', ['error' => $e->getMessage()]);
                
                // 5. استعادة النسخة الاحتياطية إذا كانت موجودة
                if ($backupFile && $createBackup) {
                    Log::warning('🔄 محاولة استعادة النسخة الاحتياطية...');
                    if ($this->restoreBackup($backupFile)) {
                        Log::info('✅ تم استعادة النسخة الاحتياطية بنجاح');
                        $results['restored'] = true;
                    } else {
                        Log::error('❌ فشل استعادة النسخة الاحتياطية - يرجى الاستعادة يدوياً');
                    }
                }
                
                throw $e;
            }
            
        } catch (Exception $e) {
            Log::error('Reverse database sync failed', [
                'error' => $e->getMessage(),
                'backup_file' => $backupFile
            ]);
            
            $results['error'] = $e->getMessage();
            throw $e;
        }

        return $results;
    }

    /**
     * مزامنة جدول من SQLite إلى MySQL (ذكية - فقط السجلات الجديدة والمحدثة)
     * 
     * @param bool $safeMode إذا كان true، إضافة فقط (لا تحديث السجلات الموجودة)
     */
    protected function syncTableReverse($mysqlDb, $sqliteDb, string $tableName, bool $safeMode = true, bool $forceFullSync = false): int
    {
        if (!$this->tableExists($sqliteDb, $tableName, true)) {
            throw new Exception("Table {$tableName} does not exist in SQLite");
        }

        // التأكد من وجود الجدول في MySQL - إنشاؤه إذا لم يكن موجوداً
        $this->ensureTableExistsReverse($sqliteDb, $mysqlDb, $tableName);

        // التحقق من وجود عمود id و updated_at
        $hasId = $this->hasColumn($sqliteDb, $tableName, 'id');
        $hasUpdatedAt = $this->hasColumn($sqliteDb, $tableName, 'updated_at');

        // جلب أكبر ID و last updated_at من MySQL المحلي (البيانات الفعلية)
        $localMaxId = 0;
        $localMaxUpdatedAt = null;
        
        if ($hasId && $this->tableExists($mysqlDb, $tableName, false)) {
            try {
                $localMaxIdRecord = $mysqlDb->table($tableName)->max('id');
                $localMaxId = $localMaxIdRecord ? (int)$localMaxIdRecord : 0;
            } catch (\Exception $e) {
                Log::warning("Failed to get max ID from MySQL table", ['table' => $tableName, 'error' => $e->getMessage()]);
            }
        }

        if ($hasUpdatedAt && $this->tableExists($mysqlDb, $tableName, false)) {
            try {
                $localMaxUpdatedAtRecord = $mysqlDb->table($tableName)->max('updated_at');
                $localMaxUpdatedAt = $localMaxUpdatedAtRecord ? $localMaxUpdatedAtRecord : null;
            } catch (\Exception $e) {
                Log::warning("Failed to get max updated_at from MySQL table", ['table' => $tableName, 'error' => $e->getMessage()]);
            }
        }

        // جلب معلومات المزامنة السابقة من metadata
        $metadata = $this->getSyncMetadata($tableName, 'up');
        $metadataLastSyncedId = $forceFullSync ? 0 : ($metadata['last_synced_id'] ?? 0);
        $metadataLastUpdatedAt = $forceFullSync ? null : ($metadata['last_updated_at'] ?? null);

        // استخدام القيمة الأكبر بين metadata والبيانات الفعلية المحلية (لضمان عدم فقدان البيانات)
        $lastSyncedId = max($localMaxId, $metadataLastSyncedId);
        $lastUpdatedAt = null;
        
        if ($localMaxUpdatedAt && $metadataLastUpdatedAt) {
            // استخدام التاريخ الأكبر
            $lastUpdatedAt = strtotime($localMaxUpdatedAt) > strtotime($metadataLastUpdatedAt) 
                ? $localMaxUpdatedAt 
                : $metadataLastUpdatedAt;
        } elseif ($localMaxUpdatedAt) {
            $lastUpdatedAt = $localMaxUpdatedAt;
        } elseif ($metadataLastUpdatedAt) {
            $lastUpdatedAt = $metadataLastUpdatedAt;
        }

        Log::info("Smart sync reverse metadata", [
            'table' => $tableName,
            'local_max_id' => $localMaxId,
            'metadata_last_synced_id' => $metadataLastSyncedId,
            'using_last_synced_id' => $lastSyncedId,
            'local_max_updated_at' => $localMaxUpdatedAt,
            'metadata_last_updated_at' => $metadataLastUpdatedAt,
            'using_last_updated_at' => $lastUpdatedAt,
            'safe_mode' => $safeMode,
            'force_full_sync' => $forceFullSync
        ]);

        // بناء الاستعلام الذكي - فقط السجلات الجديدة أو المحدثة
        $query = $sqliteDb->table($tableName);
        
        if (!$forceFullSync && ($hasId && $lastSyncedId > 0 || $hasUpdatedAt && $lastUpdatedAt)) {
            // التحقق من السجلات المفقودة (IDs موجودة في SQLite لكن غير موجودة في MySQL)
            $missingIds = [];
            if ($hasId && $this->tableExists($mysqlDb, $tableName, false)) {
                try {
                    // جلب جميع IDs من SQLite
                    $sqliteIds = $sqliteDb->table($tableName)->pluck('id')->toArray();
                    // جلب جميع IDs من MySQL
                    $mysqlIds = $mysqlDb->table($tableName)->pluck('id')->toArray();
                    // العثور على IDs المفقودة في MySQL
                    $missingIds = array_diff($sqliteIds, $mysqlIds);
                    
                    if (!empty($missingIds)) {
                        Log::info("Found missing IDs in MySQL", [
                            'table' => $tableName,
                            'missing_count' => count($missingIds),
                            'missing_ids' => array_slice($missingIds, 0, 10) // أول 10 فقط للـ log
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to check missing IDs", [
                        'table' => $tableName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $query->where(function($q) use ($hasId, $lastSyncedId, $hasUpdatedAt, $lastUpdatedAt, $missingIds) {
                // 1. السجلات الجديدة (ID > last_synced_id)
                if ($hasId && $lastSyncedId > 0) {
                    $q->where('id', '>', $lastSyncedId);
                }
                
                // 2. السجلات المفقودة (موجودة في SQLite لكن غير موجودة في MySQL)
                if ($hasId && !empty($missingIds)) {
                    $q->orWhereIn('id', $missingIds);
                }
                
                // 3. السجلات التي تم تحديثها بعد آخر مزامنة (مع ID <= last_synced_id)
                if ($hasUpdatedAt && $lastUpdatedAt) {
                    $q->orWhere(function($subQ) use ($lastUpdatedAt, $lastSyncedId, $hasId) {
                        $subQ->where('updated_at', '>', $lastUpdatedAt);
                        // تحديث السجلات الموجودة فقط (ID <= last_synced_id)
                        if ($hasId && $lastSyncedId > 0) {
                            $subQ->where('id', '<=', $lastSyncedId);
                        }
                    });
                }
            });
        }

        // جلب البيانات (بحد أقصى 10000 سجل في كل مرة لتجنب timeout)
        $data = $query->limit(10000)->get()->map(function ($item) {
            return (array) $item;
        })->toArray();
        
        if (!empty($data)) {
            Log::info("Smart sync reverse: Found records to sync", [
                'table' => $tableName,
                'count' => count($data),
                'first_id' => $hasId ? ($data[0]['id'] ?? null) : null,
                'last_id' => $hasId ? (end($data)['id'] ?? null) : null
            ]);
        }

        if (empty($data)) {
            return 0;
        }

        // تعطيل foreign key checks مؤقتاً في MySQL
        try {
            $mysqlDb->statement('SET FOREIGN_KEY_CHECKS=0');
        } catch (Exception $e) {
            // تجاهل إذا لم يكن مدعوماً
        }

        // استخدام upsert (insert or update) بدلاً من فحص كل سجل
        // تحسين الأداء: استخدام upsert مباشرة بدلاً من فحص كل سجل
        $chunkSize = 500;
        $chunks = array_chunk($data, $chunkSize);
        $totalUpserted = 0;
        $totalUpdated = 0;
        $totalInserted = 0;
        $maxId = $lastSyncedId;
        $maxUpdatedAt = $lastUpdatedAt;

        foreach ($chunks as $chunkIndex => $chunk) {
            // التأكد من تحويل جميع العناصر إلى arrays
            $insertData = [];
            foreach ($chunk as $item) {
                $insertData[] = is_array($item) ? $item : (array) $item;
            }

            // التحقق من السجلات الموجودة مسبقاً (batch check)
            $existingIds = [];
            if ($hasId) {
                $ids = array_filter(array_column($insertData, 'id'));
                if (!empty($ids)) {
                    $existingIds = $mysqlDb->table($tableName)
                        ->whereIn('id', $ids)
                        ->pluck('id')
                        ->toArray();
                }
            }

            // جلب updated_at للسجلات الموجودة (للتحقق من التحديث)
            $existingRecords = [];
            if ($hasUpdatedAt && !$safeMode && !empty($existingIds)) {
                $existingRecords = $mysqlDb->table($tableName)
                    ->whereIn('id', $existingIds)
                    ->select('id', 'updated_at')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->id => (array) $item];
                    })
                    ->toArray();
            }

            foreach ($insertData as $row) {
                try {
                    // التأكد من أن $row هو array
                    if (!is_array($row)) {
                        $row = (array) $row;
                    }
                    
                    // استخدام upsert بناءً على ID
                    if ($hasId && isset($row['id'])) {
                        $rowId = (int)$row['id'];
                        $isExisting = in_array($rowId, $existingIds);
                        
                        if ($isExisting) {
                            // في Safe Mode: تخطي السجلات الموجودة (لا تحديث)
                            if ($safeMode) {
                                Log::debug("Skipping existing record in safe mode", [
                                    'table' => $tableName,
                                    'id' => $rowId
                                ]);
                                continue; // تخطي السجل الموجود
                            }
                            
                            // تحديث السجل الموجود (فقط في الوضع غير الآمن)
                            $updateData = $row;
                            unset($updateData['id']); // لا نحدث ID
                            
                            // التحقق من updated_at قبل التحديث (تحديث فقط إذا كان أحدث)
                            if ($hasUpdatedAt && isset($row['updated_at'])) {
                                $currentRecord = $existingRecords[$rowId] ?? null;
                                
                                if ($currentRecord && isset($currentRecord['updated_at'])) {
                                    $currentUpdatedAt = strtotime($currentRecord['updated_at']);
                                    $newUpdatedAt = strtotime($row['updated_at']);
                                    
                                    // تحديث فقط إذا كان السجل الجديد أحدث
                                    if ($newUpdatedAt <= $currentUpdatedAt) {
                                        continue; // تخطي السجل القديم
                                    }
                                }
                            }
                            
                            $mysqlDb->table($tableName)
                                ->where('id', $rowId)
                                ->update($updateData);
                            $totalUpdated++;
                        } else {
                            // إدراج سجل جديد (آمن دائماً)
                            $mysqlDb->table($tableName)->insert($row);
                            $totalInserted++;
                            $existingIds[] = $rowId; // إضافة للقائمة لتجنب التحقق مرة أخرى
                        }
                        
                        // تتبع أكبر ID
                        if ($rowId > $maxId) {
                            $maxId = $rowId;
                        }
                    } else {
                        // إذا لم يكن هناك ID، إدراج مباشر (آمن دائماً)
                        $mysqlDb->table($tableName)->insert($row);
                        $totalInserted++;
                    }
                    
                    // تتبع آخر updated_at
                    if ($hasUpdatedAt && isset($row['updated_at'])) {
                        $rowUpdatedAt = strtotime($row['updated_at']);
                        $maxUpdatedAtTimestamp = $maxUpdatedAt ? strtotime($maxUpdatedAt) : 0;
                        if ($rowUpdatedAt > $maxUpdatedAtTimestamp) {
                            $maxUpdatedAt = $row['updated_at'];
                        }
                    }
                    
                    $totalUpserted++;
                } catch (Exception $e) {
                    Log::warning("Failed to sync row from SQLite to MySQL", [
                        'table' => $tableName,
                        'row_id' => $row['id'] ?? 'no_id',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
            
            // Log progress every 5 chunks
            if (($chunkIndex + 1) % 5 == 0) {
                Log::info("Sync reverse progress", [
                    'table' => $tableName,
                    'chunks_processed' => $chunkIndex + 1,
                    'total_chunks' => count($chunks),
                    'total_upserted' => $totalUpserted
                ]);
            }
        }

        // تحديث metadata بالنتائج النهائية
        if ($totalUpserted > 0 || $lastSyncedId > 0) {
            // استخدام أكبر ID فعلي من MySQL المحلي أو من المزامنة
            $finalMaxId = max($localMaxId, $maxId);
            $finalMaxUpdatedAt = null;
            
            if ($maxUpdatedAt) {
                $finalMaxUpdatedAt = $maxUpdatedAt;
            } elseif ($localMaxUpdatedAt) {
                $finalMaxUpdatedAt = $localMaxUpdatedAt;
            }
            
            $this->updateSyncMetadata($tableName, 'up', $finalMaxId, $finalMaxUpdatedAt, $totalUpserted);
            
            Log::info("Smart sync reverse completed", [
                'table' => $tableName,
                'inserted' => $totalInserted,
                'updated' => $totalUpdated,
                'skipped' => $totalUpserted - $totalInserted - $totalUpdated,
                'total' => $totalUpserted,
                'final_max_id' => $finalMaxId,
                'final_max_updated_at' => $finalMaxUpdatedAt,
                'safe_mode' => $safeMode
            ]);
        } else {
            Log::info("Smart sync reverse: No new records", ['table' => $tableName]);
        }

        // إعادة تفعيل foreign key checks
        try {
            $mysqlDb->statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (Exception $e) {
            // تجاهل إذا لم يكن مدعوماً
        }

        return $totalUpserted;
    }

    /**
     * تصفية الجداول المستثناة من المزامنة
     * 
     * @param array $tables قائمة الجداول
     * @return array قائمة الجداول بعد التصفية
     */
    protected function filterExcludedTables(array $tables): array
    {
        return array_filter($tables, function ($tableName) {
            // تخطي الجداول المستثناة
            if (in_array($tableName, $this->excludedTables)) {
                Log::debug("Skipping excluded table from sync", ['table' => $tableName]);
                return false;
            }
            
            // تخطي الجداول التي تبدأ بـ telescope_ (للتأكد من تغطية جميع جداول Telescope)
            if (strpos($tableName, 'telescope_') === 0) {
                Log::debug("Skipping Telescope table from sync", ['table' => $tableName]);
                return false;
            }
            
            return true;
        });
    }

    /**
     * مزامنة التغييرات من sync_queue إلى MySQL
     */
    public function syncFromQueue(): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total_synced' => 0,
            'queue_processed' => 0
        ];

        try {
            $syncQueueService = app(\App\Services\SyncQueueService::class);
            $mysqlDb = DB::connection('mysql');
            
            // جلب التغييرات المعلقة
            $pendingChanges = $syncQueueService->getPendingChanges(null, 500);
            
            if (empty($pendingChanges)) {
                return $results;
            }

            // تجميع التغييرات حسب الجدول
            $changesByTable = [];
            foreach ($pendingChanges as $change) {
                $tableName = $change['table_name'];
                if (!isset($changesByTable[$tableName])) {
                    $changesByTable[$tableName] = [];
                }
                $changesByTable[$tableName][] = $change;
            }

            // معالجة كل جدول
            foreach ($changesByTable as $tableName => $changes) {
                $synced = 0;
                $failed = 0;

                foreach ($changes as $change) {
                    try {
                        // تحديث حالة السجل إلى "syncing"
                        DB::table('sync_queue')
                            ->where('id', $change['id'])
                            ->update(['status' => 'syncing']);

                        $success = false;

                        switch ($change['action']) {
                            case 'insert':
                            case 'update':
                                if ($change['data']) {
                                    // استخدام upsert
                                    if ($mysqlDb->table($tableName)->where('id', $change['record_id'])->exists()) {
                                        // تحديث
                                        $updateData = $change['data'];
                                        unset($updateData['id']);
                                        $mysqlDb->table($tableName)
                                            ->where('id', $change['record_id'])
                                            ->update($updateData);
                                    } else {
                                        // إدراج
                                        $mysqlDb->table($tableName)->insert($change['data']);
                                    }
                                    $success = true;
                                }
                                break;

                            case 'delete':
                                $mysqlDb->table($tableName)
                                    ->where('id', $change['record_id'])
                                    ->delete();
                                $success = true;
                                break;
                        }

                        if ($success) {
                            $syncQueueService->markAsSynced($change['id']);
                            $synced++;
                            $results['total_synced']++;
                        } else {
                            $syncQueueService->markAsFailed($change['id'], 'Unknown action or missing data');
                            $failed++;
                        }

                    } catch (\Exception $e) {
                        $syncQueueService->markAsFailed($change['id'], $e->getMessage());
                        $failed++;
                        Log::error("Failed to sync queued change", [
                            'queue_id' => $change['id'],
                            'table' => $tableName,
                            'record_id' => $change['record_id'],
                            'action' => $change['action'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                if ($synced > 0) {
                    $results['success'][$tableName] = $synced;
                }
                if ($failed > 0) {
                    $results['failed'][$tableName] = "Failed: {$failed} records";
                }
            }

            $results['queue_processed'] = count($pendingChanges);

        } catch (\Exception $e) {
            Log::error('Sync from queue failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return $results;
    }
}

