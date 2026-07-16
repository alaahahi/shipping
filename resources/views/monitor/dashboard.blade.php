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
        .filter { margin-bottom: 16px; }
        select, button { background: #334155; color: #fff; border: 0; padding: 8px 12px; border-radius: 8px; }
        a { color: #38bdf8; }
        @media (max-width: 900px) { .charts { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="container">
    <h1>مراقبة النظام — {{ $project }}</h1>
    <p class="muted">قراءة فقط من ملفات JSONL — بدون قاعدة بيانات</p>

    <form class="filter" method="get">
        <label for="date">التاريخ:</label>
        <select name="date" id="date" onchange="this.form.submit()">
            @foreach($availableDates as $file)
                @php $d = str_replace('.log', '', $file); @endphp
                <option value="{{ $d }}" @selected($date === $d)>{{ $d }}</option>
            @endforeach
            @if(empty($availableDates))
                <option value="{{ $date }}" selected>{{ $date }}</option>
            @endif
        </select>
        <a href="{{ route('monitor.status') }}" style="margin-right:12px;">JSON Status</a>
    </form>

    <div class="grid">
        <div class="card"><h3>اتصالات MySQL الحالية</h3><div class="value">{{ $metrics['summary']['threads_connected'] ?? 0 }}</div></div>
        <div class="card"><h3>أقصى اتصالات اليوم</h3><div class="value">{{ $metrics['summary']['max_connections_today'] ?? 0 }}</div></div>
        <div class="card"><h3>عدد الطلبات</h3><div class="value">{{ $metrics['summary']['total_requests'] ?? 0 }}</div></div>
        <div class="card"><h3>متوسط الاستجابة (ms)</h3><div class="value">{{ $metrics['summary']['avg_response_ms'] ?? 0 }}</div></div>
        <div class="card"><h3>طلبات بطيئة</h3><div class="value">{{ $metrics['summary']['slow_requests_count'] ?? 0 }}</div></div>
        <div class="card"><h3>طلبات فاشلة</h3><div class="value">{{ $metrics['summary']['failed_requests_count'] ?? 0 }}</div></div>
        <div class="card"><h3>استثناءات SQL</h3><div class="value">{{ $metrics['summary']['exceptions_count'] ?? 0 }}</div></div>
        <div class="card"><h3>Queue Jobs</h3><div class="value">{{ $metrics['summary']['queue_jobs_count'] ?? 0 }}</div></div>
    </div>

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
        <table>
            <thead><tr><th>الوقت</th><th>المقياس</th><th>القيمة</th><th>الحد</th><th>المصدر</th></tr></thead>
            <tbody>
            @forelse($alerts as $alert)
                <tr>
                    <td>{{ $alert['timestamp'] ?? '-' }}</td>
                    <td>{{ $alert['metric'] ?? '-' }}</td>
                    <td>{{ $alert['value'] ?? '-' }}</td>
                    <td>{{ $alert['threshold'] ?? '-' }}</td>
                    <td>{{ $alert['url'] ?? $alert['route'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">لا توجد تنبيهات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>طلبات بطيئة</h2>
        <table>
            <thead><tr><th>الوقت</th><th>المسار</th><th>المدة ms</th><th>الحالة</th><th>Threads</th></tr></thead>
            <tbody>
            @forelse($metrics['slow_requests'] as $row)
                <tr>
                    <td>{{ $row['timestamp'] ?? '-' }}</td>
                    <td>{{ $row['route'] ?? $row['url'] ?? '-' }}</td>
                    <td>{{ $row['execution_time_ms'] ?? '-' }}</td>
                    <td>{{ $row['status'] ?? '-' }}</td>
                    <td>{{ $row['database']['threads_connected'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">لا يوجد</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>استعلامات بطيئة</h2>
        <table>
            <thead><tr><th>المسار</th><th>المدة ms</th><th>SQL</th></tr></thead>
            <tbody>
            @forelse($metrics['slow_queries'] as $row)
                <tr>
                    <td>{{ $row['route'] ?? $row['url'] ?? '-' }}</td>
                    <td>{{ $row['time_ms'] ?? '-' }}</td>
                    <td><code>{{ \Illuminate\Support\Str::limit($row['sql'] ?? '', 180) }}</code></td>
                </tr>
            @empty
                <tr><td colspan="3">لا يوجد</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>استثناءات قاعدة البيانات</h2>
        <table>
            <thead><tr><th>الوقت</th><th>النوع</th><th>الرسالة</th></tr></thead>
            <tbody>
            @forelse($metrics['exceptions'] as $row)
                <tr>
                    <td>{{ $row['timestamp'] ?? '-' }}</td>
                    <td>{{ $row['exception_class'] ?? '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($row['message'] ?? '', 200) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">لا يوجد</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
const rpm = @json($metrics['requests_per_minute'] ?? ['labels'=>[], 'values'=>[]]);
const mem = @json($metrics['memory_trend'] ?? ['labels'=>[], 'values'=>[]]);

new Chart(document.getElementById('rpmChart'), {
    type: 'line',
    data: { labels: rpm.labels, datasets: [{ label: 'Requests/min', data: rpm.values, borderColor: '#38bdf8', tension: 0.2 }] },
    options: { plugins: { legend: { labels: { color: '#e2e8f0' } } }, scales: { x: { ticks: { color: '#94a3b8' } }, y: { ticks: { color: '#94a3b8' } } } }
});

new Chart(document.getElementById('memoryChart'), {
    type: 'line',
    data: { labels: mem.labels, datasets: [{ label: 'Peak MB', data: mem.values, borderColor: '#34d399', tension: 0.2 }] },
    options: { plugins: { legend: { labels: { color: '#e2e8f0' } } }, scales: { x: { ticks: { color: '#94a3b8' } }, y: { ticks: { color: '#94a3b8' } } } }
});
</script>
</body>
</html>
