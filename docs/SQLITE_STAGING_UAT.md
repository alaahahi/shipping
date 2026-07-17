# SQLite Staging UAT Checklist

Run after `php artisan db:export-to-sqlite --source=mysql_live --verify` on a staging copy.

## Environment

```env
APP_ENV=production
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/database/shipping.sqlite
DB_FOREIGN_KEYS=true
LOCAL_NO_REMOTE=false
```

Confirm `php artisan tinker` → `DB::connection()->getDriverName()` === `sqlite`.

## Business scenarios

- [ ] Login as admin
- [ ] Dashboard clients list (`q=debit`) loads
- [ ] Add / edit car (purchases)
- [ ] Accounting in/out wallet entry; balances match MySQL export checksum
- [ ] Car contract create + payment
- [ ] Company treasury page loads
- [ ] External cars list
- [ ] No `database is locked` with 2–3 concurrent browsers for 10 minutes

## Technical checks

- [ ] `storage/logs/laravel.log` has no SQLSTATE driver errors
- [ ] Monitor `/monitor/api/status` returns JSON (`supported: false` for MySQL threads is OK)
- [ ] Delete car renumbers `car.no` without MySQL user-variable errors

## Gate

If any balance mismatch or repeated locks → **do not cut over**; stay on MySQL and file issues.
