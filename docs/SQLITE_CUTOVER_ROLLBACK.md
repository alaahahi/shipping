# SQLite Production Cutover & Rollback

## Pre-cutover

1. Staging UAT passed ([SQLITE_STAGING_UAT.md](SQLITE_STAGING_UAT.md))
2. Full MySQL backup:

```bash
mysqldump -u USER -p DBNAME > backup_pre_sqlite_YYYYMMDD.sql
```

3. Maintenance window announced
4. Keep MySQL server running (do not drop)

## Cutover steps

```bash
php artisan down

# Final export (read-only from MySQL)
php artisan db:export-to-sqlite --source=mysql_live --target=/absolute/path/database/shipping.sqlite --verify

# Point app to SQLite
# .env:
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/database/shipping.sqlite
# LOCAL_NO_REMOTE=false

php artisan config:clear
php artisan cache:clear
php artisan up
```

WAL pragmas are applied automatically in `AppServiceProvider` when driver is sqlite.

Schedule daily copy of the `.sqlite` file (+ `-wal` / `-shm` if present).

## Rollback (within first 2 weeks)

```bash
php artisan down
# Restore .env:
# DB_CONNECTION=mysql
# DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD
php artisan config:clear
php artisan up
```

If writes happened on SQLite after cutover and must return to MySQL, restore from `backup_pre_sqlite_*.sql` **or** plan a reverse sync (not automated in this release). Prefer short cutover windows to avoid dual-write.

## Do not

- Re-enable automatic MySQL→SQLite failover on connection errors
- Delete MySQL until decision is permanent and backups verified
