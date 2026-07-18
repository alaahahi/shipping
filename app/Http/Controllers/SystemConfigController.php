<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\File;
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
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }
        $validator = Validator::make($request->all(), [
            'first_title_ar' => 'nullable|string|max:255',
            'first_title_kr' => 'nullable|string|max:255',
            'second_title_ar' => 'nullable|string|max:255',
            'second_title_kr' => 'nullable|string|max:255',
            'third_title_ar' => 'nullable|string|max:255',
            'third_title_kr' => 'nullable|string|max:255',
            'default_price_s' => 'nullable|array',
            'default_price_p' => 'nullable|array',
            'usd_to_aed_rate' => 'nullable|numeric|min:0',
            'usd_to_dinar_rate' => 'nullable|numeric|min:0',
            'contract_terms' => 'nullable|array',
            'contract_terms_2' => 'nullable|array',
            'external_contract_terms' => 'nullable|array',
            'external_contract_terms_2' => 'nullable|array',
            'contract_template' => 'nullable|in:1,2,3',
            'contract_currency' => 'nullable|in:usd,dinar',
            'primary_color' => 'nullable|string|max:20',
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
                'usd_to_aed_rate' => $request->usd_to_aed_rate ?? 3.6725,
                'usd_to_dinar_rate' => $request->usd_to_dinar_rate ?? 150.00,
                'contract_terms' => $request->contract_terms ?? null,
                'contract_terms_2' => $request->contract_terms_2 ?? null,
                'external_contract_terms' => $request->external_contract_terms ?? null,
                'external_contract_terms_2' => $request->external_contract_terms_2 ?? null,
                'contract_template' => $request->contract_template ?? 1,
                'contract_currency' => $request->contract_currency ?? 'usd',
                'primary_color' => $request->primary_color ?? '#c00',
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
            if ($request->has('usd_to_aed_rate')) $updateData['usd_to_aed_rate'] = $request->usd_to_aed_rate;
            if ($request->has('usd_to_dinar_rate')) $updateData['usd_to_dinar_rate'] = $request->usd_to_dinar_rate;
            if ($request->has('contract_terms')) $updateData['contract_terms'] = $request->contract_terms;
            if ($request->has('contract_terms_2')) $updateData['contract_terms_2'] = $request->contract_terms_2;
            if ($request->has('external_contract_terms')) $updateData['external_contract_terms'] = $request->external_contract_terms;
            if ($request->has('external_contract_terms_2')) $updateData['external_contract_terms_2'] = $request->external_contract_terms_2;
            if ($request->has('contract_template')) $updateData['contract_template'] = (int) $request->contract_template;
            if ($request->has('contract_currency')) $updateData['contract_currency'] = $request->contract_currency;
            if ($request->has('primary_color')) $updateData['primary_color'] = $request->primary_color ?: '#c00';
            
            $config->update($updateData);
        }

        return Response::json([
            'message' => 'تم تحديث الإعدادات بنجاح',
            'config' => $config->fresh()
        ], 200);
    }

    /**
     * رفع شعار النظام وتخزين المسار في system_config.logo
     */
    public function uploadLogo(Request $request)
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = SystemConfig::first();
        if (! $config) {
            $config = SystemConfig::create([
                'first_title_ar' => '',
                'first_title_kr' => '',
                'second_title_ar' => '',
                'second_title_kr' => '',
                'third_title_ar' => '',
                'third_title_kr' => '',
            ]);
        }

        $dir = public_path('storage/system');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if ($config->logo && File::exists(public_path($config->logo))) {
            File::delete(public_path($config->logo));
        }

        $file = $request->file('logo');
        $filename = 'logo_'.time().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $filename);

        $relativePath = 'storage/system/'.$filename;
        $config->update(['logo' => $relativePath]);
        $fresh = $config->fresh();

        return Response::json([
            'message' => 'تم تحديث الشعار بنجاح',
            'config' => $fresh,
            'logo_url' => $fresh->logo_url,
        ], 200);
    }

    /**
     * حذف الشعار المخصص والرجوع للافتراضي
     */
    public function deleteLogo()
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        $config = SystemConfig::first();
        if ($config && $config->logo) {
            if (File::exists(public_path($config->logo))) {
                File::delete(public_path($config->logo));
            }
            $config->update(['logo' => null]);
        }

        return Response::json([
            'message' => 'تم حذف الشعار المخصص',
            'config' => $config?->fresh(),
            'logo_url' => SystemConfig::resolveLogoUrl(),
        ], 200);
    }
}
