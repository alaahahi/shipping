<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\LicenseService;
use App\Models\License;
use App\Models\UserType;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminLicenseController extends Controller
{
    /**
     * التحقق من أن المستخدم أدمن
     */
    protected function isAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $adminTypeId = UserType::where('name', 'admin')->first()?->id;
        return $user->type_id == $adminTypeId;
    }

    /**
     * عرض صفحة إدارة الترخيصات
     */
    public function index()
    {
        if (!$this->isAdmin()) {
            abort(403, 'غير مصرح لك بالوصول');
        }

        $licenses = License::orderBy('created_at', 'desc')->get()->map(function ($license) {
            return [
                'id' => $license->id,
                'domain' => $license->domain,
                'fingerprint' => substr($license->fingerprint ?? '', 0, 20) . '...',
                'type' => $license->type,
                'is_active' => $license->is_active,
                'activated_at' => $license->activated_at?->format('Y-m-d H:i:s'),
                'expires_at' => $license->expires_at?->format('Y-m-d H:i:s'),
                'days_remaining' => $license->days_remaining,
                'last_verified_at' => $license->last_verified_at?->format('Y-m-d H:i:s'),
                'created_at' => $license->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('Admin/LicenseManagement', [
            'licenses' => $licenses,
        ]);
    }

    /**
     * الحصول على قائمة الترخيصات (API)
     */
    public function list(): JsonResponse
    {
        if (!$this->isAdmin()) {
            return Response::json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $licenses = License::orderBy('created_at', 'desc')->get();

        return Response::json([
            'success' => true,
            'licenses' => $licenses
        ], 200);
    }

    /**
     * إنشاء ترخيص جديد
     */
    public function create(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return Response::json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $request->validate([
            'domain' => 'required|string',
            'type' => 'required|in:trial,standard,premium',
            'expires_at' => 'nullable|date',
            'max_installations' => 'nullable|integer|min:1',
        ]);

        try {
            $fingerprint = LicenseService::getServerFingerprint();
            
            $licenseData = [
                'domain' => $request->domain,
                'fingerprint' => $fingerprint,
                'type' => $request->type,
                'expires_at' => $request->expires_at,
                'max_installations' => $request->max_installations ?? 1,
                'issued_at' => now()->toDateTimeString(),
            ];

            $licenseKey = LicenseService::encryptLicenseKey($licenseData);

            $license = License::create([
                'license_key' => \Illuminate\Support\Facades\Crypt::encryptString($licenseKey),
                'domain' => $request->domain,
                'fingerprint' => $fingerprint,
                'type' => $request->type,
                'max_installations' => $request->max_installations ?? 1,
                'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
                'is_active' => false, // غير مفعل حتى يتم التفعيل
            ]);

            return Response::json([
                'success' => true,
                'message' => 'تم إنشاء الترخيص بنجاح',
                'license' => $license,
                'license_key' => $licenseKey, // إرجاع المفتاح للمسؤول
            ], 201);

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'فشل إنشاء الترخيص: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * تفعيل/إلغاء تفعيل ترخيص
     */
    public function toggle(Request $request, $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return Response::json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $license = License::findOrFail($id);
        $license->is_active = !$license->is_active;
        
        if ($license->is_active && !$license->activated_at) {
            $license->activated_at = now();
        }
        
        $license->save();

        return Response::json([
            'success' => true,
            'message' => $license->is_active ? 'تم تفعيل الترخيص' : 'تم إلغاء تفعيل الترخيص',
            'license' => $license
        ], 200);
    }

    /**
     * حذف ترخيص
     */
    public function destroy($id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return Response::json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $license = License::findOrFail($id);
        $license->delete();

        return Response::json([
            'success' => true,
            'message' => 'تم حذف الترخيص بنجاح'
        ], 200);
    }

    /**
     * عرض تفاصيل ترخيص
     */
    public function show($id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return Response::json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $license = License::findOrFail($id);

        return Response::json([
            'success' => true,
            'license' => [
                'id' => $license->id,
                'domain' => $license->domain,
                'fingerprint' => $license->fingerprint,
                'type' => $license->type,
                'is_active' => $license->is_active,
                'activated_at' => $license->activated_at?->format('Y-m-d H:i:s'),
                'expires_at' => $license->expires_at?->format('Y-m-d H:i:s'),
                'days_remaining' => $license->days_remaining,
                'last_verified_at' => $license->last_verified_at?->format('Y-m-d H:i:s'),
                'max_installations' => $license->max_installations,
                'created_at' => $license->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $license->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * تحديث ترخيص
     */
    public function update(Request $request, $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return Response::json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $license = License::findOrFail($id);

        $request->validate([
            'type' => 'sometimes|in:trial,standard,premium',
            'expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($request->has('type')) {
            $license->type = $request->type;
        }

        if ($request->has('expires_at')) {
            $license->expires_at = $request->expires_at ? Carbon::parse($request->expires_at) : null;
        }

        if ($request->has('is_active')) {
            $license->is_active = $request->is_active;
            if ($license->is_active && !$license->activated_at) {
                $license->activated_at = now();
            }
        }

        if ($request->has('notes')) {
            $license->notes = $request->notes;
        }

        $license->save();

        return Response::json([
            'success' => true,
            'message' => 'تم تحديث الترخيص بنجاح',
            'license' => $license
        ], 200);
    }

    /**
     * الحصول على إحصائيات الترخيصات
     */
    public function statistics(): JsonResponse
    {
        if (!$this->isAdmin()) {
            return Response::json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $stats = [
            'total' => License::count(),
            'active' => License::where('is_active', true)->count(),
            'inactive' => License::where('is_active', false)->count(),
            'expired' => License::where('expires_at', '<', now())->count(),
            'trial' => License::where('type', 'trial')->count(),
            'standard' => License::where('type', 'standard')->count(),
            'premium' => License::where('type', 'premium')->count(),
        ];

        return Response::json([
            'success' => true,
            'statistics' => $stats
        ], 200);
    }
}

