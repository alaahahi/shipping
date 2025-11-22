# كيفية مراقبة حالة المزامنة

## 📊 API Endpoint

### الحصول على حالة قاعدة البيانات والمزامنة

```bash
GET /api/database/status
```

**Response:**
```json
{
    "connection": {
        "current": "sync_sqlite",
        "is_sqlite": true,
        "is_mysql": false,
        "manual_mode": "local",
        "is_online": true,
        "auto_switch": false
    },
    "mysql": {
        "connection": "mysql",
        "is_reachable": true,
        "tables_count": 50,
        "error": null
    },
    "sqlite": {
        "connection": "sync_sqlite",
        "is_reachable": true,
        "tables_count": 45,
        "error": null
    },
    "sync": {
        "metadata_exists": true,
        "total_tables_synced": 10,
        "total_records_synced": 50000,
        "last_sync": "2025-11-21 10:30:00"
    },
    "tables": [
        {
            "table": "licenses",
            "mysql_count": 5,
            "sqlite_count": 5,
            "difference": 0,
            "is_synced": true
        },
        {
            "table": "users",
            "mysql_count": 100,
            "sqlite_count": 100,
            "difference": 0,
            "is_synced": true
        }
    ],
    "timestamp": "2025-11-21 10:35:00"
}
```

---

## 🔍 التحقق من حالة المزامنة

### 1. في المتصفح

افتح:
```
http://127.0.0.1:8000/api/database/status
```

### 2. في الكود

```php
use Illuminate\Support\Facades\Http;

$response = Http::get('/api/database/status');
$status = $response->json();

// Connection الحالي
$currentConnection = $status['connection']['current']; // sync_sqlite أو mysql

// حالة المزامنة
$isSynced = $status['tables'][0]['is_synced']; // true/false
```

---

## 📋 معلومات متاحة

### Connection Info
- `current`: Connection الحالي (sync_sqlite أو mysql)
- `is_sqlite`: هل يستخدم SQLite؟
- `is_mysql`: هل يستخدم MySQL؟
- `manual_mode`: الوضع اليدوي (local/online/null)
- `is_online`: هل هناك اتصال بالإنترنت؟
- `auto_switch`: هل التبديل تلقائي؟

### MySQL Info
- `is_reachable`: هل MySQL متاح؟
- `tables_count`: عدد الجداول
- `error`: أي أخطاء

### SQLite Info
- `is_reachable`: هل SQLite متاح؟
- `tables_count`: عدد الجداول
- `error`: أي أخطاء

### Sync Info
- `metadata_exists`: هل جدول sync_metadata موجود؟
- `total_tables_synced`: عدد الجداول المزامنة
- `total_records_synced`: إجمالي السجلات المزامنة
- `last_sync`: آخر مزامنة

### Tables Stats
- `mysql_count`: عدد السجلات في MySQL
- `sqlite_count`: عدد السجلات في SQLite
- `difference`: الفرق بينهما
- `is_synced`: هل متزامن؟

---

## ✅ التحقق من المزامنة

### جدول licenses
```json
{
    "table": "licenses",
    "mysql_count": 5,
    "sqlite_count": 5,
    "difference": 0,
    "is_synced": true
}
```

إذا كان `is_synced: true` و `difference: 0`، فالمزامنة صحيحة! ✅

---

## 🔧 استخدام في Frontend

```javascript
// Vue.js مثال
axios.get('/api/database/status')
  .then(response => {
    const status = response.data;
    
    console.log('Connection:', status.connection.current);
    console.log('Manual Mode:', status.connection.manual_mode);
    console.log('Is SQLite:', status.connection.is_sqlite);
    
    // التحقق من مزامنة licenses
    const licensesTable = status.tables.find(t => t.table === 'licenses');
    if (licensesTable && licensesTable.is_synced) {
      console.log('✅ Licenses table is synced!');
    }
  });
```

---

**استخدم `/api/database/status` لمراقبة حالة المزامنة! 📊**

