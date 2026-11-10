-- ========================================================
-- Lanka News Hub - Clean & Align Categories Table in MySQL
-- Run this in phpMyAdmin on cPanel database (liontec2_news_DB)
-- ========================================================

-- 1. Correct the category slugs, English and Sinhala names
UPDATE `categories` SET `slug` = 'local', `name_en` = 'Local', `name_si` = 'දේශීය', `status` = 'active', `order_index` = 1 WHERE `id` = 1;
UPDATE `categories` SET `slug` = 'politics', `name_en` = 'Politics', `name_si` = 'දේශපාලන', `status` = 'active', `order_index` = 2 WHERE `id` = 2;
UPDATE `categories` SET `slug` = 'business', `name_en` = 'Business', `name_si` = 'ව්‍යාපාරික', `status` = 'active', `order_index` = 3 WHERE `id` = 3;
UPDATE `categories` SET `slug` = 'sports', `name_en` = 'Sports', `name_si` = 'ක්‍රීඩා', `status` = 'active', `order_index` = 4 WHERE `id` = 4;
UPDATE `categories` SET `slug` = 'entertainment', `name_en` = 'Entertainment', `name_si` = 'විනෝදාත්මක', `status` = 'active', `order_index` = 5 WHERE `id` = 5;
UPDATE `categories` SET `slug` = 'technology', `name_en` = 'Technology', `name_si` = 'තාක්ෂණය', `status` = 'active', `order_index` = 6 WHERE `id` = 6;
UPDATE `categories` SET `slug` = 'international', `name_en` = 'International', `name_si` = 'ජාත්‍යන්තර', `status` = 'active', `order_index` = 7 WHERE `id` = 7;

-- 2. Migrate any stray articles from duplicate IDs (8, 9) into canonical IDs (1, 3)
UPDATE `articles` SET `category_id` = 1 WHERE `category_id` = 8;
UPDATE `articles` SET `category_id` = 3 WHERE `category_id` = 9;

-- 3. Deactivate duplicate categories 8 and 9 so they do not show in any queries
UPDATE `categories` SET `status` = 'inactive' WHERE `id` IN (8, 9);

-- 4. Fix any media file_path records that erroneously have /uploads/assets/
UPDATE `media` SET `file_path` = REPLACE(`file_path`, 'uploads/assets/', 'assets/') WHERE `file_path` LIKE '%uploads/assets/%';

SELECT id, name_en, slug, status FROM categories;
