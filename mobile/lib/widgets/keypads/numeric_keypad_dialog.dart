import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';

class NumericKeypadDialog extends StatefulWidget {
  final String title;
  final String initialValue;
  final String unit;
  final int decimalPlaces;
  final double? maxValue;
  final double? minValue;
  final bool showNext;
  final String nextLabel;

  const NumericKeypadDialog({
    super.key,
    required this.title,
    this.initialValue = '',
    this.unit = '',
    this.decimalPlaces = 2,
    this.maxValue,
    this.minValue,
    this.showNext = false,
    this.nextLabel = 'Next Reading',
  });

  static Future<String?> show({
    required BuildContext context,
    required String title,
    String initialValue = '',
    String unit = '',
    int decimalPlaces = 2,
    double? maxValue,
    double? minValue,
  }) {
    return showDialog<String>(
      context: context,
      barrierDismissible: true,
      builder: (ctx) => NumericKeypadDialog(
        title: title,
        initialValue: initialValue,
        unit: unit,
        decimalPlaces: decimalPlaces,
        maxValue: maxValue,
        minValue: minValue,
      ),
    );
  }

  @override
  State<NumericKeypadDialog> createState() => _NumericKeypadDialogState();
}

class _NumericKeypadDialogState extends State<NumericKeypadDialog> {
  late String _draft;
  String _errorText = '';

  @override
  void initState() {
    super.initState();
    _draft = widget.initialValue.trim();
  }

  void _onDigitPressed(String digit) {
    setState(() {
      _errorText = '';
      if (digit == '.') {
        if (widget.decimalPlaces == 0) return;
        if (_draft.contains('.')) return;
        if (_draft.isEmpty) {
          _draft = '0.';
        } else {
          _draft += '.';
        }
        return;
      }

      // Check decimal places constraint
      if (_draft.contains('.')) {
        final parts = _draft.split('.');
        if (parts.length > 1 && parts[1].length >= widget.decimalPlaces) {
          _errorText = 'Max ${widget.decimalPlaces} decimal place${widget.decimalPlaces == 1 ? '' : 's'}';
          return;
        }
      }

      // Avoid leading zeroes like 00
      if (_draft == '0') {
        _draft = digit;
      } else {
        _draft += digit;
      }

      // Check max constraint
      if (widget.maxValue != null) {
        final val = double.tryParse(_draft);
        if (val != null && val > widget.maxValue!) {
          _errorText = 'Max value is ${widget.maxValue}';
        }
      }
    });
  }

  void _onBackspace() {
    setState(() {
      _errorText = '';
      if (_draft.isNotEmpty) {
        _draft = _draft.substring(0, _draft.length - 1);
      }
    });
  }

  void _onClear() {
    setState(() {
      _draft = '';
      _errorText = '';
    });
  }

  void _onConfirm() {
    if (_draft.endsWith('.')) {
      setState(() {
        _errorText = 'Please enter a digit after decimal';
      });
      return;
    }

    final val = double.tryParse(_draft);
    if (val != null) {
      if (widget.maxValue != null && val > widget.maxValue!) {
        setState(() {
          _errorText = 'Value exceeds maximum ${widget.maxValue}';
        });
        return;
      }
      if (widget.minValue != null && val < widget.minValue!) {
        setState(() {
          _errorText = 'Value below minimum ${widget.minValue}';
        });
        return;
      }
    }

    Navigator.of(context).pop(_draft);
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      elevation: 0,
      insetPadding: const EdgeInsets.symmetric(horizontal: 40, vertical: 24),
      child: Container(
        width: 440,
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.gray200),
          boxShadow: const [
            BoxShadow(
              color: Color(0x26000000),
              blurRadius: 24,
              offset: Offset(0, 12),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 16, 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Row(
                      children: [
                        Flexible(
                          child: Text(
                            widget.title,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: AppColors.gray900,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (widget.unit.isNotEmpty) ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.indigo50,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              widget.unit,
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: AppColors.indigo600,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: AppColors.gray500),
                    onPressed: () => Navigator.of(context).pop(),
                    splashRadius: 20,
                  ),
                ],
              ),
            ),
            const Divider(height: 1),

            // Number Display Box
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  color: AppColors.gray50,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: _errorText.isNotEmpty ? AppColors.red500 : AppColors.gray300,
                    width: _errorText.isNotEmpty ? 2 : 1,
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        _draft.isEmpty ? '0' : _draft,
                        style: TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                          color: _draft.isEmpty ? AppColors.gray400 : AppColors.gray900,
                          letterSpacing: 1.2,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (_draft.isNotEmpty)
                      GestureDetector(
                        onTap: _onClear,
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 8),
                          child: Text(
                            'CLEAR',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: AppColors.indigo600,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),

            if (_errorText.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(left: 20, right: 20, bottom: 8),
                child: Text(
                  _errorText,
                  style: const TextStyle(
                    color: AppColors.red600,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),

            // Keypad Grid
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                children: [
                  _buildKeyRow(['1', '2', '3']),
                  const SizedBox(height: 8),
                  _buildKeyRow(['4', '5', '6']),
                  const SizedBox(height: 8),
                  _buildKeyRow(['7', '8', '9']),
                  const SizedBox(height: 8),
                  _buildKeyRow(['.', '0', '⌫']),
                ],
              ),
            ),

            const SizedBox(height: 16),
            const Divider(height: 1),

            // Bottom Confirm / Cancel Actions
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.of(context).pop(),
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size(0, 48),
                        side: const BorderSide(color: AppColors.gray300),
                      ),
                      child: const Text('Cancel'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton(
                      onPressed: _onConfirm,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.indigo600,
                        foregroundColor: AppColors.white,
                        minimumSize: const Size(0, 48),
                      ),
                      child: const Text(
                        'Confirm Value',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildKeyRow(List<String> keys) {
    return Row(
      children: keys.map((key) {
        return Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Material(
              color: key == '⌫' ? AppColors.gray100 : AppColors.white,
              borderRadius: BorderRadius.circular(8),
              child: InkWell(
                borderRadius: BorderRadius.circular(8),
                onTap: () {
                  if (key == '⌫') {
                    _onBackspace();
                  } else {
                    _onDigitPressed(key);
                  }
                },
                child: Container(
                  height: 52,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: AppColors.gray200),
                  ),
                  alignment: Alignment.center,
                  child: key == '⌫'
                      ? const Icon(Icons.backspace_outlined, size: 22, color: AppColors.gray700)
                      : Text(
                          key,
                          style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.w600,
                            color: AppColors.gray900,
                          ),
                        ),
                ),
              ),
            ),
          ),
        );
      }).toList(),
    );
  }
}
