-- =====================================================
-- SQL للتنفيذ على السيرفر
-- تاريخ: 2026-01-25
-- الوصف: إضافة حقل شروط العقد إلى جدول system_config
-- =====================================================

-- 1. إضافة حقل contract_terms إلى جدول system_config
ALTER TABLE `system_config` 
ADD COLUMN `contract_terms` JSON NULL 
COMMENT 'شروط العقد (JSON array)' 
AFTER `usd_to_dinar_rate`;

-- 2. إضافة القيم الافتراضية للشروط إذا لم تكن موجودة
UPDATE `system_config` 
SET `contract_terms` = JSON_ARRAY(
    'علی البائع و المشتری تسجیل السیارة حسب قوانین مدیریة المرور العامة مع إجراء معاملة نقل الملکیة',
    'علی المشتری فحص السیارة قبل الشراء و نحن غیر مسؤولین بعد توقیع عقد المعرض',
    'الطرف الاول مسؤول عن کافة أنواع الغرامات قبل موعد الشراء',
    'صاحب المعرض غیر مسؤول عن السیارة بعد البیع و کل عقد غیر مختوم من المعرض یعتبر باطل',
    'علی المشتري تسجیل السیارة خلال شهر واحد'
)
WHERE `contract_terms` IS NULL;

-- 3. تسجيل الـ migration في جدول migrations
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES 
('2026_01_25_140000_add_contract_terms_to_system_config_table', 
    (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) as m));

-- 4. التحقق من النتيجة
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'system_config'
AND COLUMN_NAME = 'contract_terms';

-- =====================================================
-- ملاحظات:
-- 1. الشروط يمكن تعديلها من صفحة إعدادات النظام
-- 2. الشرط الأخير (التاريخ والوقت) يُضاف تلقائياً في الكود
-- 3. إذا لم تكن هناك شروط محفوظة، سيتم استخدام القيم الافتراضية
-- =====================================================
