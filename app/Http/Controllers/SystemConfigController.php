<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SystemConfigController extends Controller
{
    /**
     * جلب إعدادات النظام
     */
    public function index()
    {
        SystemConfig::ensureMediaColumns();

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
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = SystemConfig::first();

        if (! $config) {
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
            if ($request->has('first_title_ar')) {
                $updateData['first_title_ar'] = $request->first_title_ar;
            }
            if ($request->has('first_title_kr')) {
                $updateData['first_title_kr'] = $request->first_title_kr;
            }
            if ($request->has('second_title_ar')) {
                $updateData['second_title_ar'] = $request->second_title_ar;
            }
            if ($request->has('second_title_kr')) {
                $updateData['second_title_kr'] = $request->second_title_kr;
            }
            if ($request->has('third_title_ar')) {
                $updateData['third_title_ar'] = $request->third_title_ar;
            }
            if ($request->has('third_title_kr')) {
                $updateData['third_title_kr'] = $request->third_title_kr;
            }
            if ($request->has('default_price_s') && (int) auth()->user()->type_id === 1) {
                $updateData['default_price_s'] = $request->default_price_s;
            }
            if ($request->has('default_price_p') && (int) auth()->user()->type_id === 1) {
                $updateData['default_price_p'] = $request->default_price_p;
            }
            if ($request->has('usd_to_aed_rate')) {
                $updateData['usd_to_aed_rate'] = $request->usd_to_aed_rate;
            }
            if ($request->has('usd_to_dinar_rate')) {
                $updateData['usd_to_dinar_rate'] = $request->usd_to_dinar_rate;
            }
            if ($request->has('contract_terms')) {
                $updateData['contract_terms'] = $request->contract_terms;
            }
            if ($request->has('contract_terms_2')) {
                $updateData['contract_terms_2'] = $request->contract_terms_2;
            }
            if ($request->has('external_contract_terms')) {
                $updateData['external_contract_terms'] = $request->external_contract_terms;
            }
            if ($request->has('external_contract_terms_2')) {
                $updateData['external_contract_terms_2'] = $request->external_contract_terms_2;
            }
            if ($request->has('contract_template')) {
                $updateData['contract_template'] = (int) $request->contract_template;
            }
            if ($request->has('contract_currency')) {
                $updateData['contract_currency'] = $request->contract_currency;
            }
            if ($request->has('primary_color')) {
                $updateData['primary_color'] = $request->primary_color ?: '#c00';
            }

            $config->update($updateData);
        }

        return Response::json([
            'message' => 'تم تحديث الإعدادات بنجاح',
            'config' => $config->fresh(),
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

        SystemConfig::ensureMediaColumns();

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = $this->firstOrCreateConfig();

        try {
            $relativePath = $this->storeSystemImage($request->file('logo'), 'logo');
        } catch (\Throwable $e) {
            Log::error('Logo upload store failed', ['error' => $e->getMessage()]);

            return Response::json(['error' => 'فشل حفظ ملف الشعار على السيرفر'], 500);
        }

        $oldLogo = $config->logo;
        if (! $this->persistMediaPath($config->id, 'logo', $relativePath)) {
            File::delete(public_path($relativePath));

            return Response::json([
                'error' => 'فشل حفظ مسار الشعار في قاعدة البيانات. تأكد من تشغيل migrate لعمود logo.',
            ], 500);
        }

        if ($oldLogo && $oldLogo !== $relativePath) {
            $this->deleteStoredMedia($oldLogo);
        }

        $fresh = SystemConfig::find($config->id);

        return Response::json([
            'message' => 'تم تحديث الشعار بنجاح',
            'config' => $fresh,
            'logo_url' => SystemConfig::resolveLogoUrl($relativePath),
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

        SystemConfig::ensureMediaColumns();

        $config = SystemConfig::first();
        if ($config && $config->logo) {
            $this->deleteStoredMedia($config->logo);
            $config->update(['logo' => null]);
        }

        return Response::json([
            'message' => 'تم حذف الشعار المخصص',
            'config' => $config?->fresh(),
            'logo_url' => SystemConfig::resolveLogoUrl(),
        ], 200);
    }

    /**
     * رفع خلفية صفحة تسجيل الدخول
     */
    public function uploadLoginBackground(Request $request)
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        SystemConfig::ensureMediaColumns();

        $validator = Validator::make($request->all(), [
            'login_background' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = $this->firstOrCreateConfig();

        try {
            $relativePath = $this->storeSystemImage($request->file('login_background'), 'login_bg');
        } catch (\Throwable $e) {
            Log::error('Login background upload store failed', ['error' => $e->getMessage()]);

            return Response::json(['error' => 'فشل حفظ ملف الخلفية على السيرفر'], 500);
        }

        $oldBg = $config->login_background;
        if (! $this->persistMediaPath($config->id, 'login_background', $relativePath)) {
            File::delete(public_path($relativePath));

            return Response::json([
                'error' => 'فشل حفظ مسار الخلفية في قاعدة البيانات. تأكد من تشغيل migrate.',
            ], 500);
        }

        if ($oldBg && $oldBg !== $relativePath) {
            $this->deleteStoredMedia($oldBg);
        }

        $fresh = SystemConfig::find($config->id);

        return Response::json([
            'message' => 'تم تحديث خلفية تسجيل الدخول بنجاح',
            'config' => $fresh,
            'login_background_url' => SystemConfig::resolveLoginBackgroundUrl($relativePath),
        ], 200);
    }

    /**
     * حذف خلفية تسجيل الدخول المخصصة
     */
    public function deleteLoginBackground()
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        SystemConfig::ensureMediaColumns();

        $config = SystemConfig::first();
        if ($config && $config->login_background) {
            $this->deleteStoredMedia($config->login_background);
            $config->update(['login_background' => null]);
        }

        return Response::json([
            'message' => 'تم حذف خلفية تسجيل الدخول',
            'config' => $config?->fresh(),
            'login_background_url' => null,
        ], 200);
    }

    /**
     * Serve stored system media (logo / login background) through Laravel.
     * Fixes hosts where /uploads/* is not publicly reachable.
     */
    public function serveSystemMedia(string $file)
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
            abort(404);
        }

        $absolute = SystemConfig::resolveMediaAbsolutePath('img/system/'.$file)
            ?: SystemConfig::resolveMediaAbsolutePath('uploads/system/'.$file)
            ?: SystemConfig::resolveMediaAbsolutePath('storage/system/'.$file);

        if (! $absolute) {
            abort(404);
        }

        return response()->file($absolute, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    protected function firstOrCreateConfig(): SystemConfig
    {
        $config = SystemConfig::first();
        if ($config) {
            return $config;
        }

        return SystemConfig::create([
            'first_title_ar' => '',
            'first_title_kr' => '',
            'second_title_ar' => '',
            'second_title_kr' => '',
            'third_title_ar' => '',
            'third_title_kr' => '',
        ]);
    }

    /**
     * Persist media path via query builder (reliable on SQLite after ensureMediaColumns).
     */
    protected function persistMediaPath(int $id, string $column, ?string $path): bool
    {
        SystemConfig::ensureMediaColumns();

        try {
            DB::table('system_config')->where('id', $id)->update([$column => $path]);

            return true;
        } catch (\Throwable $e) {
            Log::error('persistMediaPath failed', [
                'column' => $column,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function deleteStoredMedia(?string $storedPath): void
    {
        $absolute = SystemConfig::resolveMediaAbsolutePath($storedPath);
        if ($absolute && File::exists($absolute)) {
            File::delete($absolute);
        }
    }

    /**
     * Store under public/img/system (same public area as existing /img/logo.*).
     */
    protected function storeSystemImage($file, string $prefix): string
    {
        $dir = public_path('img/system');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $prefix.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $file->move($dir, $filename);

        $relativePath = 'img/system/'.$filename;
        if (! is_file(public_path($relativePath))) {
            throw new \RuntimeException('File missing after move: '.$relativePath);
        }

        return $relativePath;
    }
}
