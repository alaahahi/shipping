-- =====================================================
-- SQL للتنفيذ على السيرفر - نظام الكلفة والربح
-- تاريخ: 2026-01-17
-- الوصف: إضافة نظام تكلفة النقل وعمولة القبطان مع حساب الربح لكل شركة
-- =====================================================

-- إضافة حقول الكلفة إلى جدول trips
ALTER TABLE `trips` 
ADD COLUMN `cost_per_car_aed` DECIMAL(10, 2) NULL 
COMMENT 'سعر الكلفة لكل سيارة بالدرهم الإماراتي' 
AFTER `total_expenses`;

ALTER TABLE `trips` 
ADD COLUMN `captain_commission_aed` DECIMAL(10, 2) NULL 
COMMENT 'عمولة القبطان لكل سيارة بالدرهم الإماراتي' 
AFTER `cost_per_car_aed`;

ALTER TABLE `trips` 
ADD COLUMN `purchase_price_aed` DECIMAL(10, 2) NULL 
COMMENT 'سعر الشراء لكل سيارة بالدرهم الإماراتي (الكلفة - العمولة)' 
AFTER `captain_commission_aed`;

-- تسجيل الـ migration
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES ('2026_01_17_151633_add_cost_fields_to_trips_table', 
    (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) as m));

-- التحقق من النتيجة
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'trips'
AND COLUMN_NAME IN ('cost_per_car_aed', 'captain_commission_aed', 'purchase_price_aed');

-- =====================================================
-- شرح النظام الجديد:
-- =====================================================
-- 1. سعر الكلفة الإجمالي (cost_per_car_aed):
--    - السعر الكامل لنقل السيارة (مثال: 360 درهم)
--    - يشمل عمولة القبطان
--
-- 2. عمولة القبطان (captain_commission_aed):
--    - العمولة المخصصة للقبطان من كل سيارة (مثال: 20 درهم)
--
-- 3. سعر الشراء الفعلي (purchase_price_aed):
--    - سعر الكلفة - عمولة القبطان (360 - 20 = 340 درهم)
--    - هذا هو السعر الفعلي الذي تدفعه الشركة
--
-- 4. حساب الربح:
--    - الربح لكل سيارة = سعر البيع (shipping_price_aed) - سعر الشراء (purchase_price_aed)
--    - الربح الإجمالي = الربح لكل سيارة × عدد السيارات
--    - نسبة الربح = (الربح / سعر الشراء) × 100
--
-- مثال عملي:
-- - سعر الكلفة: 360 درهم
-- - عمولة القبطان: 20 درهم
-- - سعر الشراء: 340 درهم (360 - 20)
-- - سعر البيع للشركة: 400 درهم
-- - الربح لكل سيارة: 60 درهم (400 - 340)
-- - إذا كان عدد السيارات 50، الربح الإجمالي: 3000 درهم (60 × 50)
-- - نسبة الربح: 17.6% ((60 / 340) × 100)
-- =====================================================
