<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>حالة الترخيص</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [dir="rtl"] {
            direction: rtl;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                📊 حالة الترخيص
            </h2>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-8">
            @if(isset($license['activated']) && $license['activated'])
                <!-- ترخيص مفعل -->
                @php
                    $isValid = $license['valid'] ?? false;
                    $isExpired = isset($license['expires_at']) && strtotime($license['expires_at']) < time();
                    $daysRemaining = $license['days_remaining'] ?? null;
                    $statusColor = $isExpired ? 'red' : ($daysRemaining !== null && $daysRemaining < 30 ? 'yellow' : 'green');
                    $statusText = $isExpired ? 'منتهي الصلاحية' : ($daysRemaining === null ? 'دائم' : ($daysRemaining < 30 ? 'ينتهي قريباً' : 'مفعل وصالح'));
                    $typeLabels = ['trial' => 'تجريبي', 'standard' => 'قياسي', 'premium' => 'مميز'];
                @endphp

                <div class="mb-6 p-4 bg-{{ $statusColor }}-50 dark:bg-{{ $statusColor }}-900/20 border border-{{ $statusColor }}-200 dark:border-{{ $statusColor }}-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-{{ $statusColor }}-800 dark:text-{{ $statusColor }}-200 font-semibold text-lg">
                                @if($statusColor === 'green') ✅ @elseif($statusColor === 'yellow') ⚠️ @else ❌ @endif
                                {{ $statusText }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">النوع:</span>
                            <p class="text-gray-900 dark:text-white font-semibold">
                                {{ $typeLabels[$license['type'] ?? 'standard'] ?? 'غير محدد' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Domain:</span>
                            <p class="text-gray-900 dark:text-white font-semibold">
                                {{ $license['domain'] ?? 'غير محدد' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">ينتهي في:</span>
                            <p class="text-gray-900 dark:text-white font-semibold">
                                {{ $license['expires_at'] ?? 'دائم' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">الأيام المتبقية:</span>
                            <p class="text-gray-900 dark:text-white font-semibold">
                                {{ $daysRemaining !== null ? $daysRemaining . ' يوم' : '∞' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">مفعل منذ:</span>
                            <p class="text-gray-900 dark:text-white font-semibold">
                                {{ $license['activated_at'] ?? 'غير محدد' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">آخر تحقق:</span>
                            <p class="text-gray-900 dark:text-white font-semibold">
                                {{ $license['last_verified_at'] ?? 'لم يتم التحقق' }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <!-- ترخيص غير مفعل -->
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-red-800 dark:text-red-200 font-semibold text-lg">
                        ❌ الترخيص غير مفعل
                    </p>
                    <p class="text-red-600 dark:text-red-400 mt-2">
                        {{ $license['message'] ?? 'يجب تفعيل الترخيص لاستخدام المنتج' }}
                    </p>
                </div>
            @endif

            <!-- معلومات السيرفر -->
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">
                    معلومات السيرفر
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Domain:</span>
                        <code class="text-gray-900 dark:text-white font-mono">{{ $server['domain'] ?? 'غير محدد' }}</code>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Fingerprint:</span>
                        <code class="text-gray-900 dark:text-white font-mono text-xs">{{ substr($server['fingerprint'] ?? '', 0, 30) }}...</code>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <a href="{{ route('license.activate') }}" class="flex-1 text-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700">
                    تفعيل الترخيص
                </a>
                <button onclick="location.reload()" class="flex-1 py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    تحديث
                </button>
            </div>
        </div>
    </div>
</body>
</html>

