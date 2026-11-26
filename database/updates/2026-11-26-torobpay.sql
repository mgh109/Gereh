-- Select the existing Gereh database in phpMyAdmin before importing.
-- Existing orders and payments are preserved.
SET @torob_sql = IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'payment_transactions' AND COLUMN_NAME = 'gateway_token'),
    'SELECT 1',
    'ALTER TABLE payment_transactions ADD COLUMN gateway_token TEXT NULL'
);
PREPARE torob_stmt FROM @torob_sql;
EXECUTE torob_stmt;
DEALLOCATE PREPARE torob_stmt;

SET @torob_sql = IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'payment_transactions' AND COLUMN_NAME = 'gateway_redirect_url'),
    'SELECT 1',
    'ALTER TABLE payment_transactions ADD COLUMN gateway_redirect_url TEXT NULL'
);
PREPARE torob_stmt FROM @torob_sql;
EXECUTE torob_stmt;
DEALLOCATE PREPARE torob_stmt;
