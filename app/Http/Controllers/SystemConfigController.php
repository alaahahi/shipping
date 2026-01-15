<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Validator;

class SystemConfigController extends Controller
{
    /**
     * جلب إعدادات النظام
     */
    public function index()
    {
        $config = SystemConfig::first();
        
        // إذا لم يكن هناك سجل، إنشاء واحد افتراضي
        if (!$config) {
            $config = SystemConfig::create([
                'first_title_ar' => '',
                'first_title_kr' => '',
                'second_title_ar' => '',
                'second_title_kr' => '',
                'third_title_ar' => '',
                'third_title_kr' => '',
            ]);
        }
        
        return Response::json($config, 200);
    }

    /**
     * تحديث إعدادات النظام
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_title_ar' => 'nullable|string|max:255',
            'first_title_kr' => 'nullable|string|max:255',
            'second_title_ar' => 'nullable|string|max:255',
            'second_title_kr' => 'nullable|string|max:255',
            'third_title_ar' => 'nullable|string|max:255',
            'third_title_kr' => 'nullable|string|max:255',
            'default_price_s' => 'nullable|array',
            'default_price_p' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $config = SystemConfig::first();
        
        if (!$config) {
            $config = SystemConfig::create([
                'first_title_ar' => $request->first_title_ar ?? '',
                'first_title_kr' => $request->first_title_kr ?? '',
                'second_title_ar' => $request->second_title_ar ?? '',
                'second_title_kr' => $request->second_title_kr ?? '',
                'third_title_ar' => $request->third_title_ar ?? '',
                'third_title_kr' => $request->third_title_kr ?? '',
                'default_price_s' => $request->default_price_s ?? [],
                'default_price_p' => $request->default_price_p ?? [],
            ]);
        } else {
            $updateData = [];
            if ($request->has('first_title_ar')) $updateData['first_title_ar'] = $request->first_title_ar;
            if ($request->has('first_title_kr')) $updateData['first_title_kr'] = $request->first_title_kr;
            if ($request->has('second_title_ar')) $updateData['second_title_ar'] = $request->second_title_ar;
            if ($request->has('second_title_kr')) $updateData['second_title_kr'] = $request->second_title_kr;
            if ($request->has('third_title_ar')) $updateData['third_title_ar'] = $request->third_title_ar;
            if ($request->has('third_title_kr')) $updateData['third_title_kr'] = $request->third_title_kr;
            if ($request->has('default_price_s')) $updateData['default_price_s'] = $request->default_price_s;
            if ($request->has('default_price_p')) $updateData['default_price_p'] = $request->default_price_p;
            
            $config->update($updateData);
        }

        return Response::json([
            'message' => 'تم تحديث الإعدادات بنجاح',
            'config' => $config->fresh()
        ], 200);
    }
}
