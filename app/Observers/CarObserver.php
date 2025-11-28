<?php

namespace App\Observers;

use App\Models\Car;
use App\Models\InternalSale;
use App\Services\SyncQueueService;
use Illuminate\Support\Facades\Log;

class CarObserver
{
    protected $syncQueue;

    public function __construct(SyncQueueService $syncQueue)
    {
        $this->syncQueue = $syncQueue;
    }

    /**
     * Handle the Car "created" event.
     */
    public function created(Car $car)
    {
        // فقط في البيئة المحلية (Local) - استخدام sync_queue
        if ($this->isLocalEnvironment()) {
            try {
                $this->syncQueue->queueInsert('car', $car->id, $car->toArray());
                Log::debug('Car insert queued for sync (Local)', ['car_id' => $car->id]);
            } catch (\Exception $e) {
                Log::error('Failed to queue car insert for sync', [
                    'car_id' => $car->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        // في السيرفر: لا حاجة لـ sync_queue - يعمل مباشرة
    }

    /**
     * Handle the Car "updated" event.
     */
    public function updated(Car $car)
    {
        // تحديث المبيعات الداخلية تلقائياً عند تعديل المصاريف
        $this->updateInternalSalesIfNeeded($car);

        // فقط في البيئة المحلية (Local) - استخدام sync_queue
        if ($this->isLocalEnvironment()) {
            try {
                // جلب البيانات القديمة من التغييرات
                $oldData = $car->getOriginal();
                $newData = $car->getAttributes();
                
                $this->syncQueue->queueUpdate('car', $car->id, $oldData, $newData);
                Log::debug('Car update queued for sync (Local)', [
                    'car_id' => $car->id,
                    'changes' => $car->getChanges()
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to queue car update for sync', [
                    'car_id' => $car->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        // في السيرفر: لا حاجة لـ sync_queue - يعمل مباشرة
    }

    /**
     * تحديث المبيعات الداخلية عند تعديل مصاريف السيارة
     * 
     * ملاحظة: في المبيعات الداخلية:
     * - expenses = total_s للسيارة (التكلفة الكلية)
     * - car_price = total_s للسيارة (سعر السيارة)
     * 
     * عند تغيير total_s أو expenses_s في السيارة، يتم تحديث expenses و car_price في المبيعات الداخلية
     */
    protected function updateInternalSalesIfNeeded(Car $car)
    {
        try {
            // التحقق من وجود مبيعة داخلية للسيارة
            $internalSale = InternalSale::where('car_id', $car->id)->first();
            
            if (!$internalSale) {
                // لا توجد مبيعة داخلية، لا حاجة للتحديث
                return;
            }

            $changes = $car->getChanges();
            $needsUpdate = false;
            $updateData = [];

            // إذا تغير total_s (التكلفة الكلية للبيع)، نحدث expenses و car_price في المبيعات الداخلية
            // لأن expenses في internal_sales = total_s للسيارة
            if (isset($changes['total_s'])) {
                $newTotalS = $car->total_s ?? 0;
                $updateData['expenses'] = $newTotalS;
                $updateData['car_price'] = $newTotalS;
                $needsUpdate = true;
            }

            // إذا تغير expenses_s فقط (بدون تغيير total_s)، نحدث expenses فقط
            // لكن فقط إذا لم يتم تحديث expenses من total_s أعلاه
            if (isset($changes['expenses_s']) && !isset($changes['total_s'])) {
                // في هذه الحالة، expenses_s تغير لكن total_s لم يتغير
                // لذا نحدث expenses بناءً على total_s الحالي (لأن expenses = total_s)
                $updateData['expenses'] = $car->total_s ?? 0;
                $needsUpdate = true;
            }

            // إذا تغير total (للمشتريات)، نحدث car_price و expenses في المبيعات الداخلية
            if (isset($changes['total'])) {
                $newTotal = $car->total ?? 0;
                // إذا لم يتم تحديث car_price من total_s، نحدثه من total
                if (!isset($updateData['car_price'])) {
                    $updateData['car_price'] = $newTotal;
                }
                // إذا لم يتم تحديث expenses من total_s، نحدثه من total
                if (!isset($updateData['expenses'])) {
                    $updateData['expenses'] = $newTotal;
                }
                $needsUpdate = true;
            }

            // تحديث المبيعات الداخلية إذا لزم الأمر
            if ($needsUpdate) {
                // حفظ القيم القديمة قبل التحديث
                $oldExpenses = $internalSale->expenses;
                $oldCarPrice = $internalSale->car_price;
                $oldProfit = $internalSale->profit;
                
                // إعادة حساب الربح تلقائياً (سيتم في boot method في InternalSale)
                // الربح = سعر البيع - (سعر السيارة + المصاريف + مصاريف إضافية)
                $internalSale->update($updateData);
                
                // إعادة تحميل المبيعة للحصول على القيم المحدثة
                $internalSale->refresh();
                
                Log::info('Internal sale updated automatically after car expenses change', [
                    'car_id' => $car->id,
                    'internal_sale_id' => $internalSale->id,
                    'updated_fields' => array_keys($updateData),
                    'car_changes' => array_keys($changes),
                    'old_expenses' => $oldExpenses,
                    'new_expenses' => $internalSale->expenses,
                    'old_car_price' => $oldCarPrice,
                    'new_car_price' => $internalSale->car_price,
                    'old_profit' => $oldProfit,
                    'new_profit' => $internalSale->profit,
                    'sale_price' => $internalSale->sale_price,
                    'paid_amount' => $internalSale->paid_amount
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update internal sales after car update', [
                'car_id' => $car->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Car "deleted" event.
     */
    public function deleted(Car $car)
    {
        // فقط في البيئة المحلية (Local) - استخدام sync_queue
        if ($this->isLocalEnvironment()) {
            try {
                $this->syncQueue->queueDelete('car', $car->id);
                Log::debug('Car delete queued for sync (Local)', ['car_id' => $car->id]);
            } catch (\Exception $e) {
                Log::error('Failed to queue car delete for sync', [
                    'car_id' => $car->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        // في السيرفر: لا حاجة لـ sync_queue - يعمل مباشرة
    }

    /**
     * التحقق من أن البيئة محلية (Local)
     */
    protected function isLocalEnvironment(): bool
    {
        // التحقق من أننا في بيئة محلية وليس سيرفر
        $isLocal = env('APP_ENV') === 'local' || 
                   config('database.default') === 'sync_sqlite' ||
                   str_contains(env('APP_URL', ''), '127.0.0.1') ||
                   str_contains(env('APP_URL', ''), 'localhost');
        
        $isServer = env('APP_ENV') === 'server' || 
                    env('APP_ENV') === 'production' ||
                    str_contains(env('APP_URL', ''), 'intellijapp.com');
        
        return $isLocal && !$isServer;
    }
}

