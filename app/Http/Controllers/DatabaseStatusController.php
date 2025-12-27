<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ConnectionService;
use App\Services\DatabaseSyncService;

class DatabaseStatusController extends Controller
{
    /**
     * الحصول على حالة قاعدة البيانات والمزامنة
     */
    public function status(): JsonResponse
    {
        try {
            $currentConnection = config('database.default');
            $manualMode = ConnectionService::getManualMode();
            $isOnline = ConnectionService::isOnline();
            
            // معلومات الاتصال
            $connectionInfo = [
                'current' => $currentConnection,
                'is_sqlite' => $currentConnection === 'sync_sqlite',
                'is_mysql' => $currentConnection === 'mysql',
                'manual_mode' => $manualMode,
                'is_online' => $isOnline,
                'auto_switch' => $manualMode === null,
            ];

            // معلومات MySQL
            $mysqlInfo = $this->getConnectionInfo('mysql');
            
            // معلومات SQLite
            $sqliteInfo = $this->getConnectionInfo('sync_sqlite');

            // معلومات المزامنة
            $syncInfo = $this->getSyncInfo();

            // إحصائيات الجداول
            $tablesStats = $this->getTablesStats();

            return response()->json([
                'connection' => $connectionInfo,
                'mysql' => $mysqlInfo,
                'sqlite' => $sqliteInfo,
                'sync' => $syncInfo,
                'tables' => $tablesStats,
                'timestamp' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get database status', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على معلومات Connection
     */
    protected function getConnectionInfo(string $connection): array
    {
        try {
            $db = DB::connection($connection);
            $isReachable = false;
            $tablesCount = 0;
            $error = null;

            try {
                // محاولة الاتصال
                $db->select('SELECT 1');
                $isReachable = true;

                // جلب عدد الجداول
                if ($connection === 'sync_sqlite') {
                    $tables = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                    $tablesCount = count($tables);
                } else {
                    $database = config('database.connections.mysql.database');
                    $tables = $db->select(
                        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?",
                        [$database]
                    );
                    $tablesCount = count($tables);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }

            return [
                'connection' => $connection,
                'is_reachable' => $isReachable,
                'tables_count' => $tablesCount,
                'error' => $error,
            ];

        } catch (\Exception $e) {
            return [
                'connection' => $connection,
                'is_reachable' => false,
                'tables_count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * الحصول على معلومات المزامنة
     */
    protected function getSyncInfo(): array
    {
        try {
            $mysqlDb = DB::connection('mysql');
            
            // التحقق من وجود جدول sync_metadata
            if (!$this->tableExists($mysqlDb, 'sync_metadata', false)) {
                return [
                    'metadata_exists' => false,
                    'total_tables_synced' => 0,
                    'last_sync' => null,
                ];
            }

            $metadata = $mysqlDb->table('sync_metadata')
                ->select('table_name', 'direction', 'last_synced_at', 'total_synced')
                ->get();

            $totalSynced = $metadata->sum('total_synced');
            $lastSync = $metadata->max('last_synced_at');

            return [
                'metadata_exists' => true,
                'total_tables_synced' => $metadata->count(),
                'total_records_synced' => $totalSynced,
                'last_sync' => $lastSync ? date('Y-m-d H:i:s', strtotime($lastSync)) : null,
            ];

        } catch (\Exception $e) {
            return [
                'metadata_exists' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * الحصول على إحصائيات الجداول
     */
    protected function getTablesStats(): array
    {
        try {
            $stats = [];
            $importantTables = ['licenses', 'users', 'car', 'car_contract', 'transactions', 'wallets'];

            foreach ($importantTables as $tableName) {
                $mysqlCount = 0;
                $sqliteCount = 0;
                $mysqlError = null;
                $sqliteError = null;

                // MySQL
                try {
                    $mysqlDb = DB::connection('mysql');
                    if ($this->tableExists($mysqlDb, $tableName, false)) {
                        $mysqlCount = $mysqlDb->table($tableName)->count();
                    }
                } catch (\Exception $e) {
                    $mysqlError = $e->getMessage();
                }

                // SQLite
                try {
                    $sqliteDb = DB::connection('sync_sqlite');
                    if ($this->tableExists($sqliteDb, $tableName, true)) {
                        $sqliteCount = $sqliteDb->table($tableName)->count();
                    }
                } catch (\Exception $e) {
                    $sqliteError = $e->getMessage();
                }

                $stats[] = [
                    'table' => $tableName,
                    'mysql_count' => $mysqlCount,
                    'sqlite_count' => $sqliteCount,
                    'difference' => $mysqlCount - $sqliteCount,
                    'mysql_error' => $mysqlError,
                    'sqlite_error' => $sqliteError,
                    'is_synced' => $mysqlCount === $sqliteCount && $mysqlCount > 0,
                ];
            }

            return $stats;

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
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

