-- ============================================
-- نظام التخليص والنقل الداخلي للشركات
-- تاريخ: 2026-01-18
-- ============================================

-- الخطوة 1: إضافة الحقول الجديدة لجدول trip_companies
-- ============================================

ALTER TABLE `trip_companies` 
ADD COLUMN `clearance_per_car` DECIMAL(10, 2) DEFAULT 40.00 COMMENT 'التخليص لكل سيارة بالدولار' AFTER `shipping_price_aed`,
ADD COLUMN `internal_transport_total` DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'النقل الداخلي الكلي بالدولار' AFTER `clearance_per_car`;

-- الخطوة 2: تسجيل الـ Migration
-- ============================================

INSERT INTO `migrations` (`migration`, `batch`) 
VALUES ('2026_01_18_122148_add_clearance_and_transport_to_trip_cars_table', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- ============================================
-- التحقق من النجاح
-- ============================================

-- عرض الحقول الجديدة
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'trip_companies'
AND COLUMN_NAME IN ('clearance_per_car', 'internal_transport_total');

-- عرض بيانات تجريبية
SELECT 
    id,
    company_id,
    trip_id,
    shipping_price_per_car,
    clearance_per_car,
    internal_transport_total,
    created_at
FROM trip_companies
LIMIT 5;

-- ============================================
-- مثال على الاستخدام
-- ============================================

-- تحديث شحنة معينة
-- UPDATE trip_companies 
-- SET 
--     clearance_per_car = 40.00,
--     internal_transport_total = 1500.00
-- WHERE id = 1;

-- حساب الإجمالي لشحنة
-- SELECT 
--     tc.id,
--     c.name AS company_name,
--     t.ship_name,
--     COUNT(tcr.id) AS cars_count,
--     tc.shipping_price_per_car,
--     tc.clearance_per_car,
--     tc.internal_transport_total,
--     (tc.shipping_price_per_car * COUNT(tcr.id)) AS total_shipping,
--     (tc.clearance_per_car * COUNT(tcr.id)) AS total_clearance,
--     tc.internal_transport_total AS total_transport,
--     ((tc.shipping_price_per_car * COUNT(tcr.id)) + 
--      (tc.clearance_per_car * COUNT(tcr.id)) + 
--      tc.internal_transport_total) AS grand_total
-- FROM trip_companies tc
-- LEFT JOIN users c ON tc.company_id = c.id
-- LEFT JOIN trips t ON tc.trip_id = t.id
-- LEFT JOIN trip_cars tcr ON tcr.trip_company_id = tc.id
-- WHERE tc.id = 1
-- GROUP BY tc.id;

-- ============================================
-- ملاحظات مهمة
-- ============================================

-- 1. clearance_per_car:
--    - القيمة الافتراضية: 40 دولار
--    - يُضرب في عدد السيارات
--    - مثال: 40 × 50 سيارة = 2,000 دولار

-- 2. internal_transport_total:
--    - المبلغ الكلي للنقل الداخلي
--    - يُقسم على عدد السيارات تلقائياً
--    - مثال: 1,500 ÷ 50 سيارة = 30 دولار/سيارة

-- 3. الحساب النهائي:
--    الإجمالي = الشحن + التخليص + النقل
--    مثال: 20,000 + 2,000 + 1,500 = 23,500 دولار

-- ============================================
-- تم بنجاح! ✅
-- ============================================
