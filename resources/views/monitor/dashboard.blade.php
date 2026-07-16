<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitor Dashboard — {{ $project }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { margin: 0 0 8px; }
        .muted { color: #94a3b8; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 20px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 16px; }
        .card h3 { margin: 0 0 8px; font-size: 14px; color: #94a3b8; }
        .card .value { font-size: 28px; font-weight: 700; }
        .charts { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .panel { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border-bottom: 1px solid #334155; padding: 8px; text-align: right; vertical-align: top; }
        th { color: #94a3b8; }
        .filter { margin-bottom: 16px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        select, button { background: #334155; color: #fff; border: 0; padding: 8px 12px; border-radius: 8px; cursor: pointer; }
        a { color: #38bdf8; }
        .error { color: #f87171; }
        .loading { color: #94a3b8; }
        @media (max-width: 900px) { .charts { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="container">
    <h1>مراقبة النظام — <span id="project-name">{{ $project }}</span></h1>
    <p class="muted">كل البيانات تُقرأ من API — جاهز للربط مع نظام مركزي</p>

    <div class="filter">
        <label for="date">التاريخ:</label>
        <select id="date"></select>
        <button type="button" id="refresh">تحديث</button>
        <a href="{{ $apiBase }}/overview" target="_blank">API Overview</a>
        <a href="{{ $apiBase }}/status" target="_blank">API Status</a>
        <span id="meta" class="muted"></span>
    </div>
    <p id="status-msg" class="loading">جاري التحميل...</p>

    <div class="grid" id="summary-cards"></div>

    <div class="charts">
        <div class="panel">
            <h2>الطلبات في الدقيقة</h2>
            <canvas id="rpmChart" height="120"></canvas>
        </div>
        <div class="panel">
            <h2>استهلاك الذاكرة (MB)</h2>
            <canvas id="memoryChart" height="120"></canvas>
        </div>
    </div>

    <div class="panel">
        <h2>تنبيهات</h2>
        <div id="alerts-table"></div>
    </div>

    <div class="panel">
        <h2>طلبات بطيئة</h2>
        <div id="slow-requests-table"></div>
    </div>

    <div class="panel">
        <h2>استعلامات بطيئة</h2>
        <div id="slow-queries-table"></div>
    </div>

    <div class="panel">
        <h2>استثناءات قاعدة البيانات</h2>
        <div id="exceptions-table"></div>
    </div>
</div>

<script>
const API_BASE = @json($apiBase);
let rpmChart = null;
let memoryChart = null;

function esc(value) {
    return String(value ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function tableHtml(columns, rows, emptyText) {
    if (!rows || !rows.length) {
        return `<table><tbody><tr><td>${esc(emptyText)}</td></tr></tbody></table>`;
    }
    const head = columns.map(c => `<th>${esc(c.label)}</th>`).join('');
    const body = rows.map(row => {
        return '<tr>' + columns.map(c => `<td>${esc(typeof c.value === 'function' ? c.value(row) : row[c.key])}</td>`).join('') + '</tr>';
    }).join('');
    return `<table><thead><tr>${head}</tr></thead><tbody>${body}</tbody></table>`;
}

function renderSummary(summary, status) {
    const cards = [
        ['اتصالات MySQL الحالية', status?.threads_connected ?? summary?.threads_connected ?? 0],
        ['أقصى اتصالات اليوم', summary?.max_connections_today ?? 0],
        ['عدد الطلبات', summary?.total_requests ?? 0],
        ['متوسط الاستجابة (ms)', summary?.avg_response_ms ?? 0],
        ['طلبات بطيئة', summary?.slow_requests_count ?? 0],
        ['طلبات فاشلة', summary?.failed_requests_count ?? 0],
        ['استثناءات SQL', summary?.exceptions_count ?? 0],
        ['Queue Jobs', summary?.queue_jobs_count ?? 0],
    ];
    document.getElementById('summary-cards').innerHTML = cards.map(([title, value]) =>
        `<div class="card"><h3>${esc(title)}</h3><div class="value">${esc(value)}</div></div>`
    ).join('');
}

function renderCharts(metrics) {
    const rpm = metrics?.requests_per_minute ?? { labels: [], values: [] };
    const mem = metrics?.memory_trend ?? { labels: [], values: [] };
    const chartOpts = {
        plugins: { legend: { labels: { color: '#e2e8f0' } } },
        scales: { x: { ticks: { color: '#94a3b8' } }, y: { ticks: { color: '#94a3b8' } } }
    };

    if (rpmChart) rpmChart.destroy();
    if (memoryChart) memoryChart.destroy();

    rpmChart = new Chart(document.getElementById('rpmChart'), {
        type: 'line',
        data: { labels: rpm.labels, datasets: [{ label: 'Requests/min', data: rpm.values, borderColor: '#38bdf8', tension: 0.2 }] },
        options: chartOpts
    });

    memoryChart = new Chart(document.getElementById('memoryChart'), {
        type: 'line',
        data: { labels: mem.labels, datasets: [{ label: 'Peak MB', data: mem.values, borderColor: '#34d399', tension: 0.2 }] },
        options: chartOpts
    });
}

function renderTables(metrics, alerts) {
    document.getElementById('alerts-table').innerHTML = tableHtml([
        { label: 'الوقت', key: 'timestamp' },
        { label: 'المقياس', key: 'metric' },
        { label: 'القيمة', key: 'value' },
        { label: 'الحد', key: 'threshold' },
        { label: 'المصدر', value: r => r.url || r.route || '-' },
    ], alerts, 'لا توجد تنبيهات');

    document.getElementById('slow-requests-table').innerHTML = tableHtml([
        { label: 'الوقت', key: 'timestamp' },
        { label: 'المسار', value: r => r.route || r.url || '-' },
        { label: 'المدة ms', key: 'execution_time_ms' },
        { label: 'الحالة', key: 'status' },
        { label: 'Threads', value: r => r.database?.threads_connected ?? '-' },
    ], metrics?.slow_requests, 'لا يوجد');

    document.getElementById('slow-queries-table').innerHTML = tableHtml([
        { label: 'المسار', value: r => r.route || r.url || '-' },
        { label: 'المدة ms', key: 'time_ms' },
        { label: 'SQL', value: r => (r.sql || '').slice(0, 180) },
    ], metrics?.slow_queries, 'لا يوجد');

    document.getElementById('exceptions-table').innerHTML = tableHtml([
        { label: 'الوقت', key: 'timestamp' },
        { label: 'النوع', key: 'exception_class' },
        { label: 'الرسالة', value: r => (r.message || '').slice(0, 200) },
    ], metrics?.exceptions, 'لا يوجد');
}

function fillDateSelect(dates, selected) {
    const select = document.getElementById('date');
    const list = dates?.length ? dates : [selected];
    select.innerHTML = list.map(d => `<option value="${esc(d)}" ${d === selected ? 'selected' : ''}>${esc(d)}</option>`).join('');
}

async function loadOverview(date) {
    const statusEl = document.getElementById('status-msg');
    statusEl.textContent = 'جاري التحميل...';
    statusEl.className = 'loading';

    try {
        const url = `${API_BASE}/overview?date=${encodeURIComponent(date || '')}`;
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();

        document.getElementById('project-name').textContent = data.project || @json($project);
        document.getElementById('meta').textContent = `${data.hostname || ''} | ${data.environment || ''} | ${data.server_time || ''}`;

        fillDateSelect(data.available_dates, data.date);
        renderSummary(data.metrics?.summary, data.status);
        renderCharts(data.metrics);
        renderTables(data.metrics, data.alerts);

        statusEl.textContent = '';
    } catch (error) {
        statusEl.textContent = 'فشل تحميل البيانات من API: ' + error.message;
        statusEl.className = 'error';
    }
}

document.getElementById('date').addEventListener('change', (e) => loadOverview(e.target.value));
document.getElementById('refresh').addEventListener('click', () => loadOverview(document.getElementById('date').value));

loadOverview(new Date().toISOString().slice(0, 10));
</script>
</body>
</html>
