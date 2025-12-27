<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\CarDamageReport;
use App\Models\SystemConfig;
use Carbon\Carbon;
use Inertia\Inertia;

class CarDamageReportController extends Controller
{
    public function index(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        return Inertia::render('DamageReport/index', [
            'owner_id' => $owner_id,
        ]);
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = CarDamageReport::find($id);
        $owner_id = Auth::user()->owner_id;
        
        // التحقق من أن المستخدم هو الادمن أو صاحب التقرير
        if (!$data || ($data->owner_id != $owner_id && Auth::user()->type_id != 1)) {
            return redirect()->route('damage_report.index');
        }
        
        return Inertia::render('DamageReport/edit', [
            'data' => $data,
            'owner_id' => $owner_id,
        ]);
    }

    public function store(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $user_id = Auth::user()->id;
        $year_date = Carbon::now()->format('Y');
        $created = $request->get('created', Carbon::now()->format('Y-m-d'));

        $driver_name = $request->get('driver_name', '');
        $cmr_number = $request->get('cmr_number', '');
        $cars_info = $request->get('cars_info', []);
        $cars_count = count($cars_info);
        
        // حساب مجموع الضرر
        $total_damage = 0;
        foreach ($cars_info as $car) {
            $damage = floatval($car['damage'] ?? 0);
            // إزالة $ من النص إذا كان موجوداً
            $damage = str_replace('$', '', $damage);
            $damage = floatval(trim($damage));
            $total_damage += $damage;
        }

        $report = CarDamageReport::create([
            'verification_token' => Str::uuid()->toString(),
            'driver_name' => $driver_name,
            'cmr_number' => $cmr_number,
            'cars_count' => $cars_count,
            'total_damage' => $total_damage,
            'cars_info' => $cars_info,
            'created' => $created,
            'year_date' => $year_date,
            'owner_id' => $owner_id,
            'user_id' => $user_id,
        ]);

        return Response::json($report, 200);
    }

    public function update(Request $request)
    {
        $id = $request->get('id');
        $owner_id = Auth::user()->owner_id;
        
        $report = CarDamageReport::find($id);
        
        if (!$report || ($report->owner_id != $owner_id && Auth::user()->type_id != 1)) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $driver_name = $request->get('driver_name', '');
        $cmr_number = $request->get('cmr_number', '');
        $cars_info = $request->get('cars_info', []);
        $cars_count = count($cars_info);
        $created = $request->get('created', $report->created);
        
        // حساب مجموع الضرر
        $total_damage = 0;
        foreach ($cars_info as $car) {
            $damage = floatval($car['damage'] ?? 0);
            $damage = str_replace('$', '', $damage);
            $damage = floatval(trim($damage));
            $total_damage += $damage;
        }

        // إنشاء token إذا لم يكن موجوداً
        if (empty($report->verification_token)) {
            $report->verification_token = Str::uuid()->toString();
        }

        $report->update([
            'driver_name' => $driver_name,
            'cmr_number' => $cmr_number,
            'cars_count' => $cars_count,
            'total_damage' => $total_damage,
            'cars_info' => $cars_info,
            'created' => $created,
        ]);

        return Response::json($report, 200);
    }

    public function getIndex(Request $request)
    {
        try {
            if (!Auth::check()) {
                return Response::json(['error' => 'Unauthorized', 'data' => []], 401);
            }
            
            $owner_id = Auth::user()->owner_id;
            $q = $request->get('q', '');
            $from = $request->get('from', 0);
            $to = $request->get('to', 0);
            $limit = $request->get('limit', 20);

            // التحقق من جميع التقارير أولاً
            $model = new CarDamageReport();
            $connection = $model->getConnection()->getName();
            Log::info('Using database connection: ' . $connection);
            $allReports = CarDamageReport::where('owner_id', $owner_id)->get();
            Log::info('Total reports in DB for owner_id ' . $owner_id . ': ' . $allReports->count());
            
            // التحقق من SQLite أيضاً (فقط في البيئة المحلية)
            try {
                if (config('app.env') === 'local') {
                    $sqliteReports = DB::connection('sync_sqlite')->table('car_damage_reports')
                        ->where('owner_id', $owner_id)
                        ->get();
                    Log::info('Total reports in SQLite for owner_id ' . $owner_id . ': ' . $sqliteReports->count());
                }
            } catch (\Exception $e) {
                // تجاهل الخطأ في السيرفر إذا لم يكن SQLite متاحاً
                Log::debug('Could not check SQLite: ' . $e->getMessage());
            }

            $data = CarDamageReport::where('owner_id', $owner_id)->orderBy('id', 'desc');

            // فقط إذا تم تحديد التواريخ بشكل صحيح، نطبق الفلتر
            if ($from && $to && $from != '0' && $to != '0' && $from != '' && $to != '') {
                $data->whereBetween('created', [$from, $to]);
                Log::info('Filtering by date: ' . $from . ' to ' . $to);
            } else {
                // إذا لم يتم تحديد التاريخ، نرجع جميع البيانات
                Log::info('No date filter applied - returning all reports');
            }

            if ($q) {
                $data->where(function ($query) use ($q) {
                    $query->where('driver_name', 'LIKE', '%' . $q . '%')
                        ->orWhere('cmr_number', 'LIKE', '%' . $q . '%')
                        ->orWhereRaw("JSON_SEARCH(cars_info, 'one', ?, NULL, '$[*].vin') IS NOT NULL", ['%' . $q . '%'])
                        ->orWhereRaw("cars_info LIKE ?", ['%"vin":"%' . $q . '%"%']);
                });
                Log::info('Filtering by search: ' . $q);
            }

            $data = $data->paginate($limit)->toArray();
            
            // التأكد من أن البيانات موجودة
            Log::info('Damage Reports Count in response: ' . count($data['data'] ?? []));
            Log::info('Owner ID: ' . $owner_id);
            Log::info('Total in pagination: ' . ($data['total'] ?? 0));
            
            return Response::json($data, 200);
        } catch (\Exception $e) {
            Log::error('Error in getIndex: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return Response::json(['error' => $e->getMessage(), 'data' => [], 'total' => 0], 500);
        }
    }

    public function makeDamageReportPdf(Request $request)
    {
        $id = $request->get('doc_id', '');
        $report = CarDamageReport::find($id);
        
        if (!$report) {
            abort(404);
        }

        $config = SystemConfig::first();
        
        return view('documents.damage_report', compact('report', 'config'));
    }

    public function verify($token)
    {
        $report = CarDamageReport::where('verification_token', $token)->firstOrFail();

        if (empty($report->verification_token)) {
            $report->verification_token = Str::uuid()->toString();
            $report->save();
        }

        $config = SystemConfig::first();
        $verificationUrl = route('damage_report.verify', $report->verification_token);

        return view('damageReportVerify', [
            'report' => $report,
            'config' => $config,
            'verificationUrl' => $verificationUrl,
        ]);
    }

    public function delete(Request $request)
    {
        $id = $request->get('id');
        $owner_id = Auth::user()->owner_id;
        
        $report = CarDamageReport::find($id);
        
        if (!$report || ($report->owner_id != $owner_id && Auth::user()->type_id != 1)) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $report->delete();
        
        return Response::json(['success' => true], 200);
    }
}
