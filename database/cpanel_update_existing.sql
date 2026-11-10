-- =============================================================================
-- Lanka News Hub - Safe Database Update & Migration Script for cPanel
-- Run this in phpMyAdmin -> SQL tab to update an existing database.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Ensure 'advertisements' has all required columns
-- 'priority' is modified safely if existing; new fields are added safely
ALTER TABLE `advertisements` 
  MODIFY COLUMN `priority` INT(11) NOT NULL DEFAULT 1,
  ADD COLUMN `ad_format` VARCHAR(20) NOT NULL DEFAULT 'image',
  ADD COLUMN `advertiser` VARCHAR(150) NULL,
  ADD COLUMN `ad_text` TEXT NULL,
  ADD COLUMN `cta_label` VARCHAR(80) NULL;

-- 2. Ensure 'categories' has Tamil support
ALTER TABLE `categories` 
  ADD COLUMN `name_ta` VARCHAR(255) NOT NULL DEFAULT '';

-- 2b. Ensure 'users' has allowed_languages for multilingual writers
ALTER TABLE `users`
  ADD COLUMN `allowed_languages` VARCHAR(50) NOT NULL DEFAULT 'en,si,ta';

-- 3. Ensure 'articles' has trilingual fields and media relations
ALTER TABLE `articles` 
  MODIFY `title_si` VARCHAR(255) NULL DEFAULT '',
  MODIFY `title_en` VARCHAR(255) NULL DEFAULT '',
  ADD COLUMN `title_ta` VARCHAR(255) NULL DEFAULT '',
  ADD COLUMN `summary_ta` TEXT NULL,
  ADD COLUMN `content_ta` LONGTEXT NULL,
  ADD COLUMN `tags` TEXT NULL,
  ADD COLUMN `gallery_ids` TEXT NULL,
  ADD COLUMN `video_url` VARCHAR(255) NULL,
  ADD COLUMN `video_media_id` INT(11) NULL;

-- 4. Update status enum on articles
ALTER TABLE `articles` 
  MODIFY `status` ENUM('draft','pending','scheduled','published','archived') DEFAULT 'draft';

-- 5. Ensure 'roles' table has all 4 roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `slug`) VALUES
(1, 'Administrator', 'super_admin'),
(2, 'Editor', 'editor'),
(3, 'Journalist', 'reporter'),
(4, 'Member', 'member')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 6. Ensure 'article_revisions' exists
CREATE TABLE IF NOT EXISTS `article_revisions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `article_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `snapshot` LONGTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `revision_article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Ensure 'classified_categories' exists
CREATE TABLE IF NOT EXISTS `classified_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_en` VARCHAR(255) NOT NULL,
  `name_si` VARCHAR(255) NOT NULL,
  `name_ta` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `classified_categories` (`id`, `name_en`, `name_si`, `name_ta`, `slug`) VALUES
(1, 'Property', 'දේපළ', 'சொத்துகள்', 'property'),
(2, 'Jobs', 'රැකියා', 'வேலைகள்', 'jobs'),
(3, 'Vehicles', 'වාහන', 'வாகනங்கள்', 'vehicles'),
(4, 'Business Promotions', 'ව්‍යාපාර ප්‍රවර්ධන', 'வணிக விளம்பரங்கள்', 'business'),
(5, 'Matrimonial', 'මංගල දැන්වීම්', 'திருமணம்', 'matrimonial'),
(6, 'Services', 'සේවා', 'சேவைகள்', 'services'),
(7, 'Community Notices', 'ප්‍රජා නිවේදන', 'சமூக அறிவிப்புகள்', 'community')
ON DUPLICATE KEY UPDATE `name_en` = VALUES(`name_en`);

-- 8. Ensure 'classifieds' exists
CREATE TABLE IF NOT EXISTS `classifieds` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `language` VARCHAR(2) NOT NULL DEFAULT 'en',
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `price` DECIMAL(12,2) DEFAULT NULL,
  `contact_name` VARCHAR(150) NOT NULL,
  `contact_email` VARCHAR(255) NOT NULL,
  `contact_phone` VARCHAR(40) NOT NULL,
  `images` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `moderation_note` TEXT,
  `premium_requested` TINYINT(1) NOT NULL DEFAULT 0,
  `is_premium` TINYINT(1) NOT NULL DEFAULT 0,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `classifieds_public` (`status`,`expires_at`,`category_id`),
  KEY `idx_classifieds_user` (`user_id`,`status`),
  KEY `idx_classifieds_lookup` (`status`,`language`,`expires_at`,`is_premium`,`created_at`),
  CONSTRAINT `classifieds_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `classified_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Ensure 'favorites' exists
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `classified_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_classified` (`user_id`,`classified_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Ensure 'payments' exists
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `classified_id` INT(11) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'LKR',
  `reference` VARCHAR(150) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `recorded_by` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `idx_payments_classified` (`classified_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Ensure 'menus' exists
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_en` VARCHAR(100) NOT NULL,
  `name_si` VARCHAR(100) NOT NULL,
  `name_ta` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `order_index` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Ensure 'login_attempts' exists
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `bucket` VARCHAR(64) NOT NULL,
  `attempted_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login_bucket` (`bucket`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Ensure 'schema_versions' exists
CREATE TABLE IF NOT EXISTS `schema_versions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(100) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `schema_versions` (`version`, `applied_at`) VALUES
('base', NOW()),
('web-1', NOW()),
('web-perf-1', NOW())
ON DUPLICATE KEY UPDATE `applied_at` = VALUES(`applied_at`);

-- 14. Performance Indexes
ALTER TABLE `articles` ADD INDEX `articles_public` (`status`,`published_at`,`category_id`);
ALTER TABLE `articles` ADD INDEX `idx_articles_breaking` (`status`,`is_breaking`,`published_at`);
ALTER TABLE `articles` ADD INDEX `idx_articles_featured` (`status`,`is_featured`,`published_at`);
ALTER TABLE `articles` ADD INDEX `idx_articles_author` (`author_id`,`status`);
ALTER TABLE `articles` ADD INDEX `idx_articles_category` (`category_id`,`status`,`published_at`);
ALTER TABLE `advertisements` ADD INDEX `idx_ads_lookup` (`is_active`,`position`,`priority`);

-- 15. Ensure sample advertisements for all 11 placements exist
INSERT INTO `advertisements` (`id`, `title`, `position`, `ad_format`, `advertiser`, `ad_text`, `cta_label`, `image_path`, `url`, `priority`, `is_active`) VALUES
(1, 'SriLankan Airlines · Discover The Pearl of The Indian Ocean', 'header_728x90', 'image', 'SriLankan Airlines', NULL, NULL, 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1200&h=150&fit=crop&q=80', 'https://www.srilankan.com', 10, 1),
(2, 'Ceylon Pure Tea · Handcrafted Single Origin Excellence', 'top_left', 'image', 'Sri Lanka Tea Board', NULL, NULL, 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=600&h=120&fit=crop&q=80', 'https://www.pureceylontea.com', 10, 1),
(3, 'Colombo Port City · South Asia’s Premier Offshore Financial Hub', 'top_right', 'image', 'Port City Colombo', NULL, NULL, 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&h=120&fit=crop&q=80', 'https://www.portcitycolombo.lk', 10, 1),
(4, 'Commercial Bank Flash · Next-Generation Digital Banking', 'homepage_mid_728x90', 'image', 'Commercial Bank of Ceylon', NULL, NULL, 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=1200&h=150&fit=crop&q=80', 'https://www.combank.lk', 10, 1),
(5, 'Dialog 5G Home Broadband · Unlimited Entertainment', 'inline_feed', 'native', 'Dialog Axiata PLC', 'Experience blazing speeds with Dialog 5G across major Sri Lankan cities. Plug and play router delivered to your doorstep.', 'Explore 5G Plans', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=400&h=300&fit=crop&q=80', 'https://www.dialog.lk', 10, 1),
(6, 'Cinnamon Grand Colombo · Luxury Stays in City Centre', 'sidebar_300x250', 'image', 'Cinnamon Hotels & Resorts', NULL, NULL, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=350&h=280&fit=crop&q=80', 'https://www.cinnamonhotels.com', 10, 1),
(7, 'Bank of Ceylon · Bankers to the Nation Since 1939', 'sidebar_300x600', 'image', 'Bank of Ceylon', NULL, NULL, 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=350&h=700&fit=crop&q=80', 'https://www.boc.lk', 10, 1),
(8, 'Hayleys Solar · Clean Green Renewable Power', 'in_article', 'native', 'Hayleys Fentons Solar', 'Reduce your electricity bills by up to 90% with Tier-1 solar panels and guaranteed 25-year performance warranty.', 'Request Free Survey', 'https://images.unsplash.com/photo-1509391365360-2e959784a276?w=400&h=300&fit=crop&q=80', 'https://www.hayleysfentons.com', 10, 1),
(9, 'SLT-MOBITEL · The National ICT Solutions Provider', 'footer', 'image', 'SLT-MOBITEL', NULL, NULL, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&h=150&fit=crop&q=80', 'https://www.sltmobitel.lk', 10, 1),
(10, 'Sri Lanka Tourism · You Will Come Back for Love', 'sticky', 'image', 'Sri Lanka Tourism Promotion Bureau', NULL, NULL, 'https://images.unsplash.com/photo-1546708973-b339540b5162?w=800&h=120&fit=crop&q=80', 'https://www.srilanka.travel', 10, 1),
(11, 'Lanka Premier League 2026 · Grandstand Tickets Live', 'popup', 'native', 'Sri Lanka Cricket (SLC)', 'Book your grandstand tickets online for LPL 2026 under the floodlights in Colombo and Kandy.', 'Book Tickets Now', 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=500&h=350&fit=crop&q=80', 'https://www.srilankacricket.lk', 10, 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `position` = VALUES(`position`), `ad_format` = VALUES(`ad_format`), `advertiser` = VALUES(`advertiser`), `ad_text` = VALUES(`ad_text`), `cta_label` = VALUES(`cta_label`), `image_path` = VALUES(`image_path`), `url` = VALUES(`url`), `priority` = VALUES(`priority`), `is_active` = VALUES(`is_active`);

SET FOREIGN_KEY_CHECKS = 1;
