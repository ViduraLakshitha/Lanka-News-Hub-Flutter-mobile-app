# Lanka News Hub — Mobile App Deployment & Store Guide
**Cross-Platform Flutter Mobile Application (Android & iOS)**

---

## 1. Overview & Architecture

The **Lanka News Hub** mobile application is built using Flutter and Dart with clean provider architecture. It seamlessly integrates with the Lanka News Hub backend REST API (`/api/v1/...`).

### Feature Highlights Implemented
1. **News Features:**
   - Real-time Latest News Feed with infinite scroll pagination and pull-to-refresh.
   - Breaking News Alert ticker banner with live pulsing animation and direct story routing.
   - Trilingual Category Browsing (Local, Politics, Business, Sports, Entertainment, Technology, International).
   - Video News Integration with interactive player card and external video launch.
   - Photo Galleries with multi-image pinch-to-zoom full-screen viewer.
   - Live Updates timeline blog with auto-refresh and timestamps.
   - Comprehensive Search with instant keyword matching, recent searches history chips, and trending topics.
   - Offline Reading: full article caching enabling offline reading when connectivity drops.
   - Bookmarks & saved articles locally and synchronized with user account.
   - Native Social Media Sharing (WhatsApp, Facebook, X/Twitter, Telegram, copy link).
   - Dynamic Font Size Adjustment (Small, Normal, Large, Extra Large) with live preview.
   - Dark Mode & Light Mode support with system auto-detect and manual toggle.

2. **Classified Advertisement Marketplace:**
   - Member Registration, Sign In, and Secure Authentication.
   - Multi-image ad submissions (up to 8 high-resolution photos).
   - Edit, renew, and withdraw listings.
   - 1-Tap Advertiser Contact: Direct Telephone Call, WhatsApp chat with pre-filled message, and Email.
   - Category browsing, Location filters, and Price Range filtering (Min/Max LKR).
   - User Advertisement Dashboard with Active, Pending Review, and Expired tabs.
   - Featured / Premium ad boost option with gold badges.
   - Favorite / saved classified listings.

3. **Push Notifications:**
   - Breaking News push alerts.
   - Category-based subscription alerts.
   - Advertisement moderation and approval alerts.
   - Promotional announcements.
   - Granular Notification Preferences management in settings.

4. **Trilingual Multi-Language Support:**
   - Full localized translations in **Sinhala (සිංහල)**, **English**, and **Tamil (தமிழ்)** across all screens, buttons, categories, and alerts.

5. **API Optimization & Fast Loading:**
   - In-memory conditional caching.
   - Smooth skeleton shimmer loading indicators.
   - Offline fallback store.

---

## 2. API Configuration

The API connection is defined in `lib/core/constants/api_constants.dart`:

```dart
// Production URL (Default)
static const String productionBaseUrl = 'https://news.liontech.lk/api/v1';

// For local Android emulator testing: http://10.0.2.2:8080/api/v1
// For local iOS simulator testing: http://127.0.0.1:8080/api/v1
```

---

## 3. Google Play Store Deployment (Android)

### Package Identifier
- **Package Name:** `com.lankanewshub.app`
- **Minimum SDK:** 21 (Android 5.0 Lollipop)
- **Target SDK:** 34 (Android 14)
- **Compile SDK:** 34

### Generating a Release Keystore
Generate a secure upload keystore in your terminal:

```sh
keytool -genkey -v -keystore android/app/upload-keystore.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```

Create `android/key.properties` (do not commit to public repositories):
```properties
storePassword=your_store_password
keyPassword=your_key_password
keyAlias=upload
storeFile=../upload-keystore.jks
```

### Building the Release App Bundle (.aab)
Run from the `mobile_app` folder:

```sh
flutter build appbundle --release
```

Output file:
`mobile_app/build/app/outputs/bundle/release/app-release.aab`

### Building Standalone Release APK (.apk)
```sh
flutter build apk --release
```

Output file:
`mobile_app/build/app/outputs/flutter-apk/app-release.apk`

---

## 4. Apple App Store Deployment (iOS)

### Bundle Identifier
- **Bundle ID:** `com.lankanewshub.app`
- **iOS Deployment Target:** iOS 12.0+

### Permissions Configured in Info.plist:
- `NSPhotoLibraryUsageDescription`: Required for uploading images for classified listings.
- `NSCameraUsageDescription`: Required for taking photos of items directly.
- `LSApplicationQueriesSchemes`: Configured for `whatsapp`, `tel`, and `mailto`.

### Building iOS Archive (.ipa)
1. In `mobile_app/ios`, run:
   ```sh
   pod install
   ```
2. Build iOS release bundle:
   ```sh
   flutter build ipa --release
   ```
3. Upload to App Store Connect via Xcode Organizer or Transporter app.

---

## 5. Firebase Cloud Messaging (FCM) Integration

For live push notification delivery across devices:
1. Create a Firebase project in the [Firebase Console](https://console.firebase.google.com/).
2. Add an Android app with package `com.lankanewshub.app` and download `google-services.json` into `android/app/`.
3. Add an iOS app with bundle ID `com.lankanewshub.app` and download `GoogleService-Info.plist` into `ios/Runner/`.
4. The app's `NotificationService` automatically registers device tokens with the backend API `/api/v1/notifications/device-token`.

