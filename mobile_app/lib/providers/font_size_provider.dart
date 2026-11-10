import 'package:flutter/material.dart';
import '../core/storage/storage_service.dart';

enum FontSizePreset { small, normal, large, extraLarge }

class FontSizeProvider extends ChangeNotifier {
  double _fontScale = 1.0;

  double get fontScale => _fontScale;

  FontSizePreset get currentPreset {
    if (_fontScale <= 0.9) return FontSizePreset.small;
    if (_fontScale <= 1.05) return FontSizePreset.normal;
    if (_fontScale <= 1.2) return FontSizePreset.large;
    return FontSizePreset.extraLarge;
  }

  FontSizeProvider() {
    _loadFontScale();
  }

  void _loadFontScale() {
    _fontScale = StorageService.getFontScale();
    notifyListeners();
  }

  Future<void> setFontScale(double scale) async {
    _fontScale = scale.clamp(0.85, 1.4);
    await StorageService.saveFontScale(_fontScale);
    notifyListeners();
  }

  Future<void> setPreset(FontSizePreset preset) async {
    final scale = switch (preset) {
      FontSizePreset.small => 0.85,
      FontSizePreset.normal => 1.0,
      FontSizePreset.large => 1.15,
      FontSizePreset.extraLarge => 1.30,
    };
    await setFontScale(scale);
  }
}

