-- إنشاء جدول sync_jobs في قاعدة بيانات SQLite المحلية
CREATE TABLE IF NOT EXISTS sync_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    model TEXT NOT NULL,
    uuid TEXT,
    operation TEXT NOT NULL CHECK (operation IN ('create','update','delete')),
    payload TEXT NOT NULL,
    attempts INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now')),
    synced_at TEXT
);

CREATE INDEX IF NOT EXISTS sync_jobs_synced_at_index ON sync_jobs (synced_at);

