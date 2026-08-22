<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyCashBoxHistoryRequest;
use App\Services\CashBoxHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use RuntimeException;

class CashBoxHistoryController extends Controller
{
    public function __construct(
        protected CashBoxHistoryService $historyService
    ) {
    }

    /**
     * تاريخ رصيد الصندوق — يُستدعى فقط عند فتح التبويب (lazy).
     */
    public function index(Request $request)
    {
        $ownerId = (int) Auth::user()->owner_id;
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(10, (int) $request->get('per_page', 50)));
        $beforeId = $request->filled('before_id') ? (int) $request->get('before_id') : null;

        try {
            $data = $this->historyService->history($ownerId, $page, $perPage, $beforeId);

            return Response::json($data, 200);
        } catch (RuntimeException $e) {
            return Response::json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * تأكيد مطابقة حركة مع الكاش الفعلي (مسؤول فقط).
     */
    public function verify(VerifyCashBoxHistoryRequest $request)
    {
        $ownerId = (int) Auth::user()->owner_id;
        $validated = $request->validated();

        try {
            $verification = $this->historyService->verify(
                $ownerId,
                (int) $validated['transaction_id'],
                (int) Auth::id(),
                $validated['note'] ?? null
            );

            $history = $this->historyService->history($ownerId, 1, 50);

            return Response::json([
                'message' => 'تم توثيق الرصيد. كل الحركات حتى هذه النقطة أصبحت موثوقة.',
                'verification' => [
                    'id' => $verification->id,
                    'transaction_id' => $verification->transaction_id,
                    'ledger_balance_at_confirm' => (float) $verification->ledger_balance_at_confirm,
                    'ledger_balance_dinar_at_confirm' => (float) $verification->ledger_balance_dinar_at_confirm,
                    'verified_at' => $verification->verified_at,
                ],
                'history' => $history,
            ], 200);
        } catch (RuntimeException $e) {
            return Response::json(['message' => $e->getMessage()], 422);
        }
    }
}
