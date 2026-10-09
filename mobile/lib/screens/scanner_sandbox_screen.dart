import 'dart:async';
import 'package:flutter/material.dart';
import '../core/scanner/scan_payload_parser.dart';
import '../core/scanner/scanner_event_bus.dart';
import '../core/theme/app_colors.dart';
import '../widgets/keypads/numeric_keypad_dialog.dart';
import '../widgets/keypads/text_keyboard_dialog.dart';

class ScannerSandboxScreen extends StatefulWidget {
  const ScannerSandboxScreen({super.key});

  @override
  State<ScannerSandboxScreen> createState() => _ScannerSandboxScreenState();
}

class _ScannerSandboxScreenState extends State<ScannerSandboxScreen> {
  final ScannerEventBus _scannerBus = ScannerEventBus.instance;
  late StreamSubscription<ParsedScan> _scanSubscription;

  // Test form values
  String _itemCode = '';
  String _badgeId = '';
  String _temperatureValue = '';
  String _collapseDepthValue = '';
  String _operatorNotes = '';

  // Focus testing field
  final TextEditingController _focusedController = TextEditingController();

  final List<ParsedScan> _scanLog = [];

  @override
  void initState() {
    super.initState();
    _scanSubscription = _scannerBus.onScan.listen(_handleGlobalScan);
  }

  @override
  void dispose() {
    _scanSubscription.cancel();
    _focusedController.dispose();
    super.dispose();
  }

  void _handleGlobalScan(ParsedScan scan) {
    setState(() {
      _scanLog.insert(0, scan);
      if (_scanLog.length > 20) {
        _scanLog.removeLast();
      }

      // Smart Context Routing:
      if (scan.type == ScanType.employeeBadge) {
        _badgeId = scan.value;
      } else if (scan.type == ScanType.itemCode) {
        _itemCode = scan.value;
      }
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Scanned [${scan.type.name}]: ${scan.value}',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppColors.indigo600,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _openTemperatureKeypad() async {
    final result = await NumericKeypadDialog.show(
      context: context,
      title: 'Annealing / Furnace Temperature',
      initialValue: _temperatureValue,
      unit: '°C',
      decimalPlaces: 1,
      minValue: 0,
      maxValue: 1200,
    );

    if (result != null) {
      setState(() {
        _temperatureValue = result;
      });
    }
  }

  void _openCollapseDepthKeypad() async {
    final result = await NumericKeypadDialog.show(
      context: context,
      title: 'Welding Collapse Depth',
      initialValue: _collapseDepthValue,
      unit: 'mm',
      decimalPlaces: 2,
      minValue: 0,
      maxValue: 15,
    );

    if (result != null) {
      setState(() {
        _collapseDepthValue = result;
      });
    }
  }

  void _openRemarksKeyboard() async {
    final result = await TextKeyboardDialog.show(
      context: context,
      title: 'Inspection Notes / Alphanumeric Remarks',
      initialValue: _operatorNotes,
      hint: 'Enter remarks or alphanumeric lot code...',
    );

    if (result != null) {
      setState(() {
        _operatorNotes = result;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final lastScan = _scannerBus.lastScan;

    return Scaffold(
      backgroundColor: AppColors.gray100,
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Left Column: Live Scanner Stream & Diagnostics
            Expanded(
              flex: 5,
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header & Status
                      Row(
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
                                child: const Icon(
                                  Icons.bluetooth_searching,
                                  color: AppColors.indigo600,
                                  size: 24,
                                ),
                              ),
                              const SizedBox(width: 12),
                              const Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Bluetooth HID Scanner Monitor',
                                    style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.gray900,
                                    ),
                                  ),
                                  Text(
                                    'Listening globally for hardware keyboard/scanner input',
                                    style: TextStyle(fontSize: 12, color: AppColors.gray500),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.emerald50,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.emerald500.withValues(alpha: 0.3)),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.flash_on, color: AppColors.emerald500, size: 14),
                                const SizedBox(width: 4),
                                Text(
                                  '${_scannerBus.totalScansCount} Scans',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.emerald700,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      const Divider(),
                      const SizedBox(height: 16),

                      // Latest Scan Highlight Card
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.gray50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.indigo200),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text(
                                  'MOST RECENT SCAN',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.indigo600,
                                    letterSpacing: 0.8,
                                  ),
                                ),
                                if (lastScan != null)
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: AppColors.indigo100,
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      lastScan.type.name.toUpperCase(),
                                      style: const TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                        color: AppColors.indigo800,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              lastScan?.value ?? 'Pull the trigger on your wireless scanner to test...',
                              style: TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: lastScan != null ? AppColors.gray900 : AppColors.gray400,
                                fontFamily: 'monospace',
                              ),
                            ),
                            if (lastScan != null) ...[
                              const SizedBox(height: 6),
                              Text(
                                'Received at ${lastScan.timestamp.toLocal().toString().split('.').first}',
                                style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                              ),
                            ],
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Scanner Simulator Buttons
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        children: [
                          const Text(
                            'Simulate:',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.gray600),
                          ),
                          ActionChip(
                            label: const Text('Item 25-431'),
                            onPressed: () => _scannerBus.simulateScan('25-431'),
                          ),
                          ActionChip(
                            label: const Text('Badge EMP-001'),
                            onPressed: () => _scannerBus.simulateScan('EMP-001'),
                          ),
                          ActionChip(
                            label: const Text('Lot 20261009-A'),
                            onPressed: () => _scannerBus.simulateScan('LOT-20261009-A'),
                          ),
                          TextButton(
                            onPressed: () {
                              _scannerBus.clearHistory();
                              setState(() => _scanLog.clear());
                            },
                            child: const Text('Clear Log'),
                          ),
                        ],
                      ),

                      const SizedBox(height: 12),
                      const Text(
                        'Live Scan History (last 20 bursts):',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.gray700),
                      ),
                      const SizedBox(height: 8),

                      // History List
                      Expanded(
                        child: _scanLog.isEmpty
                          ? Center(
                              child: Text(
                                'No scans recorded yet.\nConnect scanner via Bluetooth and pull trigger.',
                                textAlign: TextAlign.center,
                                style: TextStyle(color: AppColors.gray400, fontSize: 13),
                              ),
                            )
                          : ListView.separated(
                              itemCount: _scanLog.length,
                              separatorBuilder: (_, index) => const Divider(height: 1),
                              itemBuilder: (ctx, idx) {
                                final item = _scanLog[idx];
                                return ListTile(
                                  dense: true,
                                  visualDensity: VisualDensity.compact,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 8),
                                  leading: Icon(
                                    item.type == ScanType.employeeBadge
                                        ? Icons.badge_outlined
                                        : (item.type == ScanType.itemCode
                                            ? Icons.qr_code
                                            : Icons.barcode_reader),
                                    color: AppColors.indigo600,
                                    size: 20,
                                  ),
                                  title: Text(
                                    item.value,
                                    style: const TextStyle(fontWeight: FontWeight.w600, fontFamily: 'monospace'),
                                  ),
                                  subtitle: Text(
                                    '${item.type.name} • ${item.timestamp.toLocal().toString().split('.').first}',
                                    style: const TextStyle(fontSize: 11),
                                  ),
                                );
                              },
                            ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            const SizedBox(width: 20),

            // Right Column: Pop-Up Keypad & Context Routing Tests
            Expanded(
              flex: 5,
              child: SingleChildScrollView(
                child: Column(
                  children: [
                    // Pop-up Keypads Verification Card
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Row(
                              children: [
                                Icon(Icons.dialpad, color: AppColors.indigo600, size: 22),
                                SizedBox(width: 10),
                                Text(
                                  'In-App Touch Keypads Test',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray900),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Verify that custom on-screen keypads appear on tap while Bluetooth scanner is connected.',
                              style: TextStyle(fontSize: 12, color: AppColors.gray500),
                            ),
                            const SizedBox(height: 16),
                            const Divider(),
                            const SizedBox(height: 16),

                            // Temperature Reading Keypad Trigger
                            _buildKeypadFieldTile(
                              label: 'Furnace Temperature',
                              value: _temperatureValue.isNotEmpty ? '$_temperatureValue °C' : 'Tap to open Numpad...',
                              unit: '°C',
                              icon: Icons.thermostat,
                              onTap: _openTemperatureKeypad,
                            ),
                            const SizedBox(height: 12),

                            // Collapse Depth Keypad Trigger
                            _buildKeypadFieldTile(
                              label: 'Welding Collapse Depth',
                              value: _collapseDepthValue.isNotEmpty ? '$_collapseDepthValue mm' : 'Tap to open Numpad...',
                              unit: 'mm',
                              icon: Icons.compress,
                              onTap: _openCollapseDepthKeypad,
                            ),
                            const SizedBox(height: 12),

                            // Remarks Touch Keyboard Trigger
                            _buildKeypadFieldTile(
                              label: 'Operator Notes / Remarks',
                              value: _operatorNotes.isNotEmpty ? _operatorNotes : 'Tap to open QWERTY touch keyboard...',
                              icon: Icons.keyboard_alt_outlined,
                              onTap: _openRemarksKeyboard,
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Smart Context Routing Demonstration Card
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Row(
                              children: [
                                Icon(Icons.alt_route, color: AppColors.indigo600, size: 22),
                                SizedBox(width: 10),
                                Text(
                                  'Smart Context Auto-Routing',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray900),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Scan barcodes without focusing any field to see automatic field dispatch.',
                              style: TextStyle(fontSize: 12, color: AppColors.gray500),
                            ),
                            const SizedBox(height: 16),
                            const Divider(),
                            const SizedBox(height: 16),

                            // Auto-Populated Item Code
                            _buildAutoRoutedField(
                              label: 'Auto-Routed Item Code',
                              value: _itemCode,
                              placeholder: 'Scan an Item Code barcode...',
                              icon: Icons.inventory_2_outlined,
                              onClear: () => setState(() => _itemCode = ''),
                            ),
                            const SizedBox(height: 12),

                            // Auto-Populated Badge
                            _buildAutoRoutedField(
                              label: 'Auto-Routed Operator Badge',
                              value: _badgeId,
                              placeholder: 'Scan an Employee Badge QR...',
                              icon: Icons.badge_outlined,
                              onClear: () => setState(() => _badgeId = ''),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildKeypadFieldTile({
    required String label,
    required String value,
    String? unit,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    final hasValue = !value.startsWith('Tap to');
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700),
        ),
        const SizedBox(height: 6),
        GestureDetector(
          onTap: onTap,
          behavior: HitTestBehavior.opaque,
          child: Container(
            height: 48,
            padding: const EdgeInsets.symmetric(horizontal: 14),
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: hasValue ? AppColors.indigo600 : AppColors.gray300, width: hasValue ? 1.5 : 1),
            ),
            child: Row(
              children: [
                Icon(icon, color: hasValue ? AppColors.indigo600 : AppColors.gray400, size: 20),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    value,
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: hasValue ? FontWeight.bold : FontWeight.normal,
                      color: hasValue ? AppColors.gray900 : AppColors.gray400,
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.gray100,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Text(
                    'TOUCH INPUT',
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.gray600),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildAutoRoutedField({
    required String label,
    required String value,
    required String placeholder,
    required IconData icon,
    required VoidCallback onClear,
  }) {
    final hasValue = value.isNotEmpty;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.gray700),
        ),
        const SizedBox(height: 6),
        Container(
          height: 48,
          padding: const EdgeInsets.symmetric(horizontal: 14),
          decoration: BoxDecoration(
            color: hasValue ? AppColors.emerald50 : AppColors.gray50,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: hasValue ? AppColors.emerald500 : AppColors.gray300,
              width: hasValue ? 1.5 : 1,
            ),
          ),
          child: Row(
            children: [
              Icon(icon, color: hasValue ? AppColors.emerald700 : AppColors.gray400, size: 20),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  hasValue ? value : placeholder,
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: hasValue ? FontWeight.bold : FontWeight.normal,
                    color: hasValue ? AppColors.gray900 : AppColors.gray400,
                    fontFamily: hasValue ? 'monospace' : null,
                  ),
                ),
              ),
              if (hasValue)
                IconButton(
                  icon: const Icon(Icons.clear, size: 18, color: AppColors.gray500),
                  onPressed: onClear,
                  splashRadius: 18,
                ),
            ],
          ),
        ),
      ],
    );
  }
}
