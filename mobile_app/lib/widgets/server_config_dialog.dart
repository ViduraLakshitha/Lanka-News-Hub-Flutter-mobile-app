import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:provider/provider.dart';

import '../core/constants/api_constants.dart';
import '../core/constants/app_colors.dart';
import '../providers/news_provider.dart';
import '../providers/classified_provider.dart';

class ServerConfigDialog extends StatefulWidget {
  const ServerConfigDialog({super.key});

  static Future<void> show(BuildContext context) {
    return showDialog(
      context: context,
      builder: (_) => const ServerConfigDialog(),
    );
  }

  @override
  State<ServerConfigDialog> createState() => _ServerConfigDialogState();
}

class _ServerConfigDialogState extends State<ServerConfigDialog> {
  late final TextEditingController _urlController;
  bool _isTesting = false;
  String? _testResult;
  bool _testSuccess = false;

  @override
  void initState() {
    super.initState();
    _urlController = TextEditingController(text: ApiConstants.activeBaseUrl);
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _testConnection() async {
    final rawUrl = _urlController.text.trim().replaceAll(RegExp(r'/+$'), '');
    if (rawUrl.isEmpty) return;

    setState(() {
      _isTesting = true;
      _testResult = null;
    });

    try {
      final uri = Uri.parse('$rawUrl/bootstrap');
      final res = await http.get(uri).timeout(const Duration(seconds: 5));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final cats = (body['data']?['categories'] as List?)?.length ?? 0;
        setState(() {
          _testSuccess = true;
          _testResult = 'Connected successfully! Found $cats categories in MySQL.';
        });
      } else {
        setState(() {
          _testSuccess = false;
          _testResult = 'Server responded with status: ${res.statusCode}';
        });
      }
    } catch (e) {
      setState(() {
        _testSuccess = false;
        _testResult = 'Connection failed: $e';
      });
    } finally {
      if (mounted) {
        setState(() {
          _isTesting = false;
        });
      }
    }
  }

  Future<void> _applyUrl(String url) async {
    final clean = url.trim().replaceAll(RegExp(r'/+$'), '');
    await ApiConstants.setCustomBaseUrl(clean);
    if (!mounted) return;

    Navigator.pop(context);

    // Refresh feeds
    final news = Provider.of<NewsProvider>(context, listen: false);
    final classifieds = Provider.of<ClassifiedProvider>(context, listen: false);
    news.initApp();
    classifieds.fetchClassifieds(refresh: true);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('API URL updated: $clean'),
        backgroundColor: Colors.green,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Row(
        children: [
          Icon(Icons.dns_rounded, color: AppColors.primary),
          SizedBox(width: 10),
          Text('Server Connection', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        ],
      ),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Select a preset or enter your computer\'s IP to connect to the MySQL database:',
              style: TextStyle(fontSize: 13, color: Colors.grey),
            ),
            const SizedBox(height: 12),

            // Presets
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                ActionChip(
                  label: const Text('Live cPanel (news.liontech.lk)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                  backgroundColor: AppColors.primary.withValues(alpha: 0.12),
                  onPressed: () {
                    _urlController.text = 'https://news.liontech.lk/api/v1';
                    _testConnection();
                  },
                ),
                ActionChip(
                  label: const Text('Wi-Fi PC (172.20.10.5)', style: TextStyle(fontSize: 12)),
                  onPressed: () {
                    _urlController.text = 'http://172.20.10.5:8080/api/v1';
                    _testConnection();
                  },
                ),
                ActionChip(
                  label: const Text('Emulator (10.0.2.2)', style: TextStyle(fontSize: 12)),
                  onPressed: () {
                    _urlController.text = 'http://10.0.2.2:8080/api/v1';
                    _testConnection();
                  },
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Input field
            TextField(
              controller: _urlController,
              decoration: InputDecoration(
                labelText: 'API Base URL',
                hintText: 'http://172.20.10.5:8080/api/v1',
                border: const OutlineInputBorder(),
                isDense: true,
                suffixIcon: IconButton(
                  icon: const Icon(Icons.clear, size: 18),
                  onPressed: () => _urlController.clear(),
                ),
              ),
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 10),

            // Test button
            Row(
              children: [
                OutlinedButton.icon(
                  onPressed: _isTesting ? null : _testConnection,
                  icon: _isTesting
                      ? const SizedBox(
                          width: 14,
                          height: 14,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.network_check_rounded, size: 16),
                  label: Text(_isTesting ? 'Testing...' : 'Test Connection'),
                ),
              ],
            ),

            if (_testResult != null) ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: _testSuccess ? Colors.green.withOpacity(0.12) : Colors.red.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      _testSuccess ? Icons.check_circle : Icons.error,
                      color: _testSuccess ? Colors.green : Colors.red,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _testResult!,
                        style: TextStyle(
                          color: _testSuccess ? Colors.green.shade800 : Colors.red.shade800,
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () => _applyUrl(_urlController.text),
          child: const Text('Save & Connect'),
        ),
      ],
    );
  }
}

