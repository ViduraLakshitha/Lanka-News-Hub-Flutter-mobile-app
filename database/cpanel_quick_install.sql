-- =============================================================================
-- Lanka News Hub - Complete MySQL Database Schema & Initial Data
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+ (cPanel phpMyAdmin)
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- -----------------------------------------------------------------------------
-- Table: roles
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `slug`) VALUES
(1, 'Administrator', 'super_admin'),
(2, 'Editor', 'editor'),
(3, 'Journalist', 'reporter'),
(4, 'Member', 'member')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- -----------------------------------------------------------------------------
-- Table: users
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Admin Account: admin@example.com / Password: MemberPassword123!
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `status`) VALUES
(1, 'Chief Administrator', 'admin@example.com', '$2y$12$R.C16cQyR1K0/qG74t6Cj.s9xRkW0i.79u588pW7mE6F0kS3kRz3y', 1, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- -----------------------------------------------------------------------------
-- Table: categories
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_si` varchar(255) NOT NULL,
  `name_ta` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `order_index` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name_en`, `name_si`, `name_ta`, `slug`, `order_index`, `status`) VALUES
(1, 'Local', 'දේශීය', 'உள்ளூர்', 'local', 1, 'active'),
(2, 'Politics', 'දේශපාලන', 'அரசியல்', 'politics', 2, 'active'),
(3, 'Business', 'ව්‍යාපාර', 'வணிகம்', 'business', 3, 'active'),
(4, 'Sports', 'ක්‍රීඩා', 'விளையாட்டு', 'sports', 4, 'active'),
(5, 'Entertainment', 'විනෝදාස්වාද', 'பொழுதுபோக்கு', 'entertainment', 5, 'active'),
(6, 'Technology', 'තාක්ෂණය', 'தொழில்நுட்பம்', 'technology', 6, 'active'),
(7, 'International', 'විදෙස්', 'சர்வதேசம்', 'international', 7, 'active')
ON DUPLICATE KEY UPDATE `name_en` = VALUES(`name_en`), `name_si` = VALUES(`name_si`), `name_ta` = VALUES(`name_ta`);

-- -----------------------------------------------------------------------------
-- Table: media
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uploader_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uploader_id` (`uploader_id`),
  KEY `idx_media_type` (`file_type`),
  CONSTRAINT `media_ibfk_1` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: articles
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `featured_image_id` int(11) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_si` varchar(255) NOT NULL DEFAULT '',
  `title_ta` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL,
  `summary_en` text,
  `summary_si` text,
  `summary_ta` text,
  `content_en` longtext,
  `content_si` longtext,
  `content_ta` longtext,
  `tags` text,
  `gallery_ids` text,
  `video_url` varchar(255) DEFAULT NULL,
  `video_media_id` int(11) DEFAULT NULL,
  `status` enum('draft','pending','scheduled','published','archived') DEFAULT 'draft',
  `is_breaking` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `priority` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `category_id` (`category_id`),
  KEY `featured_image_id` (`featured_image_id`),
  KEY `articles_public` (`status`,`published_at`,`category_id`),
  KEY `idx_articles_breaking` (`status`,`is_breaking`,`published_at`),
  KEY `idx_articles_featured` (`status`,`is_featured`,`published_at`),
  KEY `idx_articles_author` (`author_id`,`status`),
  KEY `idx_articles_category` (`category_id`,`status`,`published_at`),
  CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: article_revisions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `article_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `snapshot` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `revision_article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: advertisements
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `advertisements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `position` varchar(50) NOT NULL,
  `ad_format` varchar(20) NOT NULL DEFAULT 'image',
  `advertiser` varchar(150) DEFAULT NULL,
  `ad_text` text,
  `cta_label` varchar(80) DEFAULT NULL,
  `ad_code` text,
  `image_path` varchar(255) DEFAULT NULL,
  `url` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ads_lookup` (`is_active`,`position`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Advertisements across all 11 placement locations
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

-- -----------------------------------------------------------------------------
-- Table: classified_categories
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `classified_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) NOT NULL,
  `name_si` varchar(255) NOT NULL,
  `name_ta` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `classified_categories` (`id`, `name_en`, `name_si`, `name_ta`, `slug`) VALUES
(1, 'Property', 'දේපළ', 'சொத்துகள்', 'property'),
(2, 'Jobs', 'රැකියා', 'வேலைகள்', 'jobs'),
(3, 'Vehicles', 'වාහන', 'வாகனங்கள்', 'vehicles'),
(4, 'Business Promotions', 'ව්‍යාපාර ප්‍රවර්ධන', 'வணிக விளம்பரங்கள்', 'business'),
(5, 'Matrimonial', 'මංගල දැන්වීම්', 'திருமணம்', 'matrimonial'),
(6, 'Services', 'සේවා', 'சேவைகள்', 'services'),
(7, 'Community Notices', 'ප්‍රජා නිවේදන', 'சமூக அறிவிப்புகள்', 'community')
ON DUPLICATE KEY UPDATE `name_en` = VALUES(`name_en`), `name_si` = VALUES(`name_si`), `name_ta` = VALUES(`name_ta`);

-- -----------------------------------------------------------------------------
-- Table: classifieds
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `classifieds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `language` varchar(2) NOT NULL DEFAULT 'en',
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(150) NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `contact_name` varchar(150) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(40) NOT NULL,
  `images` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `moderation_note` text,
  `premium_requested` tinyint(1) NOT NULL DEFAULT 0,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `classifieds_public` (`status`,`expires_at`,`category_id`),
  KEY `idx_classifieds_user` (`user_id`,`status`),
  KEY `idx_classifieds_lookup` (`status`,`language`,`expires_at`,`is_premium`,`created_at`),
  CONSTRAINT `classifieds_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `classified_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: favorites
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `classified_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_classified` (`user_id`,`classified_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: payments
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classified_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'LKR',
  `reference` varchar(150) NOT NULL,
  `status` varchar(20) NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `idx_payments_classified` (`classified_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: menus
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_en` varchar(100) NOT NULL,
  `name_si` varchar(100) NOT NULL,
  `name_ta` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: login_attempts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bucket` varchar(64) NOT NULL,
  `attempted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login_bucket` (`bucket`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: settings
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Lanka News Hub'),
('contact_email', 'editor@example.com'),
('language_en', '1'),
('language_si', '1'),
('language_ta', '1'),
('adsense_id', ''),
('ga_id', ''),
('gtm_id', '')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- -----------------------------------------------------------------------------
-- Table: activity_logs
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_logs_user_date` (`user_id`,`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: schema_versions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `schema_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(100) NOT NULL,
  `applied_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `schema_versions` (`version`, `applied_at`) VALUES
('base', NOW()),
('web-1', NOW()),
('web-perf-1', NOW())
ON DUPLICATE KEY UPDATE `applied_at` = VALUES(`applied_at`);

SET FOREIGN_KEY_CHECKS = 1;
