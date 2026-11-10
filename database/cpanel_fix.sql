-- =============================================================================
-- Lanka News Hub - 100% Fail-Safe MySQL/MariaDB Fix for cPanel phpMyAdmin
-- This uses "IF NOT EXISTS" so it will NEVER fail on duplicate columns!
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Ensure 'articles' has all required columns
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `summary_ta` TEXT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `content_ta` LONGTEXT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `tags` TEXT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `gallery_ids` TEXT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `video_url` VARCHAR(255) NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `video_media_id` INT(11) NULL;
ALTER TABLE `articles` MODIFY `title_si` VARCHAR(255) NULL DEFAULT '';
ALTER TABLE `articles` MODIFY `title_en` VARCHAR(255) NULL DEFAULT '';
ALTER TABLE `articles` MODIFY `status` ENUM('draft','pending','scheduled','published','archived') DEFAULT 'draft';

-- 2. Ensure 'categories' has Tamil support
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `name_ta` VARCHAR(255) NOT NULL DEFAULT '';

-- 3. Ensure 'users' has allowed_languages for multilingual newsroom
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `allowed_languages` VARCHAR(50) NOT NULL DEFAULT 'en,si,ta';

-- 4. Ensure 'advertisements' has modern formats
ALTER TABLE `advertisements` MODIFY COLUMN `priority` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `advertisements` ADD COLUMN IF NOT EXISTS `ad_format` VARCHAR(20) NOT NULL DEFAULT 'image';
ALTER TABLE `advertisements` ADD COLUMN IF NOT EXISTS `advertiser` VARCHAR(150) NULL;
ALTER TABLE `advertisements` ADD COLUMN IF NOT EXISTS `ad_text` TEXT NULL;
ALTER TABLE `advertisements` ADD COLUMN IF NOT EXISTS `cta_label` VARCHAR(80) NULL;

-- 5. Ensure roles exist
INSERT INTO `roles` (`id`, `name`, `slug`) VALUES
(1, 'Administrator', 'super_admin'),
(2, 'Editor', 'editor'),
(3, 'Journalist', 'reporter'),
(4, 'Member', 'member')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

SET FOREIGN_KEY_CHECKS = 1;
