-- ============================================
-- Migration SQL للمبيعات الداخلية
-- تاريخ: 2025-01-29
-- ============================================

-- 1. إضافة حقل has_internal_sales إلى جدول users (إذا لم يكن موجوداً)
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'has_internal_sales';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TINYINT(1) DEFAULT 0 COMMENT ''تفعيل المبيعات الداخلية'' AFTER `phone`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. إضافة نوع مستخدم للمبيعات الداخلية (إذا لم يكن موجوداً)
-- ملاحظة: تحقق من اسم الجدول (user_type أو user_types) في قاعدة البيانات الخاصة بك
-- إذا كان الجدول اسمه user_types، غيّر user_type إلى user_types في السطر التالي
INSERT INTO `user_type` (`name`, `created_at`, `updated_at`)
SELECT 'internal_sales_client', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `user_type` WHERE `name` = 'internal_sales_client'
);

-- 3. التأكد من وجود INDEX على id في الجداول المرجعية
-- هذا ضروري لإضافة Foreign Key constraints

-- إضافة INDEX على car.id إذا لم يكن موجوداً
-- ملاحظة: إذا كان car.id هو PRIMARY KEY، سيتم تخطي هذا الأمر تلقائياً
SET @dbname = DATABASE();
SET @tablename = 'car';
SET @indexname = 'car_id_index_for_fk';

-- التحقق من وجود أي INDEX على car.id (PRIMARY KEY أو INDEX عادي)
SET @hasIndex = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE
    TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'id'
);

-- إذا لم يكن هناك INDEX، أضف INDEX
SET @preparedStatement = (SELECT IF(
  @hasIndex > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`id`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة INDEX على users.id إذا لم يكن موجوداً
SET @tablename = 'users';
SET @indexname = 'users_id_index_for_fk';

SET @hasIndex = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE
    TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'id'
);

SET @preparedStatement = (SELECT IF(
  @hasIndex > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`id`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. إنشاء جدول internal_sales (إذا لم يكن موجوداً)
-- ملاحظة: إذا كان الجدول موجوداً بالفعل، سيتم تخطي هذا الأمر
CREATE TABLE IF NOT EXISTS `internal_sales` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NOT NULL COMMENT 'user_id للزبون المشتري',
  `car_id` BIGINT UNSIGNED NOT NULL COMMENT 'id السيارة',
  `car_price` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'سعر السيارة',
  `shipping` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'النقل',
  `sale_price` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'سعر البيع',
  `paid_amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'المبلغ المدفوع',
  `expenses` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'المصاريف',
  `additional_expenses` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'مصاريف إضافية',
  `profit` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'الربح (حساب تلقائي: sale_price - car_price - expenses - additional_expenses)',
  `note` TEXT NULL COMMENT 'ملاحظات',
  `sale_date` DATE NULL COMMENT 'تاريخ البيع',
  `payments` JSON NULL COMMENT 'سجل الدفعات الفردية (اختياري)',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `internal_sales_client_id_index` (`client_id`),
  KEY `internal_sales_car_id_index` (`car_id`),
  KEY `internal_sales_client_car_index` (`client_id`, `car_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة Foreign Keys بشكل منفصل (بعد إنشاء الجدول)
-- هذا يمنع الأخطاء إذا كان الجدول موجوداً بالفعل
SET @dbname = DATABASE();
SET @tablename = 'internal_sales';

-- إضافة Foreign Key لـ client_id
SET @fk_name = 'internal_sales_client_id_foreign';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fk_name)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT ', @fk_name, ' FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة Foreign Key لـ car_id
-- ملاحظة: إذا فشل هذا الأمر، يمكنك تخطيه - الجداول ستعمل بدون Foreign Keys
SET @fk_name = 'internal_sales_car_id_foreign';

-- التحقق أولاً من وجود INDEX على car.id
SET @carHasIndex = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE
    TABLE_SCHEMA = @dbname
    AND TABLE_NAME = 'car'
    AND COLUMN_NAME = 'id'
    AND (INDEX_NAME = 'PRIMARY' OR INDEX_NAME LIKE '%id%')
);

-- فقط إذا كان car.id يحتوي على INDEX، أضف Foreign Key
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fk_name)
  ) > 0,
  'SELECT 1',
  IF(
    @carHasIndex > 0,
    CONCAT('ALTER TABLE `', @tablename, '` ADD CONSTRAINT `', @fk_name, '` FOREIGN KEY (`car_id`) REFERENCES `car` (`id`) ON DELETE CASCADE'),
    'SELECT 1'
  )
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. إضافة الحقول الإضافية إذا كان الجدول موجوداً بالفعل
-- إضافة car_price
SET @dbname = DATABASE();
SET @tablename = 'internal_sales';
SET @columnname = 'car_price';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(15,2) DEFAULT 0.00 COMMENT ''سعر السيارة'' AFTER `car_id`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة shipping
SET @columnname = 'shipping';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(15,2) DEFAULT 0.00 COMMENT ''النقل'' AFTER `car_price`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة additional_expenses
SET @columnname = 'additional_expenses';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(15,2) DEFAULT 0.00 COMMENT ''مصاريف إضافية'' AFTER `expenses`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة payments
SET @columnname = 'payments';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' JSON NULL COMMENT ''سجل الدفعات الفردية'' AFTER `paid_amount`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 6. إنشاء جدول buyer_payments (إذا لم يكن موجوداً)
CREATE TABLE IF NOT EXISTS `buyer_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `buyer_id` BIGINT UNSIGNED NOT NULL COMMENT 'المشتري (user_id)',
  `merchant_id` BIGINT UNSIGNED NOT NULL COMMENT 'التاجر (user_id)',
  `internal_sale_id` BIGINT UNSIGNED NULL COMMENT 'رابط بالمبيعة',
  `amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'مبلغ الدفعة',
  `payment_date` DATE NULL COMMENT 'تاريخ الدفعة',
  `note` TEXT NULL COMMENT 'ملاحظات',
  `owner_id` BIGINT UNSIGNED NOT NULL COMMENT 'owner_id للتحكم',
  `created_by` BIGINT UNSIGNED NULL COMMENT 'من أنشأ الدفعة',
  `payment_id` VARCHAR(255) NULL COMMENT 'معرف فريد للدفعة',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `buyer_payments_payment_id_unique` (`payment_id`),
  KEY `buyer_payments_buyer_id_index` (`buyer_id`),
  KEY `buyer_payments_merchant_id_index` (`merchant_id`),
  KEY `buyer_payments_internal_sale_id_index` (`internal_sale_id`),
  KEY `buyer_payments_owner_id_index` (`owner_id`),
  KEY `buyer_payments_buyer_merchant_index` (`buyer_id`, `merchant_id`),
  KEY `buyer_payments_payment_date_index` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة Foreign Keys لجدول buyer_payments بشكل منفصل
SET @dbname = DATABASE();
SET @tablename = 'buyer_payments';

-- إضافة Foreign Key لـ buyer_id
SET @fk_name = 'buyer_payments_buyer_id_foreign';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fk_name)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT ', @fk_name, ' FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة Foreign Key لـ merchant_id
SET @fk_name = 'buyer_payments_merchant_id_foreign';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fk_name)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT ', @fk_name, ' FOREIGN KEY (`merchant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة Foreign Key لـ internal_sale_id
SET @fk_name = 'buyer_payments_internal_sale_id_foreign';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fk_name)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT ', @fk_name, ' FOREIGN KEY (`internal_sale_id`) REFERENCES `internal_sales` (`id`) ON DELETE CASCADE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة Foreign Key لـ owner_id
SET @fk_name = 'buyer_payments_owner_id_foreign';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fk_name)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT ', @fk_name, ' FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- إضافة Foreign Key لـ created_by
SET @fk_name = 'buyer_payments_created_by_foreign';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fk_name)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT ', @fk_name, ' FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 7. تحديث الربح للمبيعات الموجودة (إعادة حساب الربح بالصيغة الصحيحة)
-- الربح = سعر البيع - سعر السيارة - المصاريف - مصاريف إضافية
UPDATE `internal_sales` 
SET `profit` = (`sale_price` - COALESCE(`car_price`, 0) - COALESCE(`expenses`, 0) - COALESCE(`additional_expenses`, 0))
WHERE `profit` IS NOT NULL OR `sale_price` > 0;

-- 8. تحديث car_price للمبيعات الموجودة التي car_price = 0
-- نأخذ car_price من total_s للسيارة
UPDATE `internal_sales` 
INNER JOIN `car` ON `internal_sales`.`car_id` = `car`.`id`
SET `internal_sales`.`car_price` = COALESCE(`car`.`total_s`, 0)
WHERE `internal_sales`.`car_price` = 0 OR `internal_sales`.`car_price` IS NULL;

-- 9. إعادة حساب الربح مرة أخرى بعد تحديث car_price
UPDATE `internal_sales` 
SET `profit` = (`sale_price` - COALESCE(`car_price`, 0) - COALESCE(`expenses`, 0) - COALESCE(`additional_expenses`, 0))
WHERE `sale_price` > 0;

-- ============================================
-- انتهى Migration
-- ============================================

