<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    public function __construct(protected WhatsAppQueueService $whatsApp)
    {
    }

    /**
     * Queue client debt reminder messages (dashboard bulk send).
     */
    public function notifyClients(Request $request)
    {
        if (auth()->user() && (int) auth()->user()->type_id === 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        $validator = Validator::make($request->all(), [
            'phones' => 'required|array|min:1|max:200',
            'phones.*' => 'nullable|string|max:40',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! $this->whatsApp->isEventEnabled(WhatsAppQueueService::EVENT_CLIENT_DEBT)) {
            return Response::json([
                'error' => 'إشعارات واتساب غير مفعّلة. فعّلها من الإعدادات أولاً.',
            ], 422);
        }

        $result = $this->whatsApp->notifyClientDebtReminders($request->input('phones', []));

        return Response::json([
            'message' => 'تم إرسال التذكيرات للطابور',
            'sent' => $result['sent'] ?? 0,
            'failed' => $result['failed'] ?? 0,
            'results' => $result['results'] ?? [],
        ], 200);
    }
}
