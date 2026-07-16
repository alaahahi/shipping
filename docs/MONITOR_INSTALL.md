# Laravel Monitor Module — Installation & Reuse

This monitoring module lives under `app/Monitor` and writes telemetry to JSONL files in `storage/logs/monitor/`. It does **not** use database tables.

Compatible with **Laravel 9+** and **PHP 8.0+**.

Designed for **central monitoring**: each Laravel app exposes a public JSON API. A central dashboard can poll all systems and aggregate results.

## What It Provides

- Per-request logging (duration, memory, queries, optional DB snapshot)
- Exception logging for SQL/connection failures
- Queue and console/scheduler job logging
- Threshold-based alerts (`alerts.log`)
- **Public JSON API** (no auth by default) for central hub integration
- `GET /monitor/dashboard` — optional HTML UI that loads data **only from API**
- `php artisan monitor:clean` — retention cleanup

## API Endpoints (no authentication)

All responses include: `project`, `hostname`, `environment`, `server_time`.

| Endpoint | Description |
|----------|-------------|
| `GET /monitor/api/overview?date=YYYY-MM-DD` | **Main endpoint** — status + metrics + alerts + dates |
| `GET /monitor/api/status` | Live DB/memory snapshot |
| `GET /monitor/api/metrics?date=YYYY-MM-DD` | Aggregated metrics from log files |
| `GET /monitor/api/alerts?limit=100` | Alert records |
| `GET /monitor/api/logs?date=&type=&limit=500` | Raw JSONL records (filter by type) |
| `GET /monitor/api/dates` | Available log dates |
| `GET /monitor/status` | Alias of API status (backward compatible) |

### Central hub polling example

```javascript
const systems = [
  'https://shipping.example.com/monitor/api/overview',
  'https://erp2.example.com/monitor/api/overview',
];

const results = await Promise.all(systems.map(url => fetch(url).then(r => r.json())));
```

Each system should set a unique `MONITOR_PROJECT_NAME` in `.env`.

## Environment Variables

```env
MONITOR_ENABLED=true
MONITOR_PROJECT_NAME="Shipping ERP"
MONITOR_SLOW_QUERY_MS=500
MONITOR_SLOW_REQUEST_MS=2000
MONITOR_RETENTION_DAYS=30
MONITOR_CORS_ORIGIN=*
```

Optional middleware (comma-separated), default empty = fully public:

```env
MONITOR_API_MIDDLEWARE=
MONITOR_DASHBOARD_MIDDLEWARE=
MONITOR_STATUS_MIDDLEWARE=
```

## Install In Another Laravel Project

### 1. Copy module files

```
app/Monitor/
config/monitor.php
resources/views/monitor/dashboard.blade.php
docs/MONITOR_INSTALL.md
```

### 2. Register the service provider

In `config/app.php`:

```php
App\Monitor\Providers\MonitorServiceProvider::class,
```

### 3. Wire exception reporting

In `app/Exceptions/Handler.php` inside `register()`:

```php
$this->reportable(function (Throwable $e) {
    if (app()->bound(\App\Monitor\Services\ExceptionMonitor::class)) {
        app(\App\Monitor\Services\ExceptionMonitor::class)->log($e);
    }
});
```

### 4. Ensure log directory is writable

```bash
mkdir -p storage/logs/monitor
chmod -R 775 storage/logs/monitor
```

### 5. Run tests

```bash
php artisan test tests/Unit/Monitor tests/Feature/Monitor
```

## Security Notes

- API is **public by default** for multi-system central monitoring
- Restrict access at network level (VPN, firewall, internal IPs) in production
- Set `MONITOR_CORS_ORIGIN` to your central dashboard domain if needed
- Logs stay under `storage/` (never in public web root)
- Monitoring fails silently — never breaks requests

## Log Format

Daily file: `storage/logs/monitor/YYYY-MM-DD.log`  
Alerts: `storage/logs/monitor/alerts.log`

Record types: `request`, `exception`, `queue`, `queue_failed`, `schedule`, `alert`

## Maintenance

```bash
php artisan monitor:clean
php artisan monitor:clean --days=14
```
