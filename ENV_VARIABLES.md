# متغيرات البيئة المطلوبة

## إضافة هذه المتغيرات في ملف `.env`:

```env
# رابط المشروع الثاني
SECOND_PROJECT_URL=https://example.com

# API Key للتحقق من الطلبات (يجب أن يكون نفس القيمة في المشروعين)
API_KEY=your-secret-api-key-here
```

## كيفية الاستخدام:

### 1. API للتحقق من السيارات (من المشروع الثاني):
```
GET /api/external/checkCar?vin=123456789
Headers:
  X-API-Key: your-secret-api-key-here
```

### 2. API للمبيعات (للتاجر):
```
GET /api/external/getSales?from=2024-01-01&to=2024-12-31&vin=123456789
Headers:
  X-API-Key: your-secret-api-key-here
```

## ملاحظات:
- يجب إضافة نفس `API_KEY` في المشروعين
- `SECOND_PROJECT_URL` يجب أن يكون الرابط الكامل للمشروع الثاني (مثال: https://project2.example.com)
- الـ APIs الخارجية لا تحتاج تسجيل دخول، فقط API key
- عند عدم وجود السيارة في المشروع الأول، سيتم البحث تلقائياً في المشروع الثاني

