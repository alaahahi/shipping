-- =====================================================
-- فهارس أساسية لتحسين البحث - تنفيذ سريع
-- Essential indexes for search optimization - quick execution
-- =====================================================

-- فهارس أساسية مطلوبة فوراً
-- Essential indexes needed immediately

-- 1. فهرس على owner_id (الأهم - مستخدم في كل استعلام)
ALTER TABLE `car` ADD INDEX `idx_car_owner_id` (`owner_id`);

-- 2. فهرس على vin للبحث السريع
ALTER TABLE `car` ADD INDEX `idx_car_vin` (`vin`);

-- 3. فهرس على car_number للبحث السريع
ALTER TABLE `car` ADD INDEX `idx_car_number` (`car_number`);

-- 4. فهرس على car_type للبحث السريع
ALTER TABLE `car` ADD INDEX `idx_car_type` (`car_type`);

-- 5. فهرس على client_id للعلاقات السريعة
ALTER TABLE `car` ADD INDEX `idx_car_client_id` (`client_id`);

-- 6. فهرس على name في جدول users للبحث السريع
ALTER TABLE `users` ADD INDEX `idx_users_name` (`name`);

-- =====================================================
-- فهارس مركبة للبحث المحسن (تنفيذ بعد الأساسية)
-- Composite indexes for optimized search (execute after basic ones)
-- =====================================================

-- 7. فهرس مركب على (owner_id, vin) - الأكثر استخداماً
ALTER TABLE `car` ADD INDEX `idx_car_owner_vin` (`owner_id`, `vin`);

-- 8. فهرس مركب على (owner_id, car_number)
ALTER TABLE `car` ADD INDEX `idx_car_owner_number` (`owner_id`, `car_number`);

-- 9. فهرس مركب على (owner_id, car_type)
ALTER TABLE `car` ADD INDEX `idx_car_owner_type` (`owner_id`, `car_type`);

-- 10. فهرس مركب على (owner_id, client_id)
ALTER TABLE `car` ADD INDEX `idx_car_owner_client` (`owner_id`, `client_id`);

-- =====================================================
-- فهارس إضافية للجداول المرتبطة
-- Additional indexes for related tables
-- =====================================================

-- 11. فهرس على car_id في جدول contract (إذا كان موجود)
-- ALTER TABLE `contract` ADD INDEX `idx_contract_car_id` (`car_id`);

-- 12. فهرس على car_id في جدول exit_car (إذا كان موجود)
-- ALTER TABLE `exit_car` ADD INDEX `idx_exit_car_car_id` (`car_id`);

-- =====================================================
-- ملاحظة مهمة
-- Important Note
-- =====================================================

/*
تنفيذ الفهارس:
1. نفذ الفهارس الأساسية أولاً (1-6)
2. انتظر حتى تكتمل
3. نفذ الفهارس المركبة (7-10)
4. اختبر الأداء بعد كل مجموعة

Index Execution:
1. Execute basic indexes first (1-6)
2. Wait for completion
3. Execute composite indexes (7-10)
4. Test performance after each group
*/
