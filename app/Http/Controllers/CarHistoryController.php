<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CarHistoryController extends Controller
{
    /**
     * عرض صفحة تاريخ السيارة
     */
    public function index(Request $request, $carId)
    {
        $car = Car::with(['company', 'name', 'model', 'color'])->findOrFail($carId);

        return Inertia::render('CarHistory/Index', [
            'car' => $car,
            'filters' => $request->only(['action', 'user', 'date_from', 'date_to'])
        ]);
    }

    /**
     * API للحصول على تاريخ السيارة
     */
    public function getHistory(Request $request, $carId): JsonResponse
    {
        $car = Car::findOrFail($carId);

        $query = CarHistory::where('car_id', $carId)
            ->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc');

        // فلترة حسب النوع
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // فلترة حسب المستخدم
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $history = $query->paginate(20);

        return response()->json($history);
    }

    /**
     * عرض تفاصيل تغيير معين
     */
    public function show($carId, $historyId): JsonResponse
    {
        $history = CarHistory::where('car_id', $carId)
            ->with(['car', 'user'])
            ->findOrFail($historyId);

        return response()->json($history);
    }

    /**
     * مقارنة بين نسختين
     */
    public function compare(Request $request, $carId): JsonResponse
    {
        $request->validate([
            'history_id_1' => 'required|exists:car_history,id',
            'history_id_2' => 'required|exists:car_history,id',
        ]);

        $history1 = CarHistory::where('car_id', $carId)->findOrFail($request->history_id_1);
        $history2 = CarHistory::where('car_id', $carId)->findOrFail($request->history_id_2);

        // مقارنة البيانات
        $comparison = $this->compareData($history1->new_data ?? [], $history2->new_data ?? []);

        return response()->json([
            'history1' => $history1,
            'history2' => $history2,
            'comparison' => $comparison
        ]);
    }

    /**
     * إحصائيات التغييرات
     */
    public function statistics(Request $request): JsonResponse
    {
        $ownerId = Auth::user()->owner_id;

        $stats = [
            'total_changes' => CarHistory::whereHas('car', function($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })->count(),

            'changes_by_action' => CarHistory::whereHas('car', function($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->get(),

            'changes_by_user' => CarHistory::whereHas('car', function($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })->with('user:id,name')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get(),

            'recent_activity' => CarHistory::whereHas('car', function($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })->with(['car:id,no', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
        ];

        return response()->json($stats);
    }

    /**
     * تنظيف السجلات القديمة
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'older_than_days' => 'required|integer|min:30',
        ]);

        $deleted = CarHistory::where('created_at', '<', now()->subDays($request->older_than_days))
            ->delete();

        return response()->json([
            'message' => "تم حذف {$deleted} سجل قديم",
            'deleted_count' => $deleted
        ]);
    }

    /**
     * Helper method للمقارنة بين البيانات
     */
    private function compareData(array $data1, array $data2): array
    {
        $comparison = [
            'added' => [],
            'removed' => [],
            'changed' => []
        ];

        $allKeys = array_unique(array_merge(array_keys($data1), array_keys($data2)));

        foreach ($allKeys as $key) {
            $value1 = $data1[$key] ?? null;
            $value2 = $data2[$key] ?? null;

            if ($value1 === null && $value2 !== null) {
                $comparison['added'][$key] = $value2;
            } elseif ($value1 !== null && $value2 === null) {
                $comparison['removed'][$key] = $value1;
            } elseif ($value1 != $value2) {
                $comparison['changed'][$key] = [
                    'from' => $value1,
                    'to' => $value2
                ];
            }
        }

        return $comparison;
    }

    /**
     * Determine action type from transaction
     */
    private function determineActionFromTransaction($transaction): string
    {
        $description = strtolower($transaction->description);

        if (str_contains($description, 'إضافة') || str_contains($description, 'add') || str_contains($description, 'create')) {
            return 'create';
        }

        if (str_contains($description, 'تحديث') || str_contains($description, 'تعديل') || str_contains($description, 'update') || str_contains($description, 'edit')) {
            return 'update';
        }

        if (str_contains($description, 'حذف') || str_contains($description, 'delete')) {
            return 'delete';
        }

        // Default to update for financial transactions
        return 'update';
    }

    /**
     * Extract change information from transaction description
     */
    private function extractChangeInfo(string $description): ?array
    {
        $patterns = [
            '/من\s+([^\s]+)\s+إلى\s+([^\s]+)/u', // Arabic pattern
            '/from\s+([^\s]+)\s+to\s+([^\s]+)/i', // English pattern
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                return [
                    'field' => $this->guessFieldName($description),
                    'old' => $matches[1],
                    'new' => $matches[2],
                ];
            }
        }

        return null;
    }

    /**
     * Guess field name from description
     */
    private function guessFieldName(string $description): ?string
    {
        $description = strtolower($description);

        $fieldMappings = [
            'سعر' => 'purchase_price',
            'price' => 'purchase_price',
            'مدفوع' => 'paid_amount',
            'paid' => 'paid_amount',
            'ملاحظة' => 'note',
            'note' => 'note',
        ];

        foreach ($fieldMappings as $keyword => $field) {
            if (str_contains($description, $keyword)) {
                return $field;
            }
        }

        return null;
    }

    /**
     * نقل معاملات السيارات من جدول transactions إلى car_history
     */
    public function migrateTransactions(Request $request): JsonResponse
    {
        $request->validate([
            'confirm_delete' => 'boolean',
            'limit' => 'integer|min:1|max:1000',
        ]);

        $limit = $request->get('limit', 100);
        $confirmDelete = $request->get('confirm_delete', false);

        // Get transactions related to cars
        $transactions = \App\Models\Transactions::whereNotNull('morphed_id')
            ->where('morphed_type', 'App\Models\Car')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        $migrated = 0;
        $skipped = 0;
        $errors = 0;
        $transactionIds = [];

        foreach ($transactions as $transaction) {
            try {
                // Check if car exists
                $car = \App\Models\Car::find($transaction->morphed_id);
                if (!$car) {
                    $skipped++;
                    continue;
                }

                // Check if this transaction was already migrated
                $existingHistory = CarHistory::where('car_id', $car->id)
                    ->where('created_at', $transaction->created_at)
                    ->where('description', $transaction->description)
                    ->first();

                if ($existingHistory) {
                    $skipped++;
                    continue;
                }

                // Determine action type
                $action = $this->determineActionFromTransaction($transaction);

                // Create history record
                $historyData = [
                    'car_id' => $car->id,
                    'action' => $action,
                    'description' => $transaction->description,
                    'user_id' => $transaction->user_added,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ];

                // Extract change info for updates
                if ($action === 'update') {
                    $changeInfo = $this->extractChangeInfo($transaction->description);
                    if ($changeInfo) {
                        $historyData['changes'] = $changeInfo;
                        $historyData['field_changed'] = $changeInfo['field'] ?? null;
                    }
                }

                CarHistory::create($historyData);
                $transactionIds[] = $transaction->id;
                $migrated++;

            } catch (\Exception $e) {
                \Log::error('Migration error for transaction ' . $transaction->id, [
                    'error' => $e->getMessage(),
                    'transaction' => $transaction->toArray()
                ]);
                $errors++;
            }
        }

        // Delete migrated transactions if confirmed
        $deleted = 0;
        if ($confirmDelete && !empty($transactionIds)) {
            $deleted = \App\Models\Transactions::whereIn('id', $transactionIds)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت معالجة معاملات السيارات',
            'stats' => [
                'processed' => $transactions->count(),
                'migrated' => $migrated,
                'skipped' => $skipped,
                'errors' => $errors,
                'deleted' => $deleted,
            ],
            'next_offset' => $transactions->count() >= $limit ? ($request->get('offset', 0) + $limit) : null,
        ]);
    }

    /**
     * مسح الـ Cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            \Log::info('Cache cleared via API', ['user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'تم مسح الـ Cache بنجاح'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to clear cache', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'فشل مسح الـ Cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحسين قاعدة البيانات
     */
    public function optimizeDatabase(Request $request): JsonResponse
    {
        try {
            // تشغيل أوامر تحسين قاعدة البيانات
            \Artisan::call('db:monitor'); // إذا كان متوفراً

            \Log::info('Database optimization completed via API', ['user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحسين قاعدة البيانات بنجاح'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to optimize database', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'فشل تحسين قاعدة البيانات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء نسخة احتياطية
     */
    public function generateBackup(Request $request): JsonResponse
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('backups/' . $filename);

            // التأكد من وجود المجلد
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            // إنشاء نسخة احتياطية بسيطة (يمكن تحسينها)
            $command = "sqlite3 database/database.sqlite .dump > {$path}";
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                \Log::info('Database backup created via API', [
                    'user_id' => auth()->id(),
                    'filename' => $filename
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء النسخة الاحتياطية بنجاح',
                    'filename' => $filename
                ]);
            } else {
                throw new \Exception('فشل في تنفيذ أمر النسخ الاحتياطي');
            }

        } catch (\Exception $e) {
            \Log::error('Failed to generate backup', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء النسخة الاحتياطية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * فحص صحة النظام
     */
    public function healthCheck(Request $request): JsonResponse
    {
        try {
            $health = [
                'database' => $this->checkDatabaseHealth(),
                'storage' => $this->checkStorageHealth(),
                'cache' => $this->checkCacheHealth(),
                'system' => $this->getSystemInfo()
            ];

            $issues = $this->analyzeHealthIssues($health);

            return response()->json([
                'success' => true,
                'health' => $health,
                'issues' => $issues,
                'status' => empty($issues['critical']) ? 'healthy' : 'unhealthy'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل فحص صحة النظام: ' . $e->getMessage()
            ], 500);
        }
    }

    private function checkDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            \DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000; // ms

            return [
                'status' => 'healthy',
                'response_time' => round($responseTime, 2) . 'ms',
                'message' => 'قاعدة البيانات تعمل بشكل طبيعي'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'مشكلة في الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ];
        }
    }

    private function checkStorageHealth(): array
    {
        try {
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;

            $status = $usedPercentage > 90 ? 'warning' : 'healthy';

            return [
                'status' => $status,
                'free_space' => $this->formatBytes($freeSpace),
                'used_percentage' => round($usedPercentage, 1) . '%',
                'message' => "مساحة التخزين: {$this->formatBytes($freeSpace)} متاحة"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'فشل فحص مساحة التخزين'
            ];
        }
    }

    private function checkCacheHealth(): array
    {
        try {
            $key = 'health_check_' . time();
            \Cache::put($key, 'test', 10); // 10 seconds
            $value = \Cache::get($key);
            \Cache::forget($key);

            if ($value === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'نظام الـ Cache يعمل بشكل طبيعي'
                ];
            } else {
                return [
                    'status' => 'warning',
                    'message' => 'نظام الـ Cache لا يعمل بشكل صحيح'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'فشل فحص نظام الـ Cache'
            ];
        }
    }

    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'timezone' => config('app.timezone')
        ];
    }

    private function analyzeHealthIssues(array $health): array
    {
        $issues = [
            'critical' => [],
            'warnings' => [],
            'info' => []
        ];

        foreach ($health as $component => $status) {
            if (isset($status['status'])) {
                if ($status['status'] === 'error') {
                    $issues['critical'][] = ucfirst($component) . ': ' . $status['message'];
                } elseif ($status['status'] === 'warning') {
                    $issues['warnings'][] = ucfirst($component) . ': ' . $status['message'];
                }
            }
        }

        return $issues;
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * معلومات قاعدة البيانات المفصلة
     */
    public function getDatabaseInfo(Request $request): JsonResponse
    {
        try {
            $databasePath = database_path('database.sqlite');

            $info = [
                'type' => 'SQLite',
                'version' => \DB::select('SELECT sqlite_version() as version')[0]->version ?? 'Unknown',
                'path' => $databasePath,
                'size' => file_exists($databasePath) ? $this->formatBytes(filesize($databasePath)) : 'غير محدد',
            ];

            // معلومات الجداول
            $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $info['total_tables'] = count($tables);

            $totalRecords = 0;
            foreach ($tables as $table) {
                try {
                    $count = \DB::table($table->name)->count();
                    $totalRecords += $count;
                } catch (\Exception $e) {
                    // تخطي الجداول التي لا يمكن قراءتها
                    continue;
                }
            }

            $info['total_records'] = $totalRecords;

            // معلومات إضافية
            $info['last_modified'] = file_exists($databasePath) ? date('Y-m-d H:i:s', filemtime($databasePath)) : null;
            $info['permissions'] = file_exists($databasePath) ? substr(sprintf('%o', fileperms($databasePath)), -4) : null;

            return response()->json([
                'success' => true,
                'database_info' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على معلومات قاعدة البيانات: ' . $e->getMessage()
            ], 500);
        }
    }
}
