<?php

namespace App\Observers;

use App\Models\Car;
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

