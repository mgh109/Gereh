-- Existing contact_settings installations: add only missing badge columns.
SET @badge_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_settings' AND COLUMN_NAME='enamad_link')=0, CONCAT('ALTER TABLE contact_settings ADD COLUMN enamad_link VARCHAR(2000) NOT NULL DEFAULT ', CHAR(39), CHAR(39)), 'SELECT 1');
PREPARE badge_stmt FROM @badge_sql;
EXECUTE badge_stmt;
DEALLOCATE PREPARE badge_stmt;
SET @badge_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contact_settings' AND COLUMN_NAME='enamad_image_url')=0, CONCAT('ALTER TABLE contact_settings ADD COLUMN enamad_image_url VARCHAR(2000) NOT NULL DEFAULT ', CHAR(39), CHAR(39)), 'SELECT 1');
PREPARE badge_stmt FROM @badge_sql;
EXECUTE badge_stmt;
DEALLOCATE PREPARE badge_stmt;
