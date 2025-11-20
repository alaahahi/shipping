<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إدارة التراخيص</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [dir="rtl"] {
            direction: rtl;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                إدارة التراخيص
            </h2>
        </div>

        <!-- إحصائيات -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي الترخيصات</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white" id="stat-total">{{ count($licenses) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">مفعل</div>
                <div class="text-2xl font-bold text-green-600" id="stat-active">{{ collect($licenses)->where('is_active', true)->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">معطل</div>
                <div class="text-2xl font-bold text-gray-600" id="stat-inactive">{{ collect($licenses)->where('is_active', false)->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">منتهي</div>
                <div class="text-2xl font-bold text-red-600" id="stat-expired">0</div>
            </div>
        </div>

        <!-- الجدول -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Domain</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">النوع</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الحالة</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ينتهي في</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($licenses as $license)
                                @php
                                    $typeLabels = ['trial' => 'تجريبي', 'standard' => 'قياسي', 'premium' => 'مميز'];
                                    $statusColor = !$license['is_active'] ? 'bg-gray-500' : (isset($license['expires_at']) && strtotime($license['expires_at']) < time() ? 'bg-red-500' : 'bg-green-500');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        <code class="text-xs">{{ $license['domain'] }}</code>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $typeLabels[$license['type'] ?? 'standard'] ?? 'غير محدد' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded text-xs text-white {{ $statusColor }}">
                                            {{ $license['is_active'] ? 'مفعل' : 'معطل' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $license['expires_at'] ?? 'دائم' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-2">
                                            <button onclick="toggleLicense({{ $license['id'] }})" class="text-blue-600 hover:text-blue-800">
                                                {{ $license['is_active'] ? '⏸️' : '▶️' }}
                                            </button>
                                            <button onclick="deleteLicense({{ $license['id'] }})" class="text-red-600 hover:text-red-800">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        لا توجد تراخيص
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function toggleLicense(id) {
            if (!confirm('هل أنت متأكد من تغيير حالة الترخيص؟')) return;
            
            try {
                const response = await fetch(`/api/admin/licenses/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('فشل العملية: ' + data.message);
                }
            } catch (error) {
                alert('حدث خطأ: ' + error.message);
            }
        }

        async function deleteLicense(id) {
            if (!confirm('هل أنت متأكد من حذف هذا الترخيص؟')) return;
            
            try {
                const response = await fetch(`/api/admin/licenses/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('فشل العملية: ' + data.message);
                }
            } catch (error) {
                alert('حدث خطأ: ' + error.message);
            }
        }
    </script>
</body>
</html>

