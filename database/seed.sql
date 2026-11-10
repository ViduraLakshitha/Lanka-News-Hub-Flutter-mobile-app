INSERT IGNORE INTO `roles` (`id`, `name`, `slug`) VALUES
(1, 'Super Admin', 'super_admin'),
(2, 'Editor', 'editor'),
(3, 'Reporter', 'reporter');

-- Password is 'admin123'
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `status`) VALUES
(1, 'Chief Editor', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active');

-- Default Categories (English)
INSERT INTO `categories` (`id`, `name_si`, `name_en`, `slug`, `color`, `order_index`, `status`) VALUES
(1, 'Sri Lanka', 'Sri Lanka', 'sri-lanka', '#dc2626', 1, 'active'),
(2, 'Politics', 'Politics', 'politics', '#2563eb', 2, 'active'),
(3, 'Business', 'Business', 'business', '#059669', 3, 'active'),
(4, 'Sports', 'Sports', 'sports', '#ea580c', 4, 'active'),
(5, 'World', 'World', 'world', '#7c3aed', 5, 'active'),
(6, 'Technology', 'Technology', 'technology', '#0284c7', 6, 'active'),
(7, 'Entertainment', 'Entertainment', 'entertainment', '#db2777', 7, 'active')
ON DUPLICATE KEY UPDATE `name_en` = VALUES(`name_en`), `name_si` = VALUES(`name_si`);

-- Comprehensive Sample News Articles
INSERT INTO `articles` (`id`, `author_id`, `category_id`, `title_si`, `title_en`, `slug`, `summary_si`, `summary_en`, `content_si`, `content_en`, `status`, `is_breaking`, `is_featured`, `views`, `published_at`) VALUES
(1, 1, 1, 
 'Sri Lanka Unveils Comprehensive $15B Digital Economy Roadmap for 2030', 
 'Sri Lanka Unveils Comprehensive $15B Digital Economy Roadmap for 2030', 
 'sri-lanka-unveils-comprehensive-digital-economy-roadmap-2030', 
 'The government of Sri Lanka has formally unveiled an ambitious national digital economy strategy aimed at boosting tech exports, artificial intelligence research, and public sector digital transformation.', 
 'The government of Sri Lanka has formally unveiled an ambitious national digital economy strategy aimed at boosting tech exports, artificial intelligence research, and public sector digital transformation.', 
 '<p><strong>Colombo</strong> — In a landmark policy announcement today, Sri Lankan government officials and tech industry leaders unveiled a comprehensive roadmap to expand the nation\'s digital economy to over $15 billion by 2030.</p><p>The strategy focuses on expanding nationwide high-speed connectivity, establishing specialized tech parks in regional hubs like Kandy and Galle, and providing digital literacy training to over 500,000 students and professionals over the next five years.</p><p>Industry representatives praised the forward-looking vision, noting that rapid digital modernization is essential to attracting foreign direct investment and fostering homegrown technological innovation.</p>', 
 '<p><strong>Colombo</strong> — In a landmark policy announcement today, Sri Lankan government officials and tech industry leaders unveiled a comprehensive roadmap to expand the nation\'s digital economy to over $15 billion by 2030.</p><p>The strategy focuses on expanding nationwide high-speed connectivity, establishing specialized tech parks in regional hubs like Kandy and Galle, and providing digital literacy training to over 500,000 students and professionals over the next five years.</p><p>Industry representatives praised the forward-looking vision, noting that rapid digital modernization is essential to attracting foreign direct investment and fostering homegrown technological innovation.</p>', 
 'published', 1, 1, 1850, NOW()),

(2, 1, 3, 
 'Central Bank Reports Record Foreign Exchange Reserves and Strengthening Rupee', 
 'Central Bank Reports Record Foreign Exchange Reserves and Strengthening Rupee', 
 'central-bank-reports-record-foreign-exchange-reserves-and-strengthening-rupee', 
 'Official gross reserves have climbed to multi-year highs bolstered by sustained tourist arrivals, export revenues, and strong worker remittances.', 
 'Official gross reserves have climbed to multi-year highs bolstered by sustained tourist arrivals, export revenues, and strong worker remittances.', 
 '<p>The Central Bank of Sri Lanka released its latest macroeconomic indicators today, showing that gross official foreign exchange reserves have strengthened considerably across the third quarter.</p><p>Increased tourist arrivals, paired with record high remittance inflows from overseas Sri Lankan workers, provided vital balance-of-payments support. Financial analysts observe that consistent policy stability has boosted confidence in the Colombo Stock Exchange and sovereign debt markets.</p>', 
 '<p>The Central Bank of Sri Lanka released its latest macroeconomic indicators today, showing that gross official foreign exchange reserves have strengthened considerably across the third quarter.</p><p>Increased tourist arrivals, paired with record high remittance inflows from overseas Sri Lankan workers, provided vital balance-of-payments support. Financial analysts observe that consistent policy stability has boosted confidence in the Colombo Stock Exchange and sovereign debt markets.</p>', 
 'published', 0, 1, 1420, NOW()),

(3, 1, 4, 
 'Sri Lanka Cricket Unveils Dynamic 15-Man Squad for Upcoming Regional Championship', 
 'Sri Lanka Cricket Unveils Dynamic 15-Man Squad for Upcoming Regional Championship', 
 'sri-lanka-cricket-unveils-dynamic-15-man-squad-for-upcoming-regional-championship', 
 'National selectors have finalized an aggressive lineup blending seasoned international stars with breakout domestic talents for the major continental tournament.', 
 'National selectors have finalized an aggressive lineup blending seasoned international stars with breakout domestic talents for the major continental tournament.', 
 '<p>Sri Lanka Cricket (SLC) selection committee announced the official 15-man squad this morning following a rigorous high-performance training camp at the R. Premadasa International Cricket Stadium.</p><p>Head coach and team management emphasized batting depth and spin variety as crucial strengths for the subcontinental conditions. The squad will depart next week for a two-match warm-up series before the main tournament kicks off.</p>', 
 '<p>Sri Lanka Cricket (SLC) selection committee announced the official 15-man squad this morning following a rigorous high-performance training camp at the R. Premadasa International Cricket Stadium.</p><p>Head coach and team management emphasized batting depth and spin variety as crucial strengths for the subcontinental conditions. The squad will depart next week for a two-match warm-up series before the main tournament kicks off.</p>', 
 'published', 1, 0, 2340, NOW()),

(4, 1, 6, 
 'Colombo Port City Attracts Major Global Tech Giants for South Asian Operations', 
 'Colombo Port City Attracts Major Global Tech Giants for South Asian Operations', 
 'colombo-port-city-attracts-major-global-tech-giants-for-south-asian-operations', 
 'Over twenty international technology enterprises and financial institutions have finalized agreements to establish regional development hubs in Colombo.', 
 'Over twenty international technology enterprises and financial institutions have finalized agreements to establish regional development hubs in Colombo.', 
 '<p>Commercial activity at the Colombo Port City special economic zone reached a significant milestone this week as several Fortune 500 tech companies and investment funds signed official lease agreements.</p><p>The cutting-edge commercial district provides duty-free incentives, modern green architecture, and world-class telecommunications infrastructure, positioning Sri Lanka as a vital maritime and technological corridor in South Asia.</p>', 
 '<p>Commercial activity at the Colombo Port City special economic zone reached a significant milestone this week as several Fortune 500 tech companies and investment funds signed official lease agreements.</p><p>The cutting-edge commercial district provides duty-free incentives, modern green architecture, and world-class telecommunications infrastructure, positioning Sri Lanka as a vital maritime and technological corridor in South Asia.</p>', 
 'published', 0, 1, 980, NOW()),

(5, 1, 2, 
 'Parliament Passes Landmark Clean Energy and Environmental Sustainability Bill', 
 'Parliament Passes Landmark Clean Energy and Environmental Sustainability Bill', 
 'parliament-passes-landmark-clean-energy-and-environmental-sustainability-bill', 
 'Lawmakers cross party lines to enact legislation accelerating wind and solar infrastructure projects across Northern and Eastern provinces.', 
 'Lawmakers cross party lines to enact legislation accelerating wind and solar infrastructure projects across Northern and Eastern provinces.', 
 '<p>In a historic parliamentary session, lawmakers overwhelmingly approved the National Clean Energy Act, mandating that 70% of Sri Lanka\'s total electricity generation come from renewable sources by 2030.</p><p>The bill introduces fast-track licensing for rooftop solar installations, tax credits for electric vehicle battery manufacturing, and strict conservation standards for coastal marine ecosystems.</p>', 
 '<p>In a historic parliamentary session, lawmakers overwhelmingly approved the National Clean Energy Act, mandating that 70% of Sri Lanka\'s total electricity generation come from renewable sources by 2030.</p><p>The bill introduces fast-track licensing for rooftop solar installations, tax credits for electric vehicle battery manufacturing, and strict conservation standards for coastal marine ecosystems.</p>', 
 'published', 0, 0, 750, NOW()),

(6, 1, 5, 
 'Global Climate Summit Reaches Landmark Multi-Billion Dollar Adaptation Agreement', 
 'Global Climate Summit Reaches Landmark Multi-Billion Dollar Adaptation Agreement', 
 'global-climate-summit-reaches-landmark-multi-billion-dollar-adaptation-agreement', 
 'World leaders in Geneva agree on an emergency resilience fund to support island nations vulnerable to rising sea levels and extreme weather patterns.', 
 'World leaders in Geneva agree on an emergency resilience fund to support island nations vulnerable to rising sea levels and extreme weather patterns.', 
 '<p>Delegates from over 190 nations concluded emergency negotiations in Geneva today with the formal ratification of a new $100 billion Global Climate Adaptation Facility.</p><p>The fund will disburse direct grants to developing coastal economies and island states to build sea defenses, modernize agriculture, and deploy decentralized solar grids.</p>', 
 '<p>Delegates from over 190 nations concluded emergency negotiations in Geneva today with the formal ratification of a new $100 billion Global Climate Adaptation Facility.</p><p>The fund will disburse direct grants to developing coastal economies and island states to build sea defenses, modernize agriculture, and deploy decentralized solar grids.</p>', 
 'published', 0, 0, 610, NOW()),

(7, 1, 7, 
 'Galle International Film Festival Celebrates Cinematic Excellence and Cultural Heritage', 
 'Galle International Film Festival Celebrates Cinematic Excellence and Cultural Heritage', 
 'galle-international-film-festival-celebrates-cinematic-excellence-and-cultural-heritage', 
 'The historic Galle Fort hosts world-renowned directors, producers, and actors for a week-long celebration of South Asian independent cinema.', 
 'The historic Galle Fort hosts world-renowned directors, producers, and actors for a week-long celebration of South Asian independent cinema.', 
 '<p>The iconic ramparts of the Galle Fort were illuminated last evening for the grand opening gala of the 2026 Galle International Film Festival.</p><p>Over 80 feature films, documentaries, and short animations from 25 countries are scheduled for screening across historic venues, with workshops conducted by award-winning international cinematographers.</p>', 
 '<p>The iconic ramparts of the Galle Fort were illuminated last evening for the grand opening gala of the 2026 Galle International Film Festival.</p><p>Over 80 feature films, documentaries, and short animations from 25 countries are scheduled for screening across historic venues, with workshops conducted by award-winning international cinematographers.</p>', 
 'published', 0, 0, 890, NOW()),

(8, 1, 1, 
 'Sri Lanka Tourism Exceeds 2 Million Annual Arrivals Ahead of Projected Targets', 
 'Sri Lanka Tourism Exceeds 2 Million Annual Arrivals Ahead of Projected Targets', 
 'sri-lanka-tourism-exceeds-2-million-annual-arrivals-ahead-of-projected-targets', 
 'Spurred by eco-tourism, culinary tours, and luxury beach resorts, visitor arrivals surge across the southern and central highlands.', 
 'Sri Lanka Tourism Development Authority (SLTDA) confirmed today that tourist arrivals have officially crossed the 2 million milestone earlier than anticipated.', 
 '<p>Sri Lanka Tourism Development Authority (SLTDA) confirmed today that tourist arrivals have officially crossed the 2 million milestone earlier than anticipated.</p><p>Travel industry stakeholders highlighted increased flight frequencies from Europe, the Middle East, and East Asia as primary drivers. Key tourist hubs including Ella, Mirissa, Sigiriya, and Nuwara Eliya reported record occupancy levels.</p>', 
 '<p>Sri Lanka Tourism Development Authority (SLTDA) confirmed today that tourist arrivals have officially crossed the 2 million milestone earlier than anticipated.</p><p>Travel industry stakeholders highlighted increased flight frequencies from Europe, the Middle East, and East Asia as primary drivers. Key tourist hubs including Ella, Mirissa, Sigiriya, and Nuwara Eliya reported record occupancy levels.</p>', 
 'published', 1, 0, 1670, NOW()),

(9, 1, 6, 
 'Sri Lankan Startup Secures $10M Series A to Expand AI Healthcare Diagnostics Across Asia', 
 'Sri Lankan Startup Secures $10M Series A to Expand AI Healthcare Diagnostics Across Asia', 
 'sri-lankan-startup-secures-10m-series-a-to-expand-ai-healthcare-diagnostics-across-asia', 
 'A biomedical engineering venture founded in Colombo develops cutting-edge machine learning models to detect cardiac anomalies in rural clinics.', 
 'A biomedical engineering venture founded in Colombo develops cutting-edge machine learning models to detect cardiac anomalies in rural clinics.', 
 '<p>Colombo-based healthtech startup MedAI announced a successful $10 million Series A funding round led by prominent regional venture capital firms today.</p><p>The company\'s proprietary deep-learning software enables low-cost, portable diagnostic screening for cardiovascular risks, with trials already underway in over fifty public hospitals across Sri Lanka and Southeast Asia.</p>', 
 '<p>Colombo-based healthtech startup MedAI announced a successful $10 million Series A funding round led by prominent regional venture capital firms today.</p><p>The company\'s proprietary deep-learning software enables low-cost, portable diagnostic screening for cardiovascular risks, with trials already underway in over fifty public hospitals across Sri Lanka and Southeast Asia.</p>', 
 'published', 0, 0, 480, NOW())
ON DUPLICATE KEY UPDATE 
  `title_en` = VALUES(`title_en`), 
  `title_si` = VALUES(`title_si`), 
  `summary_en` = VALUES(`summary_en`), 
  `summary_si` = VALUES(`summary_si`),
  `content_en` = VALUES(`content_en`),
  `content_si` = VALUES(`content_si`),
  `views` = VALUES(`views`),
  `is_breaking` = VALUES(`is_breaking`),
  `is_featured` = VALUES(`is_featured`);

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Lanka News Hub'),
('site_description', 'Sri Lanka\'s Premier Digital News Source'),
('primary_language', 'en'),
('contact_email', 'editor@liontech.lk')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

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
