# 📊 Dashboard Statistics - توثيق صفحة الإحصائيات

## 📋 نظرة عامة

صفحة Dashboard للإحصائيات في نظام إدارة السيارات. تعرض إحصائيات شاملة عن السيارات، الحولات، الدفعات، والتدفقات النقدية مع فلاتر حسب السنة والشهر.

---

## 🗂️ البنية والملفات

### 🔹 Backend (Laravel)

#### Controller
- **`app/Http/Controllers/StatisticsController.php`**
  - `index()` - عرض صفحة Vue
  - `getStatistics()` - API للحصول على جميع الإحصائيات
  - `carProfitStats()` - إحصائيات أرباح السيارات
  - `discountStats()` - إحصائيات الخصومات
  - `exportExcel()` - تصدير Excel (جاهز للتطوير)

#### Routes
- **Web Route:** `/dashboard/statistics`
  - ملف: `routes/web.php`
  - Middleware: `auth`, `verified`, `check.license`

- **API Routes:**
  - `/api/statistics` - GET - جميع الإحصائيات
  - `/api/statistics/car-profit-stats` - GET - إحصائيات الأرباح
  - `/api/statistics/discount-stats` - GET - إحصائيات الخصومات
  - `/api/statistics/export-excel` - GET - تصدير Excel
  - ملف: `routes/api.php`
  - Middleware: `auth:sanctum`

---

### 🔹 Frontend (Vue 3)

#### الصفحة الرئيسية
- **`resources/js/Pages/Dashboard/Statistics/Index.vue`**
  - الصفحة الرئيسية التي تجمع جميع Components
  - تستخدم Filters للفلترة
  - تستدعي API `/api/statistics`

#### Components
جميع Components موجودة في: `resources/js/Components/Dashboard/`

1. **`StatCards.vue`**
   - عرض بطاقات الإحصائيات الرئيسية
   - Props:
     - `carsCount: Number`
     - `totalCustoms: Number`
     - `exchangeProfit: Number`
     - `netProfit: Number`
     - `netTransfers: Number`
     - `cashBalance: Number`

2. **`ProfitChart.vue`**
   - رسم بياني خطي للأرباح الشهرية
   - Props:
     - `labels: Array`
     - `monthlyProfit: Array`
     - `yearlyProfit: Number`

3. **`CarProfitTable.vue`**
   - جدول أعلى السيارات ربحاً
   - Props:
     - `cars: Array`
     - `maxProfit: Number`
     - `minProfit: Number`
     - `avgProfit: Number`

4. **`DiscountTable.vue`**
   - جدول سجل الخصومات
   - Props:
     - `discounts: Array`
     - `totalDiscounts: Number`
     - `maxDiscount: Number`
     - `minDiscount: Number`

5. **`TransfersSummary.vue`**
   - ملخص الحولات
   - Props:
     - `grossTransfers: Number`
     - `transferFees: Number`
     - `netTransfers: Number`
     - `erbilTransfers: Number`

6. **`CashFlowCards.vue`**
   - بطاقات التدفقات النقدية
   - Props:
     - `cashIn: Number`
     - `cashOut: Number`
     - `netCash: Number`

7. **`CashFlowChart.vue`**
   - رسم بياني للتدفقات النقدية الشهرية
   - Props:
     - `labels: Array`
     - `cashInData: Array`
     - `cashOutData: Array`

8. **`YearClosingSummary.vue`**
   - خلاصة إغلاق السنة
   - Props:
     - `year: Number`
     - `totalIncome: Number`
     - `totalExpenses: Number`
     - `totalDiscounts: Number`
     - `netYearProfit: Number`
     - `carriedProfit: Number`
     - `isClosed: Boolean`

9. **`Filters.vue`**
   - فلاتر السنة والشهر
   - Props:
     - `selectedYear: Number`
     - `selectedMonth: Number`
     - `years: Array`

---

## 📊 الحسابات والإحصائيات

### 🔹 1. عدد السيارات
- **الحساب:** `COUNT(id)`
- **الفلاتر:**
  - `year_date` (السنة)
  - `created_at` (الشهر)

### 🔹 2. مجموع الجمرك
- **الجمرك شراء:** `SUM(dolar_custom)`
- **الجمرك بيع:** `SUM(dolar_custom_s)`
- **المجموع الكلي:** شراء + بيع

### 🔹 3. الفائدة من فرق سعر الصرف
- **الصيغة:** `SUM((dolar_price * dinar) - (dolar_price_s * dinar_s))`

### 🔹 4. مصاريف أربيل
- **من المشتريات:** `WHERE city = 'Erbil' OR note LIKE '%أربيل%'` → `SUM(expenses)`
- **من المبيعات:** `WHERE city = 'Erbil' OR note LIKE '%أربيل%'` → `SUM(expenses_s)`

### 🔹 5. النقل الداخلي
- **القيمة:** 15
- **الحساب:** عدد السيارات التي تحتوي `note LIKE '%داخلي%'` × 15
- **يُطرح من:** `expenses` و `expenses_s` عند حساب الربح

### 🔹 6. الربح الحقيقي لكل سيارة
**المعادلة:**
```
Profit = (total_s - expenses_s - discount - land_shipping_s) 
       - (total + expenses - discount + land_shipping)
```

**ملاحظات:**
- يتم طرح 15 من `expenses` و `expenses_s` إذا كانت `note` تحتوي على "داخلي"
- `discount` يقلل الربح مباشرة

### 🔹 7. إحصائيات الأرباح
- **أعلى ربح:** `MAX(profit)`
- **أقل ربح:** `MIN(profit)`
- **متوسط الربح:** `AVG(profit)`

### 🔹 8. تحليل الخصومات
- **أعلى خصم:** `MAX(discount)`
- **أقل خصم:** `MIN(discount)`
- **مجموع الخصومات:** `SUM(discount)`
- **أفضل سيارة من حيث الخصم:** `ORDER BY discount DESC LIMIT 1`

### 🔹 9. سجل الخصومات
**الأعمدة:**
- `car_number`
- `vin`
- `discount`
- `total`
- `total_s`
- `profit` (بعد الخصم)

### 🔹 10. أرباح أربيل فقط
- **الفلترة:** `WHERE city = 'Erbil' OR note LIKE '%أربيل%'`
- **الحساب:** مجموع الأرباح للسيارات في أربيل

### 🔹 11. الحولات (Transfers)
- **إجمالي الحولات:** `SUM(amount)`
- **رسوم الحولات:** `SUM(fee)`
- **صافي الحولات:** إجمالي - الرسوم
- **حولات أربيل:** فلترة حسب `note` أو `sender_note` أو `receiver_note`

**ملاحظة:** Transfers لا يحتوي على `owner_id` مباشرة، لذلك يتم الفلترة من خلال `sender_id` و `receiver_id` المرتبطين بـ `users` الذين لديهم `owner_id` المطلوب.

### 🔹 12. الدفعات (Payments)
- **دفعات المشترين:** `BuyerPayment::where('owner_id', $owner_id)->sum('amount')`
- **دفعات المبيعات:** `SalePayment::where('owner_id', $owner_id)->sum('amount')`

### 🔹 13. التدفقات النقدية (Cash Flow)
- **النقد الوارد:** دفعات المبيعات + صافي الحولات
- **النقد الصادر:** دفعات المشترين
- **صافي النقد:** وارد - صادر

### 🔹 14. الأرباح الشهرية
- حساب الربح لكل شهر من السنة (1-12)
- استخدام `year_date` للفلترة حسب السنة
- استخدام `created_at` للفلترة حسب الشهر

---

## 🔐 الأمان والفلترة

### ✅ استخدام `owner_id`
جميع الاستعلامات تستخدم `owner_id` للفلترة:

- ✅ **Cars:** `Car::where('owner_id', $owner_id)`
- ✅ **BuyerPayment:** `BuyerPayment::where('owner_id', $owner_id)`
- ✅ **SalePayment:** `SalePayment::where('owner_id', $owner_id)`
- ✅ **Transfers:** استخدام subquery للتحقق من `owner_id` من خلال `sender_id` و `receiver_id`

### ✅ Middleware
- **Web Routes:** `auth`, `verified`, `check.license`
- **API Routes:** `auth:sanctum`

---

## 🎨 الواجهة (UI)

### 🔹 Layout
- استخدام `AuthenticatedLayout`
- Responsive Design (Mobile, Tablet, Desktop)
- Dark Mode Support

### 🔹 Components Structure
```
Index.vue
├── Filters.vue
├── StatCards.vue (6 cards)
├── TransfersSummary.vue (4 cards)
├── CashFlowCards.vue (3 cards)
├── ProfitChart.vue
├── CashFlowChart.vue
├── DiscountTable.vue
├── CarProfitTable.vue
└── YearClosingSummary.vue
```

### 🔹 Charts
- استخدام SVG للرسوم البيانية (لا يحتاج مكتبات خارجية)
- Line Chart للأرباح الشهرية
- Line Chart للتدفقات النقدية (خطان: وارد وصادر)

---

## 📝 API Response Structure

### GET `/api/statistics?year=2024&month=1`

```json
{
  "total_cars": 100,
  "custom": {
    "purchase": 50000,
    "sale": 60000,
    "total": 110000
  },
  "exchange_benefit": 5000,
  "erbil_expenses": {
    "purchase": 10000,
    "sale": 12000,
    "total": 22000
  },
  "internal_shipping": 150,
  "profit_stats": {
    "max": 5000,
    "min": -500,
    "avg": 2000
  },
  "discount_stats": {
    "max": 1000,
    "min": 50,
    "total": 15000,
    "best_car": {
      "car_number": "12345",
      "vin": "ABC123",
      "discount": 1000
    }
  },
  "discount_records": [...],
  "erbil_profit": 30000,
  "monthly_profits": [1000, 2000, 1500, ...],
  "month_labels": ["يناير", "فبراير", ...],
  "yearly_profit": 24000,
  "cars_with_profit": [...],
  "cars_count": 100,
  "total_customs": 110000,
  "exchange_profit": 5000,
  "net_profit": 22500,
  "net_transfers": 50000,
  "cash_balance": 30000,
  "transfers_summary": {
    "gross_transfers": 52000,
    "transfer_fees": 2000,
    "net_transfers": 50000,
    "erbil_transfers": 15000
  },
  "cash_flow": {
    "cash_in": 80000,
    "cash_out": 50000,
    "net_cash": 30000
  },
  "cash_flow_chart": {
    "labels": ["يناير", "فبراير", ...],
    "cash_in_data": [5000, 6000, ...],
    "cash_out_data": [3000, 4000, ...]
  },
  "year_closing": {
    "year": 2024,
    "total_income": 80000,
    "total_expenses": 57500,
    "total_discounts": 15000,
    "net_year_profit": 22500,
    "carried_profit": 0,
    "is_closed": false
  }
}
```

---

## 🚀 الاستخدام

### الوصول للصفحة
1. تسجيل الدخول
2. الانتقال إلى: `/dashboard/statistics`

### الفلترة
- اختيار السنة من القائمة المنسدلة
- اختيار الشهر (اختياري) من القائمة المنسدلة
- تحديث البيانات تلقائياً عند تغيير الفلاتر

---

## 🔧 التطوير المستقبلي

### ميزات مقترحة
- [ ] تصدير Excel كامل
- [ ] تصدير PDF
- [ ] رسوم بيانية أكثر تفصيلاً (Chart.js أو ApexCharts)
- [ ] مقارنة بين السنوات
- [ ] إغلاق السنة (Year Closing)
- [ ] تنبيهات عند تجاوز عتبات معينة

---

## 📚 التقنيات المستخدمة

### Backend
- Laravel 10
- PHP 8.1+
- MySQL/MariaDB

### Frontend
- Vue 3 (Composition API)
- Inertia.js
- Tailwind CSS
- SVG Charts

---

## 📝 ملاحظات مهمة

1. **جميع الحسابات تأخذ بعين الاعتبار `owner_id`** ✅
2. **لا تعديل على الصفحات الموجودة** ✅
3. **الاعتماد فقط على أعمدة الجدول الحالية** ✅
4. **كود نظيف وقابل للتوسعة** ✅
5. **Vue للعرض فقط - لا حسابات داخل Components** ✅
6. **جميع البيانات تأتي من API واحد** ✅

---

## 🐛 استكشاف الأخطاء

### المشكلة: البيانات لا تظهر
- التحقق من تسجيل الدخول
- التحقق من `owner_id` في قاعدة البيانات
- التحقق من console في المتصفح للأخطاء

### المشكلة: الرسوم البيانية لا تعمل
- التحقق من وجود البيانات (`monthly_profits`, `labels`)
- التحقق من console للأخطاء JavaScript

### المشكلة: الحولات لا تظهر
- التحقق من وجود `sender_id` و `receiver_id` في جدول `transfers`
- التحقق من أن `users` المرتبطين لديهم `owner_id` صحيح

---

## 👨‍💻 المطور

تم تطوير هذه الصفحة كجزء من نظام إدارة السيارات.

---

## 📄 الترخيص

نفس ترخيص المشروع الرئيسي.

---

**آخر تحديث:** 2025-01-03

