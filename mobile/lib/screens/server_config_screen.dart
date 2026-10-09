import 'package:flutter/material.dart';
import '../core/theme/app_colors.dart';
import '../providers/server_config_controller.dart';

class ServerConfigScreen extends StatefulWidget {
  final ServerConfigController configController;

  const ServerConfigScreen({super.key, required this.configController});

  static Future<void> showAsDialog(BuildContext context, ServerConfigController controller) {
    return showDialog(
      context: context,
      barrierDismissible: true,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        child: SizedBox(
          width: 580,
          child: ServerConfigScreen(configController: controller),
        ),
      ),
    );
  }

  @override
  State<ServerConfigScreen> createState() => _ServerConfigScreenState();
}

class _ServerConfigScreenState extends State<ServerConfigScreen> {
  late TextEditingController _urlController;
  final List<String> _presets = [
    'http://127.0.0.1:8000/api',
    'http://192.168.2.243:8000/api',
    'http://localhost:8000/api',
  ];

  @override
  void initState() {
    super.initState();
    _urlController = TextEditingController(text: widget.configController.serverUrl);
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  void _onSave() async {
    final url = _urlController.text.trim();
    if (url.isEmpty) return;

    await widget.configController.updateServerUrl(url);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Server address saved.'),
          backgroundColor: AppColors.emerald500,
        ),
      );
      Navigator.of(context).maybePop();
    }
  }

  void _onTest() async {
    final url = _urlController.text.trim();
    if (url.isEmpty) return;

    await widget.configController.testConnection(url);
  }

  @override
  Widget build(BuildContext context) {
    final controller = widget.configController;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.gray200),
        boxShadow: const [
          BoxShadow(
            color: Color(0x33000000),
            blurRadius: 24,
            offset: Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 20, 16, 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.indigo50,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.dns_rounded, color: AppColors.indigo600, size: 22),
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'LAN Server Configuration',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: AppColors.gray900,
                      ),
                    ),
                  ],
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: AppColors.gray500),
                  onPressed: () => Navigator.of(context).maybePop(),
                  splashRadius: 20,
                ),
              ],
            ),
          ),
          const Divider(height: 1),

          Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Server Base API URL',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.gray700,
                  ),
                ),
                const SizedBox(height: 6),
                TextField(
                  controller: _urlController,
                  decoration: const InputDecoration(
                    hintText: 'e.g. http://192.168.1.100:8000/api',
                    prefixIcon: Icon(Icons.link, color: AppColors.gray400),
                  ),
                  keyboardType: TextInputType.url,
                ),
                const SizedBox(height: 12),

                // Presets
                const Text(
                  'Quick Presets:',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.gray500),
                ),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: _presets.map((preset) {
                    final isSelected = _urlController.text == preset;
                    return InkWell(
                      onTap: () {
                        setState(() {
                          _urlController.text = preset;
                        });
                      },
                      borderRadius: BorderRadius.circular(6),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: isSelected ? AppColors.indigo50 : AppColors.gray100,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: isSelected ? AppColors.indigo600 : AppColors.gray300,
                          ),
                        ),
                        child: Text(
                          preset,
                          style: TextStyle(
                            fontSize: 12,
                            fontFamily: 'monospace',
                            color: isSelected ? AppColors.indigo700 : AppColors.gray700,
                            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),

                const SizedBox(height: 20),

                // Connection Test Feedback Box
                AnimatedBuilder(
                  animation: controller,
                  builder: (context, _) {
                    final isOnline = controller.isOnline;
                    final isChecking = controller.isChecking;

                    return Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: isChecking
                            ? AppColors.indigo50
                            : (isOnline == true
                                ? AppColors.emerald50
                                : (isOnline == false ? AppColors.red50 : AppColors.gray50)),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: isChecking
                              ? AppColors.indigo200
                              : (isOnline == true
                                  ? AppColors.emerald500.withValues(alpha: 0.5)
                                  : (isOnline == false ? AppColors.red500.withValues(alpha: 0.4) : AppColors.gray200)),
                        ),
                      ),
                      child: Row(
                        children: [
                          if (isChecking) ...[
                            const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.indigo600),
                            ),
                          ] else if (isOnline == true) ...[
                            const Icon(Icons.check_circle, color: AppColors.emerald500, size: 20),
                          ] else if (isOnline == false) ...[
                            const Icon(Icons.error_outline, color: AppColors.red500, size: 20),
                          ] else ...[
                            const Icon(Icons.info_outline, color: AppColors.gray500, size: 20),
                          ],
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              controller.statusMessage.isNotEmpty
                                  ? controller.statusMessage
                                  : 'Click "Test Ping" to verify LAN connection with host PC.',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w500,
                                color: isOnline == true
                                    ? AppColors.emerald700
                                    : (isOnline == false ? AppColors.red700 : AppColors.gray700),
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ],
            ),
          ),

          const Divider(height: 1),

          // Actions
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 16, 24, 20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                OutlinedButton.icon(
                  onPressed: _onTest,
                  icon: const Icon(Icons.network_check, size: 18),
                  label: const Text('Test Ping'),
                ),
                const SizedBox(width: 12),
                ElevatedButton.icon(
                  onPressed: _onSave,
                  icon: const Icon(Icons.save, size: 18),
                  label: const Text('Save & Apply'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
