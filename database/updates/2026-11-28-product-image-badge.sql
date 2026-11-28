SET @badge_sql = IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'products' AND COLUMN_NAME = 'image_badge'),
    'SELECT 1',
    'ALTER TABLE products ADD COLUMN image_badge VARCHAR(40) NULL'
);
PREPARE badge_stmt FROM @badge_sql;
EXECUTE badge_stmt;
DEALLOCATE PREPARE badge_stmt;
