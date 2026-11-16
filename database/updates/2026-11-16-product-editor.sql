-- Run once in phpMyAdmin after selecting the GEREH database.
-- Adds optional fields only; existing products remain unchanged.
-- This script can be rerun on MySQL/MariaDB with an empty table prefix.
SET @statement = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='product_code')=0,
 'ALTER TABLE `products` ADD COLUMN `product_code` VARCHAR(80) NULL', 'SELECT 1');
PREPARE editor_update FROM @statement;
EXECUTE editor_update;
DEALLOCATE PREPARE editor_update;
SET @statement = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='item_condition')=0,
 'ALTER TABLE `products` ADD COLUMN `item_condition` VARCHAR(20) NULL', 'SELECT 1');
PREPARE editor_update FROM @statement;
EXECUTE editor_update;
DEALLOCATE PREPARE editor_update;
