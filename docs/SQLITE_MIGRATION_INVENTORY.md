# SQLite Migration Inventory — Shipping ERP

Generated as part of the safe MySQL → SQLite migration plan.

## Source of truth for production schema

- Structure dump: `salamjalalco_main (6).sql` (2026-07-16, structure-only, 54 tables)
- Critical columns match recent migrations (`car`, `car_contract`, `users`, `system_config`, …)

## Removed before migration (trips module)

Dropped from codebase + migration `2026_07_16_163000_drop_trips_module_tables`:

- Tables: `trips`, `trip_companies`, `trip_cars`, `trip_expenses`, `consignee_payments`, `internal_transport_payments`
- Related UI/API and `shipping_trips_admin` user type / app pages

## MySQL-only SQL blockers (priority)

| Priority | Location | Issue | Fix |
|----------|----------|-------|-----|
| P0 | `DashboardController::DelCar` | `@row_number` user variables | Portable PHP renumber |
| P0 | `AccountingController` JSON filters | Already branched sqlite/mysql | Keep via `DatabaseDriver` |
| P1 | `Monitor/DbStatusService` | `SHOW STATUS` / `CONNECTION_ID` | Return null on non-MySQL |
| P2 | ~50 `DB::raw` / aggregates | Mostly portable (`SUM`, `COALESCE`) | Review during UAT |

## ENUM / JSON

- ENUM columns become TEXT/CHECK under SQLite via Laravel
- JSON columns stored as TEXT; use driver helpers for extract filters

## Tables in migrations but not in production dump

- `jobs`, `job_batches`, `sync_queue`, `car_sales`
- Export tool should copy living MySQL tables; optional empty migrate for queue tables

## Legacy tables (no Schema::create in repo)

Must be copied from MySQL as-is: `car`, `users`, `wallets`, `transactions`, `car_contract`, …

## Heavy tables (export verify targets)

- `car`, `transactions`, `car_contract`, `wallets`

## Backup before any cutover

```bash
mysqldump -u USER -p salamjalalco_main > backup_YYYYMMDD.sql
```

Keep MySQL server online for ≥2 weeks after cutover.

## Tools added

- `php artisan db:export-to-sqlite --source=mysql_live --verify`
- Connection `mysql_live` always targets real MySQL (ignores local sqlite remap)
- Docs: `SQLITE_STAGING_UAT.md`, `SQLITE_CUTOVER_ROLLBACK.md`
