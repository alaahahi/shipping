-- إضافة حقل سعر 100 دولار بالدينار
ALTER TABLE `system_config` 
ADD COLUMN `usd_to_dinar_rate` DECIMAL(10,2) DEFAULT 150.00 
COMMENT 'سعر 100 دولار بالدينار' 
AFTER `usd_to_aed_rate`;

-- تحديث القيمة الافتراضية إذا كان السجل موجود
UPDATE `system_config` SET `usd_to_dinar_rate` = 150.00 WHERE `usd_to_dinar_rate` IS NULL;
