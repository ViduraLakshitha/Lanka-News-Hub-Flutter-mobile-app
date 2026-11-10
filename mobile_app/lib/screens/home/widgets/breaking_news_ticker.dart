import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../models/article_model.dart';
import '../../../core/constants/app_colors.dart';
import '../../../providers/language_provider.dart';
import '../../news/article_detail_screen.dart';

class BreakingNewsTicker extends StatefulWidget {
  final List<ArticleModel> breakingArticles;

  const BreakingNewsTicker({
    super.key,
    required this.breakingArticles,
  });

  @override
  State<BreakingNewsTicker> createState() => _BreakingNewsTickerState();
}

class _BreakingNewsTickerState extends State<BreakingNewsTicker> {
  late PageController _pageController;
  int _currentIndex = 0;
  Timer? _scrollTimer;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    _startTimer();
  }

  void _startTimer() {
    _scrollTimer?.cancel();
    _scrollTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (!mounted || widget.breakingArticles.length <= 1) return;
      if (!_pageController.hasClients) return;
      final nextIndex = (_currentIndex + 1) % widget.breakingArticles.length;
      _pageController.animateToPage(
        nextIndex,
        duration: const Duration(milliseconds: 500),
        curve: Curves.easeInOut,
      );
      setState(() {
        _currentIndex = nextIndex;
      });
    });
  }

  @override
  void dispose() {
    _scrollTimer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.breakingArticles.isEmpty) return const SizedBox.shrink();

    final langCode = context.watch<LanguageProvider>().languageCode;

    return Container(
      height: 42,
      margin: const EdgeInsets.fromLTRB(16, 10, 16, 4),
      decoration: BoxDecoration(
        color: AppColors.breakingRed.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.breakingRed.withValues(alpha: 0.25), width: 1),
      ),
      child: Row(
        children: [
          // Pulse Badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: const BoxDecoration(
              color: AppColors.breakingRed,
              borderRadius: BorderRadius.horizontal(left: Radius.circular(7)),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.flash_on_rounded, size: 14, color: Colors.white),
                SizedBox(width: 4),
                Text(
                  'BREAKING',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),

          // Ticker text
          Expanded(
            child: PageView.builder(
              controller: _pageController,
              itemCount: widget.breakingArticles.length,
              physics: const NeverScrollableScrollPhysics(),
              itemBuilder: (context, index) {
                final article = widget.breakingArticles[index];
                final title = article.getLocalizedTitle(langCode);

                return InkWell(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ArticleDetailScreen(article: article),
                      ),
                    );
                  },
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: AppColors.breakingRed,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),
    );
  }
}
