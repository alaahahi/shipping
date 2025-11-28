# إعداد API للمشروع الثاني

## المتطلبات

يجب إضافة المتغيرات التالية في ملف `.env` في **كلا المشروعين**:

```env
# رابط المشروع الثاني (يضاف في المشروع الأول فقط)
SECOND_PROJECT_URL=https://project2.example.com

# API Key (يجب أن يكون نفس القيمة في المشروعين)
API_KEY=your-secret-api-key-here-change-this
```

## كيفية العمل

### 1. التحقق من السيارات

عند البحث عن سيارة برقم الشانصي:
- يتم البحث أولاً في المشروع الحالي
- إذا لم يتم العثور على السيارة، يتم البحث تلقائياً في المشروع الثاني
- إذا وُجدت في المشروع الثاني، تظهر رسالة تحذيرية

### 2. APIs المتاحة

#### API للتحقق من السيارات (للمشروع الثاني)
```
GET /api/external/checkCar?vin=123456789
Headers:
  X-API-Key: your-secret-api-key-here
```

**الاستجابة:**
```json
{
  "exists": true,
  "car": {
    "id": 1,
    "vin": "123456789",
    "car_type": "تويوتا",
    "year": 2024,
    "car_color": "أبيض",
    "purchase_expenses": {...},
    "sales_expenses": {...},
    "profit": 1000
  }
}
```

#### API للمبيعات (للتاجر)
```
GET /api/external/getSales?from=2024-01-01&to=2024-12-31&vin=123456789
Headers:
  X-API-Key: your-secret-api-key-here
```

**المعاملات:**
- `from` (اختياري): تاريخ البداية
- `to` (اختياري): تاريخ النهاية
- `vin` (اختياري): رقم الشانصي للبحث

**الاستجابة:**
```json
{
  "success": true,
  "count": 10,
  "sales": [
    {
      "id": 1,
      "vin": "123456789",
      "car_type": "تويوتا",
      "total_s": 5000,
      "paid": 3000,
      "profit": 1000,
      "client": {
        "id": 1,
        "name": "اسم العميل"
      }
    }
  ]
}
```

## الأمان

- جميع الـ APIs الخارجية محمية بـ API Key
- يجب إرسال `X-API-Key` في الـ Headers أو `api_key` في الـ Query Parameters
- الـ API Key يجب أن يكون نفس القيمة في المشروعين
- الـ APIs للعرض فقط، لا تسمح بالتعديل

## ملاحظات مهمة

1. **API Key**: يجب تغيير `API_KEY` إلى قيمة آمنة في الإنتاج
2. **URL**: تأكد من أن `SECOND_PROJECT_URL` هو الرابط الكامل (مع https://)
3. **Timeout**: يتم تعيين timeout 5 ثوانٍ للاتصال بالمشروع الثاني
4. **الأخطاء**: إذا فشل الاتصال بالمشروع الثاني، سيتم تجاهل الخطأ ولن يؤثر على البحث في المشروع الحالي

## الاختبار

للاختبار، يمكنك استخدام curl:

```bash
# اختبار API للتحقق من السيارات
curl -H "X-API-Key: your-secret-api-key-here" \
  "https://project2.example.com/api/external/checkCar?vin=123456789"

# اختبار API للمبيعات
curl -H "X-API-Key: your-secret-api-key-here" \
  "https://project2.example.com/api/external/getSales?from=2024-01-01&to=2024-12-31"
```

