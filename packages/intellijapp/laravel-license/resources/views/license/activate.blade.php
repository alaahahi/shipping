<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تفعيل الترخيص</title>
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
                🔑 تفعيل المنتج
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                أدخل مفتاح الترخيص لتفعيل المنتج
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-8">
            <!-- معلومات السيرفر -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
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
                        <code class="text-gray-900 dark:text-white font-mono text-xs">{{ substr($server['fingerprint'] ?? '', 0, 20) }}...</code>
                    </div>
                </div>
            </div>

            <!-- حالة الترخيص الحالي -->
            @if(isset($license['activated']) && $license['activated'])
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-green-800 dark:text-green-200">
                        ✅ الترخيص مفعل حالياً
                    </p>
                </div>
            @endif

            <!-- رسالة الخطأ -->
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-red-800 dark:text-red-200">
                        ❌ {{ session('error') }}
                    </p>
                </div>
            @endif

            <!-- نموذج التفعيل -->
            <form id="activateForm" method="POST" action="{{ route('license.activate.post') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="license_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        مفتاح الترخيص
                    </label>
                    <textarea
                        id="license_key"
                        name="license_key"
                        rows="4"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-rose-500 focus:border-rose-500 dark:bg-gray-700 dark:text-white font-mono text-sm"
                        placeholder="الصق مفتاح الترخيص هنا..."
                    ></textarea>
                    @error('license_key')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span id="btnText">تفعيل الآن</span>
                        <span id="btnLoading" class="hidden">جاري التفعيل...</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center">
            <a href="{{ route('license.status') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                عرض حالة الترخيص
            </a>
        </div>
    </div>

    <script>
        document.getElementById('activateForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            btn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
        });

        // نسخ Fingerprint
        function copyFingerprint() {
            const fingerprint = '{{ $server['fingerprint'] ?? '' }}';
            navigator.clipboard.writeText(fingerprint).then(() => {
                alert('تم النسخ!');
            });
        }
    </script>
</body>
</html>

