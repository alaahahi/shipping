-- ============================================
-- ملف التحقق من نجاح Migration (نسخة مبسطة)
-- Verify Migration Success (Simple Version)
-- ============================================

-- 1. التحقق من وجود جدول internal_sales
SELECT 
    'internal_sales' AS table_name,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ موجود'
        ELSE '❌ غير موجود'
    END AS status,
    COUNT(*) AS table_exists
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'internal_sales';

-- 2. التحقق من وجود جدول buyer_payments
SELECT 
    'buyer_payments' AS table_name,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ موجود'
        ELSE '❌ غير موجود'
    END AS status,
    COUNT(*) AS table_exists
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'buyer_payments';

-- 3. التحقق من وجود حقل has_internal_sales في جدول users
SELECT 
    'has_internal_sales column' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ موجود'
        ELSE '❌ غير موجود'
    END AS status,
    COUNT(*) AS column_exists
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'users' 
AND column_name = 'has_internal_sales';

-- 4. التحقق من وجود جدول user_type أولاً
SELECT 
    'user_type table exists' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ موجود'
        ELSE '❌ غير موجود'
    END AS status,
    COUNT(*) AS table_exists
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'user_type';

-- 5. التحقق من وجود نوع المستخدم internal_sales_client
-- ملاحظة: هذا الاستعلام يعمل فقط إذا كان جدول user_type موجوداً
-- إذا كان الجدول غير موجود، سيظهر خطأ - يمكنك تخطيه
SELECT 
    'internal_sales_client user type' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ موجود'
        ELSE '❌ غير موجود'
    END AS status,
    COUNT(*) AS exists_count
FROM `user_type` 
WHERE `name` = 'internal_sales_client';

-- ============================================
-- إذا كانت جميع النتائج تظهر "✅ موجود"، فالمigration نجح!
-- If all results show "✅ موجود", the migration succeeded!
-- 
-- ملاحظة: إذا ظهر خطأ في الاستعلام الأخير (user_type)، 
-- فهذا يعني أن جدول user_type غير موجود في قاعدة البيانات
-- وهذا ليس مشكلة كبيرة - يمكنك تخطيه
-- ============================================
