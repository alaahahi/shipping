<?php

namespace App\Http\Controllers;

use App\Models\ExternalCar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ]);

        return Response::json($car, 200);
    }

    public function update(Request $request)
    {
        $ownerId = Auth::user()->owner_id;
        $car = ExternalCar::find($request->id);

        if (!$car || !$this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }

        $validated = $this->validatePayload($request);
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

        $query = ExternalCar::where('owner_id', $ownerId)->orderByDesc('id');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('dealer_name', 'LIKE', '%' . $q . '%')
                    ->orWhere('car_type', 'LIKE', '%' . $q . '%')
                    ->orWhere('car_number', 'LIKE', '%' . $q . '%')
                    ->orWhere('car_color', 'LIKE', '%' . $q . '%');
            });
        }

        $totals = (clone $query)->selectRaw(
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

        if (!$car || !$this->canAccess($car, $ownerId)) {
            return Response::json(['error' => 'غير مصرح'], 403);
        }

        $car->delete();

        return Response::json(['ok' => true], 200);
    }

    private function validatePayload(Request $request): array
    {
        $request->validate([
            'dealer_name' => 'required|string|max:255',
            'car_type' => 'required|string|max:255',
            'car_number' => 'required|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'car_color' => 'nullable|string|max:255',
            'paid_dollar' => 'nullable|integer|min:0',
            'paid_dinar' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:2000',
            'date' => 'nullable|date',
        ]);

        return [
            'dealer_name' => trim((string) $request->dealer_name),
            'car_type' => trim((string) $request->car_type),
            'car_number' => trim((string) $request->car_number),
            'year' => $request->filled('year') ? (int) $request->year : null,
            'car_color' => trim((string) ($request->car_color ?? '')),
            'paid_dollar' => (int) ($request->paid_dollar ?? 0),
            'paid_dinar' => (int) ($request->paid_dinar ?? 0),
            'note' => trim((string) ($request->note ?? '')),
            'date' => $request->date ?: Carbon::now()->format('Y-m-d'),
        ];
    }

    private function canAccess(ExternalCar $car, int $ownerId): bool
    {
        return (int) $car->owner_id === (int) $ownerId || (int) Auth::user()->type_id === 1;
    }
}
