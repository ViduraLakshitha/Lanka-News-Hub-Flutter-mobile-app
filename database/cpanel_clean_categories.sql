DELETE FROM `categories` WHERE `id` IN (8, 9);

UPDATE `categories` SET `slug` = CONCAT('temp_', `id`) WHERE `id` BETWEEN 1 AND 7;

UPDATE `categories` SET `slug` = 'local', `name_en` = 'Local', `name_si` = 'දේශීය', `status` = 'active', `order_index` = 1 WHERE `id` = 1;
UPDATE `categories` SET `slug` = 'politics', `name_en` = 'Politics', `name_si` = 'දේශපාලන', `status` = 'active', `order_index` = 2 WHERE `id` = 2;
UPDATE `categories` SET `slug` = 'business', `name_en` = 'Business', `name_si` = 'ව්‍යාපාරික', `status` = 'active', `order_index` = 3 WHERE `id` = 3;
UPDATE `categories` SET `slug` = 'sports', `name_en` = 'Sports', `name_si` = 'ක්‍රීඩා', `status` = 'active', `order_index` = 4 WHERE `id` = 4;
UPDATE `categories` SET `slug` = 'entertainment', `name_en` = 'Entertainment', `name_si` = 'විනෝදාත්මක', `status` = 'active', `order_index` = 5 WHERE `id` = 5;
UPDATE `categories` SET `slug` = 'technology', `name_en` = 'Technology', `name_si` = 'තාක්ෂණය', `status` = 'active', `order_index` = 6 WHERE `id` = 6;
UPDATE `categories` SET `slug` = 'international', `name_en` = 'International', `name_si` = 'ජාත්‍යන්තර', `status` = 'active', `order_index` = 7 WHERE `id` = 7;
