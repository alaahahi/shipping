-- ============================================
-- ملف التحقق من نجاح Migration
-- Verify Migration Success
-- ============================================

-- 1. التحقق من وجود جدول internal_sales
SELECT 
    'internal_sales' AS table_name,
    COUNT(*) AS table_exists
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'internal_sales';

-- 2. التحقق من وجود جدول buyer_payments
SELECT 
    'buyer_payments' AS table_name,
    COUNT(*) AS table_exists
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'buyer_payments';

-- 3. التحقق من وجود حقل has_internal_sales في جدول users
SELECT 
    'has_internal_sales column' AS check_name,
    COUNT(*) AS column_exists
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'users' 
AND column_name = 'has_internal_sales';

-- 4. عرض بنية جدول internal_sales (إذا كان موجوداً)
SET @table_exists = (
    SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'internal_sales'
);

SET @sql = IF(@table_exists > 0, 'DESCRIBE `internal_sales`', 'SELECT "Table internal_sales does not exist" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. عرض بنية جدول buyer_payments (إذا كان موجوداً)
SET @table_exists = (
    SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'buyer_payments'
);

SET @sql = IF(@table_exists > 0, 'DESCRIBE `buyer_payments`', 'SELECT "Table buyer_payments does not exist" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. التحقق من وجود نوع المستخدم internal_sales_client
SELECT 
    'internal_sales_client user type' AS check_name,
    COUNT(*) AS exists_count
FROM `user_type` 
WHERE `name` = 'internal_sales_client';

-- 7. عرض عدد السجلات في internal_sales (إن وجدت)
SET @table_exists = (
    SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'internal_sales'
);

SET @sql = IF(
    @table_exists > 0, 
    'SELECT ''internal_sales records'' AS check_name, COUNT(*) AS record_count FROM `internal_sales`',
    'SELECT ''internal_sales records'' AS check_name, 0 AS record_count, ''Table does not exist'' AS note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. عرض عدد السجلات في buyer_payments (إن وجدت)
SET @table_exists = (
    SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'buyer_payments'
);

SET @sql = IF(
    @table_exists > 0, 
    'SELECT ''buyer_payments records'' AS check_name, COUNT(*) AS record_count FROM `buyer_payments`',
    'SELECT ''buyer_payments records'' AS check_name, 0 AS record_count, ''Table does not exist'' AS note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 9. التحقق من INDEXes على الجداول (إذا كانت موجودة)
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX
FROM information_schema.STATISTICS
WHERE table_schema = DATABASE()
AND TABLE_NAME IN ('internal_sales', 'buyer_payments')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- ============================================
-- إذا كانت جميع النتائج صحيحة، فالمigration نجح!
-- If all results are correct, the migration succeeded!
-- ============================================

