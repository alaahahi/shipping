<?php

namespace IntellijApp\License\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use IntellijApp\License\Services\LicenseService;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;

class LicenseController extends Controller
{
    /**
     * عرض صفحة التفعيل
     */
    public function showActivate()
    {
        $licenseInfo = LicenseService::getLicenseInfo();
        $serverInfo = [
            'domain' => LicenseService::getCurrentDomain(),
            'fingerprint' => LicenseService::getServerFingerprint(),
        ];

        return view('license::activate', [
            'license' => $licenseInfo,
            'server' => $serverInfo,
        ]);
    }

    /**
     * تفعيل الترخيص
     */
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string',
            'domain' => 'nullable|string',
            'fingerprint' => 'nullable|string',
        ]);

        $result = LicenseService::activate(
            $request->license_key,
            $request->domain,
            $request->fingerprint
        );

        if ($result['success']) {
            return Response::json([
                'success' => true,
                'message' => $result['message'],
                'license' => $result['license'],
            ], 200);
        }

        return Response::json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * عرض حالة الترخيص
     */
    public function status(): JsonResponse
    {
        $licenseInfo = LicenseService::getLicenseInfo();
        $serverInfo = [
            'domain' => LicenseService::getCurrentDomain(),
            'fingerprint' => LicenseService::getServerFingerprint(),
        ];

        return Response::json([
            'success' => true,
            'license' => $licenseInfo,
            'server' => $serverInfo,
        ], 200);
    }

    /**
     * صفحة حالة الترخيص
     */
    public function showStatus()
    {
        $licenseInfo = LicenseService::getLicenseInfo();
        $serverInfo = [
            'domain' => LicenseService::getCurrentDomain(),
            'fingerprint' => LicenseService::getServerFingerprint(),
        ];

        return view('license::status', [
            'license' => $licenseInfo,
            'server' => $serverInfo,
        ]);
    }

    /**
     * إلغاء تفعيل الترخيص
     */
    public function deactivate(): JsonResponse
    {
        $result = LicenseService::deactivate();

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'تم إلغاء تفعيل الترخيص بنجاح',
            ], 200);
        }

        return Response::json([
            'success' => false,
            'message' => 'فشل إلغاء تفعيل الترخيص',
        ], 400);
    }

    /**
     * التحقق من الترخيص
     */
    public function verify(): JsonResponse
    {
        $isValid = LicenseService::verify();

        return Response::json([
            'success' => $isValid,
            'valid' => $isValid,
            'message' => $isValid ? 'الترخيص صالح' : 'الترخيص غير صالح',
        ], $isValid ? 200 : 403);
    }

    /**
     * الحصول على معلومات السيرفر
     */
    public function getServerInfo(): JsonResponse
    {
        return Response::json([
            'success' => true,
            'domain' => LicenseService::getCurrentDomain(),
            'fingerprint' => LicenseService::getServerFingerprint(),
        ], 200);
    }

    /**
     * مزامنة الترخيص مع السيرفر المركزي
     */
    public function sync(): JsonResponse
    {
        $result = LicenseService::syncWithCentralServer();
        
        return Response::json($result, $result['success'] ? 200 : 400);
    }

    /**
     * جلب الترخيص من السيرفر المركزي
     */
    public function pull(): JsonResponse
    {
        $result = LicenseService::pullFromCentralServer();
        
        return Response::json($result, $result['success'] ? 200 : 400);
    }

    /**
     * دفع الترخيص إلى السيرفر المركزي
     */
    public function push(): JsonResponse
    {
        $result = LicenseService::pushToCentralServer();
        
        return Response::json($result, $result['success'] ? 200 : 400);
    }
}

