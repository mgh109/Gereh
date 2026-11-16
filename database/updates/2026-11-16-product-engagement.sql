-- Select the GEREH database in phpMyAdmin. Safe to run again. Empty table prefix.
CREATE TABLE IF NOT EXISTS product_daily_views (
 product_id BIGINT UNSIGNED NOT NULL, day DATE NOT NULL, views INT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY(product_id,day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS product_comments (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 product_id BIGINT UNSIGNED NOT NULL, author_name VARCHAR(100) NOT NULL, body TEXT NOT NULL,
 rating TINYINT UNSIGNED NULL, status VARCHAR(20) NOT NULL DEFAULT 'pending',
 created_at DATETIME NOT NULL, moderated_at DATETIME NULL,
 INDEX product_status_created (product_id,status,created_at), INDEX status_created (status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
