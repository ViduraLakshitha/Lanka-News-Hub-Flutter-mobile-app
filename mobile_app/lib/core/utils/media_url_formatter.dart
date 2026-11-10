class MediaUrlFormatter {
  static const String defaultHost = 'https://news.liontech.lk';

  /// Normalizes and cleans all media/image URLs across the app.
  /// - Fixes double prepended `/uploads/assets/` -> `/assets/`
  /// - Resolves relative URLs to absolute HTTPS URLs
  /// - Migrates old localhost/127.0.0.1/emulator IPs to the live domain
  static String? format(String? rawUrl) {
    if (rawUrl == null) return null;
    var url = rawUrl.trim();
    if (url.isEmpty || url == 'null') return null;

    // 1. Fix double prepended /uploads/assets/ -> /assets/
    if (url.contains('/uploads/assets/')) {
      url = url.replaceAll('/uploads/assets/', '/assets/');
    } else if (url.startsWith('uploads/assets/')) {
      url = url.replaceFirst('uploads/assets/', 'assets/');
    } else if (url.contains('/uploads/public/assets/')) {
      url = url.replaceAll('/uploads/public/assets/', '/assets/');
    }

    // 2. Fix local IPs or emulator hostnames to live production domain
    if (url.contains('localhost') ||
        url.contains('127.0.0.1') ||
        url.contains('10.0.2.2') ||
        url.contains('192.168.')) {
      final uri = Uri.tryParse(url);
      if (uri != null) {
        url = '$defaultHost${uri.path}';
      }
    }

    // 3. If relative URL, prepend live domain
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      if (url.startsWith('/')) {
        url = '$defaultHost$url';
      } else {
        url = '$defaultHost/$url';
      }
    }

    // 4. Force HTTPS for iOS App Transport Security (ATS)
    if (url.startsWith('http://news.liontech.lk')) {
      url = url.replaceFirst('http://', 'https://');
    }

    return url;
  }
}
