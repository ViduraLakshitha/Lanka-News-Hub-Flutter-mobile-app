import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'core/theme/app_theme.dart';
import 'core/storage/storage_service.dart';
import 'core/constants/api_constants.dart';
import 'core/localization/app_localizations.dart';
import 'core/crash/crash_service.dart';
import 'core/notifications/notification_service.dart';
import 'core/analytics/analytics_service.dart';

import 'providers/theme_provider.dart';
import 'providers/font_size_provider.dart';
import 'providers/language_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/news_provider.dart';
import 'providers/bookmark_provider.dart';
import 'providers/classified_provider.dart';
import 'providers/notification_provider.dart';

import 'screens/splash/splash_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // 1. Crash Monitoring Initialization
  CrashService.init();

  // 2. Local Persistent Storage Initialization
  await StorageService.init();
  ApiConstants.initBaseUrl();

  // 3. Push Notifications Initialization
  await NotificationService.init();

  // 4. Analytics Initialization
  await AnalyticsService.logScreenView('AppLaunch');

  runApp(const LankaNewsApp());
}

class LankaNewsApp extends StatelessWidget {
  const LankaNewsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProvider(create: (_) => FontSizeProvider()),
        ChangeNotifierProvider(create: (_) => LanguageProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => NewsProvider()),
        ChangeNotifierProvider(create: (_) => BookmarkProvider()),
        ChangeNotifierProvider(create: (_) => ClassifiedProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
      ],
      child: const _LankaNewsAppView(),
    );
  }
}

class _LankaNewsAppView extends StatelessWidget {
  const _LankaNewsAppView();

  @override
  Widget build(BuildContext context) {
    final themeProvider = context.watch<ThemeProvider>();
    final fontProvider = context.watch<FontSizeProvider>();
    final langProvider = context.watch<LanguageProvider>();

    return MaterialApp(
      title: 'Lanka News Hub',
      debugShowCheckedModeBanner: false,

      // Themes
      theme: AppTheme.lightTheme(fontProvider.fontScale),
      darkTheme: AppTheme.darkTheme(fontProvider.fontScale),
      themeMode: themeProvider.themeMode,

      // Multi-Language Support (Sinhala, English, Tamil)
      locale: langProvider.locale,
      supportedLocales: AppLocalizations.supportedLocales,
      localizationsDelegates: const [
        AppLocalizationsDelegate(),
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],

      home: const SplashScreen(),
    );
  }
}
