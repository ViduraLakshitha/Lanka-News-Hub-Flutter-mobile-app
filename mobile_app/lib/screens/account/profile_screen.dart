import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/language_provider.dart';
import '../../providers/theme_provider.dart';
import '../../providers/font_size_provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/localization/app_localizations.dart';
import 'login_screen.dart';
import '../classifieds/my_ads_screen.dart';
import '../classifieds/favorite_ads_screen.dart';
import '../news/offline_articles_screen.dart';
import '../notifications/notification_settings_screen.dart';
import '../../widgets/server_config_dialog.dart';
import '../../core/constants/api_constants.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final langProvider = context.watch<LanguageProvider>();
    final themeProvider = context.watch<ThemeProvider>();
    final fontProvider = context.watch<FontSizeProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('nav_account')),
      ),
      body: ListView(
        children: [
          // Header Card
          Container(
            padding: const EdgeInsets.all(20),
            color: isDark ? AppColors.darkCard : Colors.white,
            child: auth.isAuthenticated
                ? Row(
                    children: [
                      CircleAvatar(
                        radius: 30,
                        backgroundColor: AppColors.primary,
                        child: Text(
                          auth.user!.name.isNotEmpty ? auth.user!.name[0].toUpperCase() : 'U',
                          style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              auth.user!.name,
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              auth.user!.email,
                              style: const TextStyle(fontSize: 13, color: Colors.grey),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.primary.withOpacity(0.12),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                auth.user!.role.toUpperCase(),
                                style: const TextStyle(
                                  color: AppColors.primary,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  )
                : Column(
                    children: [
                      const CircleAvatar(
                        radius: 30,
                        backgroundColor: Colors.grey,
                        child: Icon(Icons.person, size: 36, color: Colors.white),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        context.tr('welcome_back'),
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        context.tr('sign_in_required'),
                        style: const TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const LoginScreen()),
                          );
                        },
                        style: ElevatedButton.styleFrom(
                          minimumSize: const Size(200, 42),
                        ),
                        child: Text(context.tr('sign_in')),
                      ),
                    ],
                  ),
          ),
          const SizedBox(height: 12),

          // User Activity Sections
          if (auth.isAuthenticated) ...[
            _buildSectionHeader('MARKETPLACE & SAVED'),
            _buildTile(
              context,
              icon: Icons.dashboard_outlined,
              title: context.tr('my_ads'),
              subtitle: 'Manage, renew, and edit your listings',
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const MyAdsScreen()),
                );
              },
            ),
            _buildTile(
              context,
              icon: Icons.favorite_border,
              title: context.tr('saved_ads'),
              subtitle: 'Classified advertisements you saved',
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const FavoriteAdsScreen()),
                );
              },
            ),
          ],

          _buildTile(
            context,
            icon: Icons.offline_pin_outlined,
            title: context.tr('offline_articles'),
            subtitle: 'Saved news articles for offline reading',
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const OfflineArticlesScreen()),
              );
            },
          ),
          const SizedBox(height: 12),

          // App Settings Section
          _buildSectionHeader('PREFERENCES & SETTINGS'),

          // Language Switcher (Sinhala, English, Tamil)
          _buildTile(
            context,
            icon: Icons.language,
            title: context.tr('language'),
            subtitle: langProvider.languageName,
            trailing: const Icon(Icons.arrow_forward_ios, size: 14),
            onTap: () => _showLanguageDialog(context, langProvider),
          ),

          // Dark Mode Switcher
          SwitchListTile(
            secondary: Icon(
              themeProvider.isDarkMode ? Icons.dark_mode : Icons.light_mode,
              color: AppColors.primary,
            ),
            title: Text(context.tr('dark_mode')),
            subtitle: Text(
              themeProvider.themeMode == ThemeMode.system
                  ? context.tr('system_theme')
                  : (themeProvider.isDarkMode ? 'Dark theme active' : 'Light theme active'),
            ),
            value: themeProvider.isDarkMode,
            activeThumbColor: AppColors.primary,
            onChanged: (val) => themeProvider.toggleTheme(),
          ),

          // Font Size Adjuster
          _buildTile(
            context,
            icon: Icons.format_size_rounded,
            title: context.tr('font_size'),
            subtitle: 'Scale: ${(fontProvider.fontScale * 100).round()}%',
            trailing: const Icon(Icons.arrow_forward_ios, size: 14),
            onTap: () => _showFontSizeDialog(context, fontProvider),
          ),

          // Notification Preferences
          _buildTile(
            context,
            icon: Icons.notifications_active_outlined,
            title: context.tr('notification_settings'),
            subtitle: 'Breaking news, categories, ad approvals',
            trailing: const Icon(Icons.arrow_forward_ios, size: 14),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const NotificationSettingsScreen()),
              );
            },
          ),
          const SizedBox(height: 12),

          // About & Store Deployment Info
          _buildSectionHeader('APPLICATION & SERVER INFO'),
          _buildTile(
            context,
            icon: Icons.dns_rounded,
            title: 'Server Connection / API URL',
            subtitle: ApiConstants.activeBaseUrl,
            trailing: const Icon(Icons.arrow_forward_ios, size: 14),
            onTap: () => ServerConfigDialog.show(context),
          ),
          _buildTile(
            context,
            icon: Icons.info_outline,
            title: context.tr('app_version'),
            subtitle: '1.0.0 (Build 101) • Production Ready',
          ),
          _buildTile(
            context,
            icon: Icons.verified_user_outlined,
            title: context.tr('privacy_policy'),
            subtitle: 'Data protection and reader privacy',
          ),
          _buildTile(
            context,
            icon: Icons.description_outlined,
            title: context.tr('terms_conditions'),
            subtitle: 'Classified marketplace guidelines',
          ),

          // Sign out button
          if (auth.isAuthenticated) ...[
            const SizedBox(height: 20),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: OutlinedButton.icon(
                onPressed: () {
                  showDialog(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: Text(context.tr('sign_out')),
                      content: const Text('Are you sure you want to sign out of your account?'),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(ctx),
                          child: Text(context.tr('cancel')),
                        ),
                        ElevatedButton(
                          onPressed: () {
                            Navigator.pop(ctx);
                            auth.logout();
                          },
                          child: Text(context.tr('sign_out')),
                        ),
                      ],
                    ),
                  );
                },
                icon: const Icon(Icons.logout, color: Colors.red),
                label: Text(
                  context.tr('sign_out'),
                  style: const TextStyle(color: Colors.red),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: Colors.red),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ],
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 6),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          letterSpacing: 1,
          color: Colors.grey,
        ),
      ),
    );
  }

  Widget _buildTile(
    BuildContext context, {
    required IconData icon,
    required String title,
    String? subtitle,
    Widget? trailing,
    VoidCallback? onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppColors.primary),
      title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
      subtitle: subtitle != null ? Text(subtitle, style: const TextStyle(fontSize: 12)) : null,
      trailing: trailing,
      onTap: onTap,
    );
  }

  void _showLanguageDialog(BuildContext context, LanguageProvider provider) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(context.tr('language')),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildLangTile(ctx, provider, 'en', 'English (English)'),
            _buildLangTile(ctx, provider, 'si', 'සිංහල (Sinhala)'),
            _buildLangTile(ctx, provider, 'ta', 'தமிழ் (Tamil)'),
          ],
        ),
      ),
    );
  }

  Widget _buildLangTile(BuildContext ctx, LanguageProvider provider, String code, String label) {
    final isSelected = provider.languageCode == code;
    return ListTile(
      title: Text(
        label,
        style: TextStyle(
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? AppColors.primary : null,
        ),
      ),
      trailing: isSelected ? const Icon(Icons.check, color: AppColors.primary) : null,
      onTap: () {
        provider.setLanguage(code);
        Navigator.pop(ctx);
      },
    );
  }

  void _showFontSizeDialog(BuildContext context, FontSizeProvider provider) {
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            title: Text(context.tr('font_size')),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _buildPresetBtn(provider, FontSizePreset.small, 'Small', setDialogState),
                    _buildPresetBtn(provider, FontSizePreset.normal, 'Normal', setDialogState),
                    _buildPresetBtn(provider, FontSizePreset.large, 'Large', setDialogState),
                    _buildPresetBtn(provider, FontSizePreset.extraLarge, 'XL', setDialogState),
                  ],
                ),
                const SizedBox(height: 16),
                Slider(
                  value: provider.fontScale,
                  min: 0.85,
                  max: 1.35,
                  divisions: 10,
                  activeColor: AppColors.primary,
                  onChanged: (val) {
                    provider.setFontScale(val);
                    setDialogState(() {});
                  },
                ),
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.grey.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    'News article preview text.',
                    style: TextStyle(
                      fontSize: 14 * provider.fontScale,
                      fontFamily: 'serif',
                    ),
                  ),
                ),
              ],
            ),
            actions: [
              TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Done')),
            ],
          );
        },
      ),
    );
  }

  Widget _buildPresetBtn(
    FontSizeProvider provider,
    FontSizePreset preset,
    String label,
    StateSetter setDialogState,
  ) {
    final isSelected = provider.currentPreset == preset;
    return ChoiceChip(
      label: Text(label, style: const TextStyle(fontSize: 11)),
      selected: isSelected,
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : null,
        fontWeight: isSelected ? FontWeight.bold : null,
      ),
      onSelected: (_) {
        provider.setPreset(preset);
        setDialogState(() {});
      },
    );
  }
}

