<?php

namespace App\Http\Controllers;

use App\Models\CarDamageReport;
use App\Models\SystemConfig;
use App\Services\IranPageAccessService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Damage reports accessible from the Iran invoices page (login or shared token + PIN).
 */
class IranDamageReportController extends Controller
{
    public function __construct(protected IranPageAccessService $iranAccess)
    {
    }

    public function index(Request $request)
    {
        $ownerId = $this->iranAccess->ownerId($request);
        $q = $request->get('q', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');
        $limit = min(max((int) $request->get('limit', 25), 1), 100);

        $query = CarDamageReport::query()
            ->where('owner_id', $ownerId)
            ->orderBy('id', 'desc');

        $this->restrictToOwnUnlessAdmin($query);

        if ($from && $to && $from !== '0' && $to !== '0') {
            $query->whereBetween('created', [$from, $to]);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('driver_name', 'LIKE', '%'.$q.'%')
                    ->orWhere('cmr_number', 'LIKE', '%'.$q.'%')
                    ->orWhere('cars_info', 'LIKE', '%'.$q.'%');
            });
        }

        return Response::json($query->paginate($limit), 200);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'driver_name' => 'nullable|string|max:255',
            'cmr_number' => 'nullable|string|max:255',
            'cars_info' => 'required|array|min:1',
            'created' => 'nullable|date',
        ])->validate();

        $ownerId = $this->iranAccess->ownerId($request);
        $carsInfo = $request->get('cars_info', []);
        $totalDamage = $this->sumDamage($carsInfo);

        $report = CarDamageReport::create([
            'verification_token' => Str::uuid()->toString(),
            'driver_name' => $request->get('driver_name', ''),
            'cmr_number' => $request->get('cmr_number', ''),
            'cars_count' => count($carsInfo),
            'total_damage' => $totalDamage,
            'cars_info' => $carsInfo,
            'created' => $request->get('created', Carbon::now()->format('Y-m-d')),
            'year_date' => Carbon::now()->format('Y'),
            'owner_id' => $ownerId,
            'user_id' => Auth::id(),
        ]);

        return Response::json($report, 200);
    }

    public function destroy(Request $request)
    {
        $id = (int) $request->get('id');
        $ownerId = $this->iranAccess->ownerId($request);

        $query = CarDamageReport::query()
            ->where('owner_id', $ownerId)
            ->where('id', $id);
        $this->restrictToOwnUnlessAdmin($query);

        $report = $query->first();
        if (! $report) {
            return Response::json(['message' => 'التقرير غير موجود'], 404);
        }

        $user = Auth::user();
        if ($user && ! $this->iranAccess->isAdmin($user) && (int) $report->user_id !== (int) $user->id) {
            return Response::json(['message' => 'غير مسموح'], 403);
        }

        $report->delete();

        return Response::json(['success' => true], 200);
    }

    public function pdf(Request $request)
    {
        $id = (int) $request->get('doc_id');
        $ownerId = $this->iranAccess->ownerId($request);

        $query = CarDamageReport::query()
            ->where('owner_id', $ownerId)
            ->where('id', $id);
        $this->restrictToOwnUnlessAdmin($query);

        $report = $query->first();
        if (! $report) {
            abort(404);
        }

        $config = SystemConfig::first();

        return view('documents.damage_report', compact('report', 'config'));
    }

    private function restrictToOwnUnlessAdmin($query): void
    {
        $user = Auth::user() ?: request()->user();

        // Token guest has no user identity — do not expose all damage reports.
        if (! $user) {
            $query->whereRaw('0 = 1');

            return;
        }

        if (! $this->iranAccess->isAdmin($user)) {
            $query->where('user_id', $user->id);
        }
    }

    private function sumDamage(array $carsInfo): float
    {
        $total = 0.0;
        foreach ($carsInfo as $car) {
            $damage = str_replace('$', '', (string) ($car['damage'] ?? 0));
            $total += (float) trim($damage);
        }

        return round($total, 2);
    }
}
