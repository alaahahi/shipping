<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineApiController extends Controller
{
    /**
     * تحميل جميع السيارات للعمل Offline
     * يحمل فقط البيانات الأساسية
     */
    public function getAllCarsForOffline(Request $request)
    {
        try {
            $cars = Car::with(['client:id,name', 'carModel:id,title'])
                ->select([
                    'id', 'chassis', 'lot', 'make', 'year', 'color',
                    'status', 'user_id', 'car_model_id', 'price',
                    'created_at', 'updated_at'
                ])
                ->get()
                ->map(function ($car) {
                    return [
                        'id' => $car->id,
                        'chassis' => $car->chassis,
                        'lot' => $car->lot,
                        'make' => $car->make,
                        'year' => $car->year,
                        'color' => $car->color,
                        'status' => $car->status,
                        'user_id' => $car->user_id,
                        'car_model_id' => $car->car_model_id,
                        'price' => $car->price,
                        'client_name' => $car->client->name ?? '',
                        'model_name' => $car->carModel->title ?? '',
                        'created_at' => $car->created_at,
                        'updated_at' => $car->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'cars' => $cars,
                'count' => $cars->count(),
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading cars for offline: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحميل سيارات عميل معين للعمل Offline
     */
    public function getUserCarsForOffline(Request $request, $userId)
    {
        try {
            $cars = Car::where('user_id', $userId)
                ->with(['client:id,name', 'carModel:id,title'])
                ->select([
                    'id', 'chassis', 'lot', 'make', 'year', 'color',
                    'status', 'user_id', 'car_model_id', 'price',
                    'created_at', 'updated_at'
                ])
                ->get()
                ->map(function ($car) {
                    return [
                        'id' => $car->id,
                        'chassis' => $car->chassis,
                        'lot' => $car->lot,
                        'make' => $car->make,
                        'year' => $car->year,
                        'color' => $car->color,
                        'status' => $car->status,
                        'user_id' => $car->user_id,
                        'car_model_id' => $car->car_model_id,
                        'price' => $car->price,
                        'client_name' => $car->client->name ?? '',
                        'model_name' => $car->carModel->title ?? '',
                        'created_at' => $car->created_at,
                        'updated_at' => $car->updated_at,
                    ];
                });

            $client = User::find($userId);

            return response()->json([
                'success' => true,
                'cars' => $cars,
                'client' => $client ? [
                    'id' => $client->id,
                    'name' => $client->name,
                    'phone' => $client->phone,
                ] : null,
                'count' => $cars->count(),
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading user cars for offline: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل تحميل البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * البحث عن سيارة بالشاصي
     */
    public function searchByChassis(Request $request)
    {
        try {
            $chassis = $request->input('chassis');
            
            if (empty($chassis)) {
                return response()->json([
                    'success' => false,
                    'message' => 'الرجاء إدخال رقم الشاصي'
                ], 400);
            }

            $cars = Car::where('chassis', 'LIKE', "%{$chassis}%")
                ->with(['client:id,name', 'carModel:id,title'])
                ->get()
                ->map(function ($car) {
                    return [
                        'id' => $car->id,
                        'chassis' => $car->chassis,
                        'lot' => $car->lot,
                        'make' => $car->make,
                        'year' => $car->year,
                        'color' => $car->color,
                        'status' => $car->status,
                        'user_id' => $car->user_id,
                        'price' => $car->price,
                        'client_name' => $car->client->name ?? '',
                        'model_name' => $car->carModel->title ?? '',
                    ];
                });

            return response()->json([
                'success' => true,
                'cars' => $cars,
                'count' => $cars->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching by chassis: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل البحث',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * البحث عن سيارة بالكاتي (رقم اللوحة)
     */
    public function searchByLot(Request $request)
    {
        try {
            $lot = $request->input('lot');
            
            if (empty($lot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'الرجاء إدخال رقم الكاتي'
                ], 400);
            }

            $cars = Car::where('lot', 'LIKE', "%{$lot}%")
                ->with(['client:id,name', 'carModel:id,title'])
                ->get()
                ->map(function ($car) {
                    return [
                        'id' => $car->id,
                        'chassis' => $car->chassis,
                        'lot' => $car->lot,
                        'make' => $car->make,
                        'year' => $car->year,
                        'color' => $car->color,
                        'status' => $car->status,
                        'user_id' => $car->user_id,
                        'price' => $car->price,
                        'client_name' => $car->client->name ?? '',
                        'model_name' => $car->carModel->title ?? '',
                    ];
                });

            return response()->json([
                'success' => true,
                'cars' => $cars,
                'count' => $cars->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching by lot: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل البحث',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حفظ دفعة offline
     */
    public function savePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'car_id' => 'required|exists:cars,id',
                'amount' => 'required|numeric|min:0',
                'user_id' => 'required|exists:users,id',
                'note' => 'nullable|string',
                'local_id' => 'nullable|string', // للتعرف على العمليات المحلية
            ]);

            $transaction = Transactions::create([
                'car_id' => $validated['car_id'],
                'amount' => $validated['amount'],
                'user_id' => $validated['user_id'],
                'note' => $validated['note'] ?? '',
                'type' => 'payment',
                'date' => now(),
                'created_by' => auth()->id() ?? $validated['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'message' => 'تم حفظ الدفعة بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving payment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل حفظ الدفعة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حفظ سحب offline
     */
    public function saveWithdrawal(Request $request)
    {
        try {
            $validated = $request->validate([
                'car_id' => 'required|exists:cars,id',
                'amount' => 'required|numeric|min:0',
                'user_id' => 'required|exists:users,id',
                'note' => 'nullable|string',
                'local_id' => 'nullable|string',
            ]);

            $transaction = Transactions::create([
                'car_id' => $validated['car_id'],
                'amount' => -abs($validated['amount']), // سحب يكون سالب
                'user_id' => $validated['user_id'],
                'note' => $validated['note'] ?? '',
                'type' => 'withdrawal',
                'date' => now(),
                'created_by' => auth()->id() ?? $validated['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'message' => 'تم حفظ السحب بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving withdrawal: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل حفظ السحب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على دفعات سيارة معينة
     */
    public function getCarPayments(Request $request, $carId)
    {
        try {
            $payments = Transactions::where('car_id', $carId)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'car_id' => $transaction->car_id,
                        'amount' => $transaction->amount,
                        'type' => $transaction->amount >= 0 ? 'payment' : 'withdrawal',
                        'note' => $transaction->note,
                        'date' => $transaction->date,
                        'created_at' => $transaction->created_at,
                    ];
                });

            $totalPayments = $payments->where('type', 'payment')->sum('amount');
            $totalWithdrawals = abs($payments->where('type', 'withdrawal')->sum('amount'));

            return response()->json([
                'success' => true,
                'payments' => $payments,
                'summary' => [
                    'total_payments' => $totalPayments,
                    'total_withdrawals' => $totalWithdrawals,
                    'balance' => $totalPayments - $totalWithdrawals,
                    'count' => $payments->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting car payments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل جلب البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

