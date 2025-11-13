ALTER TABLE `car_contract`
  ADD COLUMN `verification_token` VARCHAR(64) NULL AFTER `status`;

UPDATE `car_contract`
SET `verification_token` = UUID()
WHERE `verification_token` IS NULL;

ALTER TABLE `car_contract`
  ADD UNIQUE KEY `car_contract_verification_token_unique` (`verification_token`);
ALTER TABLE `car_contract`
  ADD COLUMN `seller_id_number` VARCHAR(100) NULL AFTER `address_seller`,
  ADD COLUMN `buyer_id_number` VARCHAR(100) NULL AFTER `address_buyer`;