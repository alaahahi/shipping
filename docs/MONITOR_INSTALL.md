# Laravel Monitor Module — Installation & Reuse

This monitoring module lives under `app/Monitor` and writes telemetry to JSONL files in `storage/logs/monitor/`. It does **not** use database tables.

Compatible with **Laravel 9+** and **PHP 8.0+**.

## What It Provides

- Per-request logging (duration, memory, queries, optional DB snapshot)
- Exception logging for SQL/connection failures
- Queue and console/scheduler job logging
- Threshold-based alerts (`alerts.log`)
- `GET /monitor/status` — JSON health snapshot
- `GET /monitor/dashboard` — Blade dashboard (Chart.js, log files only)
- `php artisan monitor:clean` — retention cleanup

## Files To Copy

```
app/Monitor/
config/monitor.php
resources/views/monitor/dashboard.blade.php
docs/MONITOR_INSTALL.md
```

Optional tests:

```
tests/Unit/Monitor/
tests/Feature/Monitor/
```

## Install In Another Laravel Project

### 1. Copy module files

Copy the directories/files listed above into the target project.

### 2. Register the service provider

In `config/app.php` providers array:

```php
App\Monitor\Providers\MonitorServiceProvider::class,
```

(Laravel 11+: register in `bootstrap/providers.php`.)

### 3. Publish config (optional)

```bash
php artisan vendor:publish --tag=monitor-config
```

Or keep `config/monitor.php` as committed project config.

### 4. Wire exception reporting

In `app/Exceptions/Handler.php` inside `register()`:

```php
$this->reportable(function (Throwable $e) {
    if (app()->bound(\App\Monitor\Services\ExceptionMonitor::class)) {
        app(\App\Monitor\Services\ExceptionMonitor::class)->log($e);
    }
});
```

### 5. Configure access control

Edit `config/monitor.php`:

- `admin_type_ids` — user `type_id` values allowed to open dashboard/status
- `dashboard_middleware` / `status_middleware` — default: `web`, `auth`, `monitor.admin`

Adjust `MonitorAdmin` middleware if your app uses roles/permissions instead of `type_id`.

### 6. Environment variables

```env
MONITOR_ENABLED=true
MONITOR_PROJECT_NAME="My App"
MONITOR_SLOW_QUERY_MS=500
MONITOR_SLOW_REQUEST_MS=2000
MONITOR_RETENTION_DAYS=30
MONITOR_ADMIN_TYPES=1
```

### 7. Ensure log directory is writable

```bash
mkdir -p storage/logs/monitor
chmod -R 775 storage/logs/monitor
```

### 8. Run tests (optional)

```bash
php artisan test --filter=Monitor
```

## Routes

| Route | Description |
|-------|-------------|
| `/monitor/status` | JSON DB/memory snapshot |
| `/monitor/dashboard` | HTML dashboard from log files |

Monitor routes are ignored from request logging via `ignore_routes` config.

## Log Format

Daily file: `storage/logs/monitor/YYYY-MM-DD.log`  
Alerts: `storage/logs/monitor/alerts.log`

Each line is JSON with fields such as:

- `type`: `request`, `exception`, `queue`, `queue_failed`, `schedule`, `alert`
- `timestamp`, `project`, `hostname`, `environment`
- Type-specific payload (URL, queries, DB stats, etc.)

## Maintenance

```bash
php artisan monitor:clean
php artisan monitor:clean --days=14
```

Retention also runs opportunistically once per day via cache key `monitor:last_retention_cleanup`.

## Extracting To A Composer Package Later

1. Move `app/Monitor` → `packages/monitoring/src`
2. Namespace stays `App\Monitor` or rename to `Vendor\Monitoring`
3. Add `composer.json` with PSR-4 autoload + `extra.laravel.providers`
4. Publish config/views via package service provider

The current layout is intentionally package-friendly.

## Security Notes

- Logs stay under `storage/` (never public)
- Protect dashboard/status with auth + admin middleware
- Monitoring fails silently — never breaks requests
