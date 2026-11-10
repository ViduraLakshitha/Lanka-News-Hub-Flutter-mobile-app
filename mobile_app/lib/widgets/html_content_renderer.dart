import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';

class HtmlContentRenderer extends StatelessWidget {
  final String html;
  final double fontScale;
  final bool isDark;

  const HtmlContentRenderer({
    super.key,
    required this.html,
    this.fontScale = 1.0,
    this.isDark = false,
  });

  @override
  Widget build(BuildContext context) {
    final blocks = _parseHtmlBlocks(html);

    if (blocks.isEmpty) {
      return Text(
        _cleanText(html),
        style: TextStyle(
          fontSize: 16 * fontScale,
          height: 1.65,
          letterSpacing: 0.2,
          color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: blocks.map((block) => _buildBlock(block)).toList(),
    );
  }

  Widget _buildBlock(_ContentBlock block) {
    switch (block.type) {
      case _BlockType.heading:
        return Padding(
          padding: const EdgeInsets.only(top: 18, bottom: 8),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                width: 4,
                height: 18 * fontScale,
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  block.text,
                  style: TextStyle(
                    fontSize: 18 * fontScale,
                    fontWeight: FontWeight.bold,
                    fontFamily: 'serif',
                    color: isDark ? Colors.white : Colors.black87,
                  ),
                ),
              ),
            ],
          ),
        );

      case _BlockType.blockquote:
        return Container(
          margin: const EdgeInsets.symmetric(vertical: 12),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkSurface : const Color(0xFFF9F9FB),
            borderRadius: BorderRadius.circular(8),
            border: Border(
              left: BorderSide(color: AppColors.primary, width: 3.5),
            ),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(
                Icons.format_quote_rounded,
                size: 20,
                color: AppColors.primary.withValues(alpha: 0.7),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  block.text,
                  style: TextStyle(
                    fontSize: 15 * fontScale,
                    fontStyle: FontStyle.italic,
                    height: 1.55,
                    color: isDark ? Colors.white70 : Colors.black87,
                  ),
                ),
              ),
            ],
          ),
        );

      case _BlockType.paragraph:
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: Text(
            block.text,
            style: TextStyle(
              fontSize: 16 * fontScale,
              height: 1.65,
              letterSpacing: 0.25,
              color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
            ),
          ),
        );
    }
  }

  static String _cleanText(String input) {
    var out = input;
    // Decode HTML entities
    out = out
        .replaceAll('&quot;', '"')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&#039;', "'")
        .replaceAll('&#39;', "'")
        .replaceAll('&mdash;', '—')
        .replaceAll('&ndash;', '–')
        .replaceAll('&hellip;', '...');

    // Strip remaining HTML tags
    out = out.replaceAll(RegExp(r'<[^>]*>'), '');
    return out.trim();
  }

  List<_ContentBlock> _parseHtmlBlocks(String raw) {
    if (!raw.contains('<') && !raw.contains('>')) {
      final clean = _cleanText(raw);
      return clean.isNotEmpty ? [_ContentBlock(_BlockType.paragraph, clean)] : [];
    }

    final blocks = <_ContentBlock>[];
    final regex = RegExp(
      r'<(p|h1|h2|h3|h4|blockquote|li)[^>]*>(.*?)</\1>',
      caseSensitive: false,
      dotAll: true,
    );

    final matches = regex.allMatches(raw);
    for (final m in matches) {
      final tag = m.group(1)!.toLowerCase();
      final body = _cleanText(m.group(2) ?? '');
      if (body.isEmpty) continue;

      if (tag.startsWith('h')) {
        blocks.add(_ContentBlock(_BlockType.heading, body));
      } else if (tag == 'blockquote') {
        blocks.add(_ContentBlock(_BlockType.blockquote, body));
      } else {
        blocks.add(_ContentBlock(_BlockType.paragraph, body));
      }
    }

    if (blocks.isEmpty) {
      final clean = _cleanText(raw);
      if (clean.isNotEmpty) {
        blocks.add(_ContentBlock(_BlockType.paragraph, clean));
      }
    }

    return blocks;
  }
}

enum _BlockType { paragraph, heading, blockquote }

class _ContentBlock {
  final _BlockType type;
  final String text;

  _ContentBlock(this.type, this.text);
}

