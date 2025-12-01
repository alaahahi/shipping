-- Migration: Add verification_token to car_damage_reports table
-- Date: 2025-12-01

-- Add verification_token column
ALTER TABLE `car_damage_reports` 
ADD COLUMN `verification_token` VARCHAR(255) NULL AFTER `id`;

-- Add unique index on verification_token
ALTER TABLE `car_damage_reports` 
ADD UNIQUE INDEX `car_damage_reports_verification_token_unique` (`verification_token`);

-- Update existing records to generate verification tokens (using UUID format)
-- Note: You may need to adjust this based on your database UUID function
UPDATE `car_damage_reports` 
SET `verification_token` = CONCAT(
    SUBSTRING(MD5(CONCAT(id, created_at)), 1, 8), '-',
    SUBSTRING(MD5(CONCAT(id, created_at)), 9, 4), '-',
    SUBSTRING(MD5(CONCAT(id, created_at)), 13, 4), '-',
    SUBSTRING(MD5(CONCAT(id, created_at)), 17, 4), '-',
    SUBSTRING(MD5(CONCAT(id, created_at)), 21, 12)
)
WHERE `verification_token` IS NULL;

