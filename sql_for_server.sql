-- =====================================================
-- SQL للتنفيذ على السيرفر
-- تاريخ: 2026-01-17
-- الوصف: إضافة نظام تحويل العملات وسعر النقل بالدرهم الإماراتي
-- =====================================================

-- 1. إضافة حقل سعر الصرف إلى جدول system_config
ALTER TABLE `system_config` 
ADD COLUMN `usd_to_aed_rate` DECIMAL(10, 4) DEFAULT 3.6725 
COMMENT 'سعر صرف الدولار مقابل الدرهم الإماراتي' 
AFTER `default_price_p`;

-- 2. إضافة حقل سعر النقل بالدرهم الإماراتي إلى جدول trip_companies
ALTER TABLE `trip_companies` 
ADD COLUMN `shipping_price_aed` DECIMAL(10, 2) NULL 
COMMENT 'سعر نقل السيارة بالدرهم الإماراتي' 
AFTER `shipping_price_per_car`;

-- 3. تسجيل الـ migrations في جدول migrations
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES 
('2026_01_17_150716_add_exchange_rate_to_system_config_table', 
    (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) as m)),
('2026_01_17_151149_add_shipping_price_aed_to_trip_companies_table', 
    (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) as m));

-- 4. التحقق من النتيجة
SELECT 
    'system_config' as table_name,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'system_config'
AND COLUMN_NAME = 'usd_to_aed_rate'

UNION ALL

SELECT 
    'trip_companies' as table_name,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'trip_companies'
AND COLUMN_NAME = 'shipping_price_aed';

-- =====================================================
-- ملاحظات:
-- 1. سعر الصرف الافتراضي: 1 USD = 3.6725 AED
-- 2. يمكن تعديل سعر الصرف من صفحة إعدادات النظام
-- 3. عند إدخال سعر النقل بالدرهم، يتم التحويل تلقائياً للدولار
-- =====================================================
