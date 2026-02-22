<?php

namespace App\Observers;

use App\Models\CarContract;
use App\Services\SyncQueueService;
use Illuminate\Support\Facades\Log;

class CarContractObserver
{
    protected $syncQueue;

    public function __construct(SyncQueueService $syncQueue)
    {
        $this->syncQueue = $syncQueue;
    }

    /**
     * Handle the CarContract "created" event.
     */
    public function created(CarContract $carContract)
    {
        if ($this->isLocalEnvironment()) {
            try {
                $this->syncQueue->queueInsert('car_contract', $carContract->id, $carContract->toArray());
                Log::debug('CarContract insert queued for sync (Local)', ['car_contract_id' => $carContract->id]);
            } catch (\Exception $e) {
                Log::error('Failed to queue car contract insert for sync', [
                    'car_contract_id' => $carContract->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the CarContract "updated" event.
     */
    public function updated(CarContract $carContract)
    {
        if ($this->isLocalEnvironment()) {
            try {
                $oldData = $carContract->getOriginal();
                $newData = $carContract->getAttributes();
                $this->syncQueue->queueUpdate('car_contract', $carContract->id, $oldData, $newData);
                Log::debug('CarContract update queued for sync (Local)', [
                    'car_contract_id' => $carContract->id,
                    'changes' => $carContract->getChanges()
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to queue car contract update for sync', [
                    'car_contract_id' => $carContract->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the CarContract "deleted" event (soft delete or force delete).
     * عند حذف عقد محلياً يُسجَّل في sync_queue ليُحذف من السيرفر عند المزامنة.
     */
    public function deleted(CarContract $carContract)
    {
        if ($this->isLocalEnvironment()) {
            try {
                $this->syncQueue->queueDelete('car_contract', $carContract->id);
                Log::debug('CarContract delete queued for sync (Local)', ['car_contract_id' => $carContract->id]);
            } catch (\Exception $e) {
                Log::error('Failed to queue car contract delete for sync', [
                    'car_contract_id' => $carContract->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    protected function isLocalEnvironment(): bool
    {
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
