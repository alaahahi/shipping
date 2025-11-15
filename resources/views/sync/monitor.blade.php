<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة مراقبة المزامنة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: "Cairo", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .pulse-dot {
            position: relative;
        }
        .pulse-dot::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            background: currentColor;
            opacity: 0.2;
            border-radius: 9999px;
            transform: translate(-50%, -50%);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 0.3; }
            70% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
            100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
        }
        .grid-auto-fill {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 9999px;
            padding: 0.15rem 0.85rem;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-muted { background-color: #e5e7eb; color: #374151; }
        .model-card {
            border: 1px solid rgba(15,23,42,0.06);
            border-radius: 1rem;
            padding: 1rem;
            background: white;
            box-shadow: 0 5px 12px rgba(15,23,42,0.04);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .model-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15,23,42,0.08);
        }
        .model-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="bg-gradient-to-l from-indigo-600 to-blue-500 text-white shadow">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col gap-4">
            <div>
                <p class="text-sm text-white/80">التزامن المحلي &raquo; الخادم الرئيسي</p>
                <h1 class="text-3xl font-bold">لوحة مراقبة المزامنة</h1>
            </div>
            <div class="flex flex-col lg:flex-row gap-4 text-sm">
                <div class="flex-1 bg-white/15 rounded-lg px-4 py-3 border border-white/20">
                    <p class="text-white/80 text-xs">خادم المزامنة</p>
                    <p class="font-semibold">{{ $serverUrl ?: 'لم يتم ضبطه' }}</p>
                </div>
                <div class="flex-1 bg-white/15 rounded-lg px-4 py-3 border border-white/20">
                    <p class="text-white/80 text-xs">Endpoints</p>
                    <p class="font-semibold">Push: {{ $pushEndpoint }} &ndash; Pull: {{ $pullEndpoint }}</p>
                </div>
                <div class="flex-1 bg-white/15 rounded-lg px-4 py-3 border border-white/20">
                    <p class="text-white/80 text-xs">الموديلات المتزامنة</p>
                    <p class="font-semibold">{{ implode(', ', $models) ?: 'لم يتم تحديد موديلات' }}</p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        <section class="grid-auto-fill">
            <div class="bg-white rounded-2xl shadow p-5 border border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm text-slate-500">العقود المعلقة</h3>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 pulse-dot"></span>
                </div>
                <p id="metric-pending" class="text-3xl font-bold text-slate-800 mt-2">0</p>
                <p class="text-xs text-slate-400 mt-1">لم يتم إرسالهم بعد</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5 border border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm text-slate-500">المتزامنة اليوم</h3>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-dot"></span>
                </div>
                <p id="metric-synced" class="text-3xl font-bold text-slate-800 mt-2">0</p>
                <p class="text-xs text-slate-400 mt-1">عمليات ناجحة آخر 24 ساعة</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5 border border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm text-slate-500">محاولات فاشلة</h3>
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 pulse-dot"></span>
                </div>
                <p id="metric-failed" class="text-3xl font-bold text-slate-800 mt-2">0</p>
                <p class="text-xs text-slate-400 mt-1">حالات تحتاج مراجعة</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5 border border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm text-slate-500">حجم قائمة الانتظار</h3>
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 pulse-dot"></span>
                </div>
                <p id="metric-queue" class="text-3xl font-bold text-slate-800 mt-2">0</p>
                <p class="text-xs text-slate-400 mt-1">إجمالي السجلات (مزامَن + معلق)</p>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow border border-slate-100">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-xl font-semibold text-slate-800 mb-1">تشخيص الاتصال المحلي</h2>
                <p class="text-sm text-slate-500">تأكد من أن قاعدة بيانات المزامنة (SQLite) متاحة ويمكن الكتابة عليها وأن جدول <code>sync_jobs</code> موجود.</p>
            </div>
            <div class="p-6 grid gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500">اسم الاتصال</p>
                        <p id="diag-connection" class="text-lg font-semibold text-slate-800 mt-1">--</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">السائق (Driver)</p>
                        <p id="diag-driver" class="text-sm text-slate-700 mt-1">--</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">المسار / قاعدة البيانات</p>
                        <p id="diag-database" class="text-sm text-slate-600 break-all mt-1">--</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">حجم الملف</p>
                        <p id="diag-file-size" class="text-sm text-slate-700 mt-1">--</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">وجود ملف قاعدة البيانات</span>
                        <span id="diag-file-exists" class="badge badge-muted">--</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">صلاحية الكتابة</span>
                        <span id="diag-writable" class="badge badge-muted">--</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">جدول sync_jobs</span>
                        <span id="diag-table" class="badge badge-muted">--</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">نسخة SQLite</span>
                        <span id="diag-sqlite-version" class="text-sm font-semibold text-slate-700">--</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow border border-slate-100">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">الجداول المتزامنة</h2>
                    <p class="text-sm text-slate-500">نظرة سريعة على حالة كل جدول مشارك في المزامنة.</p>
                </div>
                <span class="text-xs text-slate-400">يتم التحديث مع كل استعلام للحالة</span>
            </div>
            <div class="p-6">
                <div id="models-grid" class="model-grid"></div>
                <p id="models-empty" class="text-sm text-slate-400 text-center py-6 hidden">لم يتم تعريف أي موديلات في ملف sync.php</p>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow border border-slate-100">
            <div class="p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center gap-4 justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">الحالة اللحظية</h2>
                    <p class="text-sm text-slate-500" id="last-sync">آخر مزامنة: لم تُسجَّل بعد</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button id="run-sync" class="px-5 py-2.5 rounded-full bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition">
                        🔄 مزامنة الآن
                    </button>
                    <button id="flush-pending" class="px-5 py-2.5 rounded-full border text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        🗑️ حذف العقود المعلقة
                    </button>
                    <button id="flush-all" class="px-5 py-2.5 rounded-full border text-sm font-semibold text-rose-600 border-rose-200 hover:bg-rose-50 transition">
                        ⚠️ مسح السجل بالكامل
                    </button>
                </div>
            </div>
            <div class="p-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="font-semibold text-slate-700 mb-3">مؤشر صحة المزامنة</h3>
                    <div class="bg-slate-100 rounded-full h-4">
                        <div id="health-bar" class="h-4 rounded-full bg-gradient-to-l from-emerald-400 to-emerald-600 transition-all" style="width: 0%"></div>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-slate-500">
                        <li>• كلما زادت نسبة الإخضرار كانت المزامنة في وضع جيد.</li>
                        <li>• تأكد من أن عدد المحاولات الفاشلة لا يتجاوز 5.</li>
                        <li>• قم بتشغيل مزامنة يدوية بعد العمل في وضع Offline طويل.</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-700 mb-3">سجل العمليات الأخيرة</h3>
                    <ul id="jobs-timeline" class="space-y-3 text-sm text-slate-600 max-h-72 overflow-y-auto"></ul>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-slate-700">تفاصيل العمليات</h3>
                    <span class="text-xs text-slate-400">يتم التحديث كل 5 ثوانٍ</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-right">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="py-2 px-3">النموذج</th>
                                <th class="py-2 px-3">العملية</th>
                                <th class="py-2 px-3">UUID</th>
                                <th class="py-2 px-3">المحاولات</th>
                                <th class="py-2 px-3">وقت الإنشاء</th>
                                <th class="py-2 px-3">وقت المزامنة</th>
                            </tr>
                        </thead>
                        <tbody id="jobs-table" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <template id="timeline-item">
        <li class="bg-slate-50 border border-slate-100 rounded-xl px-4 py-3">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                <span data-field="created_at"></span>
                <span data-field="model" class="font-semibold text-slate-500"></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span data-field="badge" class="text-xs font-semibold px-2 py-0.5 rounded-full"></span>
                    <span class="text-slate-700 text-sm" data-field="uuid"></span>
                </div>
                <span class="text-xs text-slate-400" data-field="synced_at"></span>
            </div>
        </li>
    </template>

    <script>
        const endpoints = {
            status: '{{ url('/api/sync/status') }}',
            run: '{{ url('/sync/run') }}',
            flush: '{{ url('/sync/jobs') }}',
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const metricEls = {
            pending: document.getElementById('metric-pending'),
            synced: document.getElementById('metric-synced'),
            failed: document.getElementById('metric-failed'),
            queue: document.getElementById('metric-queue'),
        };
        const lastSyncEl = document.getElementById('last-sync');
        const healthBarEl = document.getElementById('health-bar');
        const timelineEl = document.getElementById('jobs-timeline');
        const jobsTableEl = document.getElementById('jobs-table');
        const diagEls = {
            connection: document.getElementById('diag-connection'),
            driver: document.getElementById('diag-driver'),
            database: document.getElementById('diag-database'),
            fileSize: document.getElementById('diag-file-size'),
            fileExists: document.getElementById('diag-file-exists'),
            writable: document.getElementById('diag-writable'),
            table: document.getElementById('diag-table'),
            sqliteVersion: document.getElementById('diag-sqlite-version'),
        };
        const modelsGridEl = document.getElementById('models-grid');
        const modelsEmptyEl = document.getElementById('models-empty');

        async function fetchStatus() {
            try {
                const res = await fetch(endpoints.status);
                if (!res.ok) throw new Error('فشل قراءة الحالة');
                const data = await res.json();
                updateMetrics(data.metrics);
                updateTimeline(data.jobs);
                updateTable(data.jobs);
                updateDiagnostics(data.diagnostics);
                updateModels(data.models);
            } catch (error) {
                console.error(error);
            }
        }

        function updateMetrics(metrics) {
            metricEls.pending.textContent = metrics.pending ?? 0;
            metricEls.synced.textContent = metrics.synced_today ?? 0;
            metricEls.failed.textContent = metrics.failed ?? 0;
            metricEls.queue.textContent = metrics.queue_size ?? 0;

            const ratio = metrics.queue_size
                ? Math.max(0, Math.min(100, ((metrics.queue_size - metrics.pending) / metrics.queue_size) * 100))
                : 100;
            healthBarEl.style.width = `${ratio}%`;

            lastSyncEl.textContent = metrics.last_sync_time
                ? `آخر مزامنة: ${new Date(metrics.last_sync_time).toLocaleString('ar-IQ')}`
                : 'آخر مزامنة: لم تُسجَّل بعد';
        }

        function updateTimeline(jobs) {
            timelineEl.innerHTML = '';
            jobs.slice(0, 8).forEach(job => {
                const tpl = document.getElementById('timeline-item').content.cloneNode(true);
                tpl.querySelector('[data-field="created_at"]').textContent = job.created_at
                    ? new Date(job.created_at).toLocaleTimeString('ar-IQ', { hour: '2-digit', minute: '2-digit' })
                    : '--';
                tpl.querySelector('[data-field="model"]').textContent = job.model;
                tpl.querySelector('[data-field="uuid"]').textContent = job.uuid ?? '--';
                const badge = tpl.querySelector('[data-field="badge"]');
                badge.textContent = job.operation;
                badge.classList.add(jobBadgeClass(job.operation));
                tpl.querySelector('[data-field="synced_at"]').textContent = job.synced_at
                    ? new Date(job.synced_at).toLocaleTimeString('ar-IQ', { hour: '2-digit', minute: '2-digit' })
                    : 'معلّق';
                timelineEl.appendChild(tpl);
            });
        }

        function jobBadgeClass(op) {
            switch (op) {
                case 'create': return 'bg-emerald-100 text-emerald-700';
                case 'update': return 'bg-blue-100 text-blue-700';
                case 'delete': return 'bg-rose-100 text-rose-700';
                default: return 'bg-slate-100 text-slate-600';
            }
        }

        function updateTable(jobs) {
            jobsTableEl.innerHTML = '';
            jobs.forEach(job => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50';
                row.innerHTML = `
                    <td class="py-2.5 px-3">${job.model}</td>
                    <td class="py-2.5 px-3">
                        <span class="px-2 py-1 rounded-full text-xs ${jobBadgeClass(job.operation)}">
                            ${job.operation}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 text-slate-500 text-xs">${job.uuid ?? '--'}</td>
                    <td class="py-2.5 px-3">${job.attempts ?? 0}</td>
                    <td class="py-2.5 px-3 text-xs text-slate-500">${job.created_at ? new Date(job.created_at).toLocaleString('ar-IQ') : '--'}</td>
                    <td class="py-2.5 px-3 text-xs ${job.synced_at ? 'text-emerald-600' : 'text-amber-500'}">
                        ${job.synced_at ? new Date(job.synced_at).toLocaleString('ar-IQ') : 'معلّق'}
                    </td>
                `;
                jobsTableEl.appendChild(row);
            });
        }

        function updateDiagnostics(diag = {}) {
            if (!diagEls.connection) return;
            diagEls.connection.textContent = diag.connection ?? '--';
            diagEls.driver.textContent = diag.driver ?? '--';
            diagEls.database.textContent = diag.database ?? '--';
            diagEls.sqliteVersion.textContent = diag.sqlite_version ?? '--';
            diagEls.fileSize.textContent = diag.database_size ? formatBytes(diag.database_size) : '--';

            setBadge(diagEls.fileExists, diag.database_exists, 'موجود', 'مفقود');
            setBadge(diagEls.table, diag.sync_jobs_table, 'متوفر', 'غير موجود');
            setBadge(diagEls.writable, diag.writable, 'قابل للكتابة', 'غير قابل');
        }

        function updateModels(models = []) {
            if (!modelsGridEl || !modelsEmptyEl) {
                return;
            }

            modelsGridEl.innerHTML = '';
            if (!models.length) {
                modelsEmptyEl.classList.remove('hidden');
                return;
            }

            modelsEmptyEl.classList.add('hidden');

            models.forEach((model) => {
                const card = document.createElement('div');
                card.className = 'model-card';
                card.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="text-xs uppercase tracking-widest text-slate-400">${model.key}</p>
                            <h4 class="text-lg font-semibold text-slate-800">${model.name ?? '--'}</h4>
                        </div>
                        <span class="badge ${model.error ? 'badge-danger' : 'badge-success'}">
                            ${model.error ? 'خطأ' : 'جاهز'}
                        </span>
                    </div>
                    <div class="space-y-2 text-sm text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">الاتصال</span>
                            <span>${model.connection ?? '--'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">عدد السجلات</span>
                            <span>${formatNumber(model.count)}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">آخر تحديث</span>
                            <span>${model.latest ? formatDate(model.latest) : '--'}</span>
                        </div>
                        ${model.error ? `<p class="text-xs text-rose-500 mt-2">${model.error}</p>` : ''}
                    </div>
                `;
                modelsGridEl.appendChild(card);
            });
        }

        function setBadge(el, state, trueLabel, falseLabel) {
            if (!el) return;
            if (state === null || state === undefined) {
                el.textContent = '--';
                el.className = 'badge badge-muted';
                return;
            }

            if (state) {
                el.textContent = trueLabel;
                el.className = 'badge badge-success';
            } else {
                el.textContent = falseLabel;
                el.className = 'badge badge-danger';
            }
        }

        function formatBytes(bytes) {
            if (!bytes && bytes !== 0) return '--';
            const units = ['بايت', 'ك.ب', 'م.ب', 'ج.ب', 'ت.ب'];
            let value = bytes;
            let unitIndex = 0;
            while (value >= 1024 && unitIndex < units.length - 1) {
                value /= 1024;
                unitIndex++;
            }
            const formatted = value >= 10 || unitIndex === 0 ? value.toFixed(0) : value.toFixed(1);
            return `${formatted} ${units[unitIndex]}`;
        }

        function formatNumber(value) {
            if (value === null || value === undefined) return '--';
            return new Intl.NumberFormat('ar-IQ').format(value);
        }

        function formatDate(value) {
            try {
                return new Date(value).toLocaleString('ar-IQ');
            } catch (error) {
                return value ?? '--';
            }
        }

        async function postAction(url, method = 'POST', body = null) {
            const res = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: body ? JSON.stringify(body) : null,
            });
            if (!res.ok) throw new Error('فشلت العملية');
            return res.json();
        }

        document.getElementById('run-sync').addEventListener('click', async () => {
            const btn = event.currentTarget;
            btn.disabled = true;
            btn.textContent = '... جاري المزامنة';
            try {
                await postAction(endpoints.run);
                await fetchStatus();
            } catch (error) {
                alert(error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = '🔄 مزامنة الآن';
            }
        });

        document.getElementById('flush-pending').addEventListener('click', async () => {
            if (!confirm('سيتم حذف جميع العمليات المعلقة. متابعة؟')) return;
            await postAction(endpoints.flush, 'DELETE', { mode: 'pending' });
            fetchStatus();
        });

        document.getElementById('flush-all').addEventListener('click', async () => {
            if (!confirm('تحذير: سيتم حذف كل سجل المزامنة المحلي. هل أنت متأكد؟')) return;
            await postAction(endpoints.flush, 'DELETE', { mode: 'all' });
            fetchStatus();
        });

        fetchStatus();
        setInterval(fetchStatus, 5000);
    </script>
</body>
</html>

