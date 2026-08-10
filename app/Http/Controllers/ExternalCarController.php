<?php

namespace App\Http\Controllers;

use App\Models\ExternalCar;
use App\Models\ExternalCarPayment;
use App\Models\SystemConfig;
use App\Services\CarExpensesWalletPostingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class ExternalCarController extends Controller
{
    public function index()
    {
        return Inertia::render('ExternalCars/Index');
    }

    public function store(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $validated = $this->validatePayload($request);

        $car = ExternalCar::create([
            ...$validated,
            'owner_id' => $ownerId,
            'user_id' => Auth::id(),
            'paid_dollar' => 0,
            'paid_dinar' => 0,
        ]);

        return Response::json($car, 200);
    }

    public function update(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $car = ExternalCar::find($request->id);

        if (! $car || ! $this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }

        $validated = $this->validatePayload($request);
        // Paid totals are derived from payments — never overwrite from car form.
        unset($validated['paid_dollar'], $validated['paid_dinar']);
        $car->update($validated);

        return Response::json($car->fresh(), 200);
    }

    public function getIndex(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $q = trim((string) $request->get('q', ''));
        $from = $request->get('from');
        $to = $request->get('to');
        $limit = (int) ($request->get('limit', 50));

        $query = ExternalCar::where('owner_id', $ownerId)
            ->withCount([
                'payments as unposted_count' => fn ($q) => $q->where('is_posted', false),
            ])
            ->orderByDesc('id');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('dealer_name', 'LIKE', '%'.$q.'%')
                    ->orWhere('vin', 'LIKE', '%'.$q.'%')
                    ->orWhere('car_type', 'LIKE', '%'.$q.'%')
                    ->orWhere('car_number', 'LIKE', '%'.$q.'%')
                    ->orWhere('car_color', 'LIKE', '%'.$q.'%');
            });
        }

        $totals = (clone $query)->reorder()->selectRaw(
            'COALESCE(SUM(paid_dollar), 0) as total_paid_dollar, COALESCE(SUM(paid_dinar), 0) as total_paid_dinar'
        )->first();

        $data = $query->paginate($limit)->toArray();
        $data['total_paid_dollar'] = (int) ($totals->total_paid_dollar ?? 0);
        $data['total_paid_dinar'] = (int) ($totals->total_paid_dinar ?? 0);

        return Response::json($data, 200);
    }

    public function delete(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $car = ExternalCar::find($request->id);

        if (! $car || ! $this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }

        DB::transaction(function () use ($car) {
            $car->payments()->delete();
            $car->delete();
        });

        return Response::json(['ok' => true], 200);
    }

    public function getPayments(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $car = ExternalCar::with(['payments' => fn ($q) => $q->orderByDesc('id')])
            ->find($request->get('external_car_id'));

        if (! $car || ! $this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }

        return Response::json([
            'car' => $car,
            'payments' => $car->payments,
            'paid_dollar' => (int) $car->paid_dollar,
            'paid_dinar' => (int) $car->paid_dinar,
        ], 200);
    }

    public function storePayment(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $request->validate([
            'external_car_id' => 'required|integer|exists:external_cars,id',
            'amount_dollar' => 'nullable|integer|min:0',
            'amount_dinar' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:2000',
            'created' => 'nullable|date',
        ]);

        $car = ExternalCar::find($request->external_car_id);
        if (! $car || ! $this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }
        $amountDollar = (int) ($request->amount_dollar ?? 0);
        $amountDinar = (int) ($request->amount_dinar ?? 0);
        if ($amountDollar <= 0 && $amountDinar <= 0) {
            return Response::json(['error' => 'أدخل مبلغاً بالدولار أو الدينار'], 422);
        }

        $payment = DB::transaction(function () use ($request, $car, $ownerId, $amountDollar, $amountDinar) {
            $payment = ExternalCarPayment::create([
                'external_car_id' => $car->id,
                'owner_id' => $ownerId,
                'user_id' => Auth::id(),
                'amount_dollar' => $amountDollar,
                'amount_dinar' => $amountDinar,
                'note' => trim((string) ($request->note ?? '')),
                'created' => $request->created ?: Carbon::now()->format('Y-m-d'),
            ]);
            $car->syncPaidTotals();

            return $payment;
        });

        return Response::json([
            'payment' => $payment,
            'car' => $this->externalCarPayload($car->fresh()),
        ], 200);
    }

    public function deletePayment(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $payment = ExternalCarPayment::find($request->id);

        if (! $payment) {
            return Response::json(['error' => 'الدفعة غير موجودة'], 404);
        }

        $car = ExternalCar::find($payment->external_car_id);
        if (! $car || ! $this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }
        if ($payment->is_posted) {
            return Response::json(['error' => 'هذه الدفعة مُرحَّلة — لا يمكن حذفها'], 422);
        }

        DB::transaction(function () use ($payment, $car) {
            $payment->delete();
            $car->syncPaidTotals();
        });

        return Response::json([
            'ok' => true,
            'car' => $this->externalCarPayload($car->fresh()),
        ], 200);
    }

    public function postExpensesToWallet(Request $request, CarExpensesWalletPostingService $poster)
    {
        $ownerId = Auth::user()->owner_id;
        $car = ExternalCar::with('payments')->find($request->id);

        if (! $car || ! $this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }

        $unposted = $car->payments->filter(fn ($payment) => ! $payment->is_posted);
        $amountDollar = (int) $unposted->sum('amount_dollar');
        $amountDinar = (int) $unposted->sum('amount_dinar');

        if ($amountDollar <= 0 && $amountDinar <= 0) {
            return Response::json(['error' => 'لا يوجد مصروف جديد للترحيل'], 422);
        }

        $note = trim(sprintf(
            'سيارة خارجية %s %s %s %s %s$ / %s د',
            $car->dealer_name ?? '',
            $car->car_type ?? '',
            $car->vin ?: ($car->car_number ?? ''),
            $car->expenses_posted ? 'إضافي' : 'إجمالي',
            number_format($amountDollar),
            number_format($amountDinar)
        ));
        $userNote = mb_substr(trim((string) $request->input('note', '')), 0, 500);
        if ($userNote !== '') {
            $note .= ' — '.$userNote;
        }

        try {
            $result = $poster->postTotalsToDefaultWallet($amountDollar, $amountDinar, $note);
        } catch (\RuntimeException $e) {
            return Response::json(['error' => $e->getMessage()], 422);
        }

        ExternalCarPayment::query()
            ->whereIn('id', $unposted->pluck('id')->all())
            ->update(['is_posted' => true]);

        $car->update([
            'expenses_posted' => true,
            'expenses_posted_at' => now(),
        ]);

        return Response::json([
            'ok' => true,
            'car' => $this->externalCarPayload($car->fresh()),
            'posted' => $result,
        ], 200);
    }

    public function printDetails(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $car = ExternalCar::with(['payments' => fn ($q) => $q->orderBy('id')])
            ->find($request->get('external_car_id'));

        if (! $car || ! $this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }

        $config = SystemConfig::first();
        $totalAmountDollar = (int) $car->payments->sum('amount_dollar');
        $totalAmountDinar = (int) $car->payments->sum('amount_dinar');

        return view('receiptExternalCar', compact(
            'car',
            'config',
            'totalAmountDollar',
            'totalAmountDinar'
        ));
    }

    private function validatePayload(Request $request): array
    {
        $request->validate([
            'dealer_name' => 'required|string|max:255',
            'vin' => 'nullable|string|max:32',
            'car_type' => 'required|string|max:255',
            'car_number' => 'required|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'car_color' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
            'date' => 'nullable|date',
        ]);

        return [
            'dealer_name' => trim((string) $request->dealer_name),
            'vin' => trim((string) ($request->vin ?? '')) ?: null,
            'car_type' => trim((string) $request->car_type),
            'car_number' => trim((string) $request->car_number),
            'year' => $request->filled('year') ? (int) $request->year : null,
            'car_color' => trim((string) ($request->car_color ?? '')),
            'note' => trim((string) ($request->note ?? '')),
            'date' => $request->date ?: Carbon::now()->format('Y-m-d'),
        ];
    }

    private function canAccess(ExternalCar $car, int $ownerId): bool
    {
        return (int) $car->owner_id === (int) $ownerId || (int) Auth::user()->type_id === 1;
    }

    private function externalCarPayload(ExternalCar $car): ExternalCar
    {
        $car->loadCount([
            'payments as unposted_count' => fn ($q) => $q->where('is_posted', false),
        ]);

        return $car;
    }
}
