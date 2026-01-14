<?php

namespace App\Services;

use App\Models\Transfers;
use App\Models\ConnectedSystem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalTransferService
{
    /**
     * إرسال تحويل إلى نظام خارجي
     */
    public function sendTransfer(Transfers $transfer, ConnectedSystem $targetSystem)
    {
        try {
            $data = $this->formatTransferData($transfer);
            
            $response = Http::timeout(env('EXTERNAL_TRANSFER_TIMEOUT', 30))
                ->withHeaders([
                    'API-Key' => $targetSystem->api_key,
                    'Content-Type' => 'application/json',
                ])
                ->post($targetSystem->domain . '/api/receive-transfer', $data);

            if ($response->successful()) {
                $responseData = $response->json();
                // تحديث التحويل المحلي بـ external_transfer_id
                $transfer->update([
                    'external_transfer_id' => $responseData['transfer']['id'] ?? null,
                    'stauts' => 'قيد التسليم'
                ]);
                
                Log::info('Transfer sent successfully', [
                    'transfer_id' => $transfer->id,
                    'target_system' => $targetSystem->name,
                    'external_transfer_id' => $responseData['transfer']['id'] ?? null
                ]);
                
                return ['success' => true, 'data' => $responseData];
            } else {
                Log::error('Failed to send transfer', [
                    'transfer_id' => $transfer->id,
                    'target_system' => $targetSystem->name,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return ['success' => false, 'error' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending transfer', [
                'transfer_id' => $transfer->id,
                'target_system' => $targetSystem->name,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * تنسيق بيانات التحويل للإرسال
     */
    public function formatTransferData(Transfers $transfer)
    {
        return [
            'amount' => $transfer->amount,
            'currency' => $transfer->currency ?? '$',
            'sender_note' => $transfer->sender_note,
            'note' => $transfer->note,
            'sender_system_domain' => env('APP_URL'),
            'transfer_no' => $transfer->no,
        ];
    }

    /**
     * التحقق من صحة API_KEY
     */
    public function validateApiKey($apiKey)
    {
        $expectedKey = env('API_KEY');
        return $apiKey === $expectedKey;
    }

    /**
     * إرسال تأكيد تحويل إلى النظام المرسل
     */
    public function sendTransferConfirmation(Transfers $transfer, ConnectedSystem $sourceSystem)
    {
        try {
            // external_transfer_id هو رقم التحويل (no) في النظام المرسل
            // إذا كان التحويل وارداً، external_transfer_id يحتوي على رقم التحويل من النظام المرسل
            // إذا كان التحويل مرسلاً، نستخدم no
            $externalTransferId = $transfer->external_transfer_id ?? $transfer->no;
            
            $data = [
                'external_transfer_id' => $externalTransferId,
                'fee' => $transfer->fee,
                'receiver_note' => $transfer->receiver_note,
                'status' => 'تم الأستلام'
            ];
            
            $response = Http::timeout(env('EXTERNAL_TRANSFER_TIMEOUT', 30))
                ->withHeaders([
                    'API-Key' => $sourceSystem->api_key,
                    'Content-Type' => 'application/json',
                ])
                ->post($sourceSystem->domain . '/api/confirm-external-transfer', $data);

            if ($response->successful()) {
                Log::info('Transfer confirmation sent successfully', [
                    'transfer_id' => $transfer->id,
                    'source_system' => $sourceSystem->name
                ]);
                
                return ['success' => true, 'data' => $response->json()];
            } else {
                Log::error('Failed to send transfer confirmation', [
                    'transfer_id' => $transfer->id,
                    'source_system' => $sourceSystem->name,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return ['success' => false, 'error' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending transfer confirmation', [
                'transfer_id' => $transfer->id,
                'source_system' => $sourceSystem->name,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
