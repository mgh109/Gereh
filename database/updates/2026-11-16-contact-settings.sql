-- Run once in phpMyAdmin for existing installations (no terminal needed).
CREATE TABLE IF NOT EXISTS `contact_settings` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `postal_code` VARCHAR(10) NOT NULL,
  `address` TEXT NOT NULL,
  `updated_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `contact_settings` (`id`,`phone`,`email`,`postal_code`,`address`)
SELECT 1,'01133290207','h3ein157@gmail.com','4819675397','مازندران، ساری، محله صادقیه، کوچه اول صادقیه، کوچه ایثار ۱۱، پلاک ۰۰، طبقه همکف'
WHERE NOT EXISTS (SELECT 1 FROM `contact_settings` WHERE `id`=1);
