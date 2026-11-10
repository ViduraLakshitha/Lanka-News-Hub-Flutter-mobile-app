import 'package:flutter/foundation.dart';

class CrashService {
  static final List<String> _breadcrumbs = [];
  static const int _maxBreadcrumbs = 30;

  static void init() {
    // Catch Flutter framework errors
    FlutterError.onError = (FlutterErrorDetails details) {
      FlutterError.presentError(details);
      recordError(details.exception, details.stack, reason: details.context?.toString());
    };

    // Catch asynchronous / platform errors
    PlatformDispatcher.instance.onError = (error, stack) {
      recordError(error, stack, reason: 'PlatformDispatcher error');
      return true;
    };

    addBreadcrumb('App initialized');
  }

  static void addBreadcrumb(String message) {
    final timestamp = DateTime.now().toIso8601String().substring(11, 19);
    final entry = '[$timestamp] $message';
    _breadcrumbs.add(entry);
    if (_breadcrumbs.length > _maxBreadcrumbs) {
      _breadcrumbs.removeAt(0);
    }
  }

  static void recordError(dynamic error, StackTrace? stack, {String? reason}) {
    debugPrint('🚨 [CrashMonitoring] Caught error: $error');
    if (reason != null) debugPrint('   Reason: $reason');
    if (stack != null) debugPrint('   Stack: ${stack.toString().split("\n").take(4).join("\n")}');
    debugPrint('   Breadcrumbs:\n   ${_breadcrumbs.takeLast(5).join("\n   ")}');

    // Integration point for FirebaseCrashlytics:
    // FirebaseCrashlytics.instance.recordError(error, stack, reason: reason);
  }

  static List<String> get breadcrumbs => List.unmodifiable(_breadcrumbs);
}

extension _TakeLast<T> on List<T> {
  Iterable<T> takeLast(int n) {
    if (length <= n) return this;
    return skip(length - n);
  }
}

