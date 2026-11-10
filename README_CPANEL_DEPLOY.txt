Lanka News Hub - cPanel Deployment Instructions for Mobile App
================================================================

1. UPLOAD FILES TO CPANEL:
   Extract and upload the contents of this zip to your website directory on cPanel:
   - app/Controllers/ApiController.php  -> upload into your hosting app/Controllers/
   - app/routes.php                    -> upload into your hosting app/
   - public/index.php                  -> upload into your hosting public/ (or public_html/)

2. DATABASE UPDATE:
   - In cPanel, open phpMyAdmin.
   - Select your database: liontec2_news_DB
   - Click "Import" tab (or click "SQL" tab).
   - Import/Execute the attached database/cpanel_mobile_update.sql file.

3. VERIFY:
   In your browser, visit:
   https://news.liontech.lk/api/v1/news
   You will see the JSON response of all live news from your cPanel MySQL database!

4. MOBILE APP:
   Open Lanka News Hub mobile app on your phone.
   Tap the Server icon in the top right (or go to Account -> Server Connection).
   Select "Live Production" (https://news.liontech.lk/api/v1).
   Tap "Test Connection", then tap "Save & Connect".
   Your mobile app will now fetch and update directly from your live cPanel MySQL database anywhere in the world!

