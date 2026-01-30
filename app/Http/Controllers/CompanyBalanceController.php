<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use App\Models\TripCompany;
use App\Models\TripCar;
use App\Models\Trip;
use App\Models\User;
use App\Models\InternalTransportPayment;
use App\Services\AccountingCacheService;

class CompanyBalanceController extends Controller
{
    protected $accounting;

    public function __construct(AccountingCacheService $accounting)
    {
        $this->accounting = $accounting;
    }

    /**
     * عرض قائمة الشركات
     */
    public function index()
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        $owner_id = Auth::user()->owner_id;
        
        // جلب جميع الشركات التي لها شحنات
        $companies = TripCompany::where('owner_id', $owner_id)
            ->with(['company', 'trip'])
            ->withCount('cars')
            ->get()
            ->groupBy('company_id')
            ->map(function ($companyTrips, $companyId) {
                $company = $companyTrips->first()->company;
                $totalCars = $companyTrips->sum('cars_count');
                $totalShipments = $companyTrips->count();
                
                // حساب الإجمالي
                $totalAmount = 0;
                foreach ($companyTrips as $tripCompany) {
                    $carsCount = $tripCompany->cars()->count();
                    $shipping = ($tripCompany->shipping_price_per_car ?? 0) * $carsCount;
                    $clearance = ($tripCompany->clearance_per_car ?? 0) * $carsCount;
                    $transport = $tripCompany->internal_transport_total ?? 0;
                    $totalAmount += $shipping + $clearance + $transport;
                }
                
                return [
                    'id' => $companyId,
                    'name' => $company->name,
                    'phone' => $company->phone,
                    'total_cars' => $totalCars,
                    'total_shipments' => $totalShipments,
                    'total_amount' => $totalAmount,
                ];
            })
            ->values();

        return Inertia::render('CompanyBalances/Index', [
            'companies' => $companies,
        ]);
    }

    /**
     * عرض تفاصيل شركة معينة
     */
    public function show($companyId)
    {
        $this->accounting->loadAccounts(Auth::user()->owner_id);
        $owner_id = Auth::user()->owner_id;

        // جلب الشركة
        $company = User::where('id', $companyId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        // جلب جميع الشحنات (trip_companies) للشركة
        $shipments = TripCompany::where('company_id', $companyId)
            ->where('owner_id', $owner_id)
            ->with(['trip', 'cars.consignee', 'transportPayments'])
            ->get()
            ->map(function ($tripCompany) {
                $carsCount = $tripCompany->cars()->count();
                $totalTransport = $tripCompany->transportPayments->sum('amount');
                
                return [
                    'id' => $tripCompany->id,
                    'trip' => $tripCompany->trip,
                    'cars_count' => $carsCount,
                    'shipping_price_per_car' => $tripCompany->shipping_price_per_car,
                    'shipping_price_aed' => $tripCompany->shipping_price_aed,
                    'clearance_per_car' => $tripCompany->clearance_per_car,
                    'transport_payments' => $tripCompany->transportPayments,
                    'internal_transport_total' => $totalTransport,
                    'internal_transport_per_car' => $carsCount > 0 && $totalTransport 
                        ? round($totalTransport / $carsCount, 2) 
                        : 0,
                    'total_shipping' => ($tripCompany->shipping_price_per_car ?? 0) * $carsCount,
                    'total_clearance' => ($tripCompany->clearance_per_car ?? 0) * $carsCount,
                    'total_amount' => (($tripCompany->shipping_price_per_car ?? 0) * $carsCount) 
                        + (($tripCompany->clearance_per_car ?? 0) * $carsCount) 
                        + $totalTransport,
                    'cars' => $tripCompany->cars,
                ];
            });

        // حساب الإحصائيات
        $stats = [
            'total_cars' => $shipments->sum('cars_count'),
            'total_shipments' => $shipments->count(),
            'total_shipping' => $shipments->sum('total_shipping'),
            'total_clearance' => $shipments->sum('total_clearance'),
            'total_transport' => $shipments->sum('internal_transport_total'),
            'grand_total' => $shipments->sum('total_amount'),
        ];

        return Inertia::render('CompanyBalances/Show', [
            'company' => $company,
            'shipments' => $shipments,
            'stats' => $stats,
        ]);
    }

    /**
     * تحديث التخليص
     */
    public function updateShipmentFees(Request $request, $shipmentId)
    {
        $validated = $request->validate([
            'clearance_per_car' => 'required|numeric|min:0',
        ]);

        $owner_id = Auth::user()->owner_id;
        $tripCompany = TripCompany::where('id', $shipmentId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $tripCompany->update([
            'clearance_per_car' => $validated['clearance_per_car'],
        ]);

        return Response::json([
            'success' => true,
            'message' => 'تم تحديث التخليص بنجاح',
            'shipment' => $tripCompany->load('trip'),
        ]);
    }

    /**
     * إضافة دفعة نقل داخلي
     */
    public function addTransportPayment(Request $request, $shipmentId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'driver_name' => 'required|string|max:255',
            'cmr_number' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $owner_id = Auth::user()->owner_id;
        $tripCompany = TripCompany::where('id', $shipmentId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $payment = InternalTransportPayment::create([
            'trip_company_id' => $tripCompany->id,
            'amount' => $validated['amount'],
            'driver_name' => $validated['driver_name'],
            'cmr_number' => $validated['cmr_number'],
            'payment_date' => $validated['payment_date'],
            'notes' => $validated['notes'] ?? null,
            'owner_id' => $owner_id,
        ]);

        return Response::json([
            'success' => true,
            'message' => 'تم إضافة الدفعة بنجاح',
            'payment' => $payment,
            'total' => $tripCompany->transportPayments()->sum('amount'),
        ]);
    }

    /**
     * حذف دفعة نقل داخلي
     */
    public function deleteTransportPayment($paymentId)
    {
        $owner_id = Auth::user()->owner_id;
        $payment = InternalTransportPayment::where('id', $paymentId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $tripCompanyId = $payment->trip_company_id;
        $payment->delete();

        $tripCompany = TripCompany::find($tripCompanyId);
        $newTotal = $tripCompany ? $tripCompany->transportPayments()->sum('amount') : 0;

        return Response::json([
            'success' => true,
            'message' => 'تم حذف الدفعة بنجاح',
            'total' => $newTotal,
        ]);
    }

    /**
     * تحديث دفعة نقل داخلي
     */
    public function updateTransportPayment(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'driver_name' => 'required|string|max:255',
            'cmr_number' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $owner_id = Auth::user()->owner_id;
        $payment = InternalTransportPayment::where('id', $paymentId)
            ->where('owner_id', $owner_id)
            ->firstOrFail();

        $payment->update($validated);

        $tripCompany = TripCompany::find($payment->trip_company_id);
        $newTotal = $tripCompany ? $tripCompany->transportPayments()->sum('amount') : 0;

        return Response::json([
            'success' => true,
            'message' => 'تم تحديث الدفعة بنجاح',
            'payment' => $payment,
            'total' => $newTotal,
        ]);
    }
}
