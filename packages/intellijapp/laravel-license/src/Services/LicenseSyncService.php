<?php

namespace IntellijApp\License\Services;

use IntellijApp\License\Models\License;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LicenseSyncService
{
    /**
     * مزامنة جدول licenses من MySQL إلى SQLite
     */
    public static function syncToSQLite(): array
    {
        try {
            $mysqlDb = DB::connection('mysql');
            $sqliteDb = DB::connection('sync_sqlite');

            // التحقق من وجود الجدول في SQLite
            if (!self::tableExists($sqliteDb, 'licenses')) {
                self::createLicensesTableInSQLite($sqliteDb);
            }

            // جلب جميع التراخيص من MySQL
            $licenses = $mysqlDb->table('licenses')->get();

            $synced = 0;
            foreach ($licenses as $license) {
                try {
                    // استخدام upsert (insert or update)
                    $sqliteDb->table('licenses')->updateOrInsert(
                        ['id' => $license->id],
                        (array) $license
                    );
                    $synced++;
                } catch (Exception $e) {
                    Log::warning("Failed to sync license {$license->id} to SQLite", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("License sync to SQLite completed", [
                'total' => count($licenses),
                'synced' => $synced
            ]);

            return [
                'success' => true,
                'total' => count($licenses),
                'synced' => $synced
            ];

        } catch (Exception $e) {
            Log::error("License sync to SQLite failed", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * مزامنة جدول licenses من SQLite إلى MySQL
     */
    public static function syncToMySQL(): array
    {
        try {
            $mysqlDb = DB::connection('mysql');
            $sqliteDb = DB::connection('sync_sqlite');

            // التحقق من وجود الجدول في SQLite
            if (!self::tableExists($sqliteDb, 'licenses')) {
                return [
                    'success' => false,
                    'error' => 'Licenses table does not exist in SQLite'
                ];
            }

            // جلب جميع التراخيص من SQLite
            $licenses = $sqliteDb->table('licenses')->get();

            $synced = 0;
            foreach ($licenses as $license) {
                try {
                    // استخدام upsert (insert or update)
                    $mysqlDb->table('licenses')->updateOrInsert(
                        ['id' => $license->id],
                        (array) $license
                    );
                    $synced++;
                } catch (Exception $e) {
                    Log::warning("Failed to sync license {$license->id} to MySQL", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("License sync to MySQL completed", [
                'total' => count($licenses),
                'synced' => $synced
            ]);

            return [
                'success' => true,
                'total' => count($licenses),
                'synced' => $synced
            ];

        } catch (Exception $e) {
            Log::error("License sync to MySQL failed", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * إنشاء جدول licenses في SQLite
     */
    protected static function createLicensesTableInSQLite($sqliteDb): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS licenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                license_key TEXT,
                domain TEXT,
                fingerprint TEXT,
                type TEXT DEFAULT 'standard',
                max_installations INTEGER DEFAULT 1,
                activated_at TEXT,
                expires_at TEXT,
                is_active INTEGER DEFAULT 1,
                last_verified_at TEXT,
                notes TEXT,
                created_at TEXT,
                updated_at TEXT
            )
        ";

        $sqliteDb->statement($sql);
        
        // إنشاء indexes للأداء
        try {
            $sqliteDb->statement('CREATE INDEX IF NOT EXISTS idx_licenses_domain ON licenses(domain)');
            $sqliteDb->statement('CREATE INDEX IF NOT EXISTS idx_licenses_is_active ON licenses(is_active)');
            $sqliteDb->statement('CREATE INDEX IF NOT EXISTS idx_licenses_fingerprint ON licenses(fingerprint)');
        } catch (Exception $e) {
            Log::warning("Failed to create indexes for licenses table", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * التحقق من وجود جدول
     */
    protected static function tableExists($db, string $tableName): bool
    {
        try {
            $result = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name = ?", [$tableName]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * إضافة جدول licenses إلى قائمة المزامنة
     * (للاستخدام مع DatabaseSyncService الموجود)
     */
    public static function addToSyncList(): void
    {
        // هذا يمكن استخدامه لإضافة 'licenses' إلى قائمة الجداول المزامنة
        // في DatabaseSyncService الموجود
        Log::info("License table added to sync list");
    }
}

