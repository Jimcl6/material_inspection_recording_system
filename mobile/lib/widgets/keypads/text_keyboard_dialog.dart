import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';

class TextKeyboardDialog extends StatefulWidget {
  final String title;
  final String initialValue;
  final String hint;

  const TextKeyboardDialog({
    super.key,
    required this.title,
    this.initialValue = '',
    this.hint = 'Type or scan text...',
  });

  static Future<String?> show({
    required BuildContext context,
    required String title,
    String initialValue = '',
    String hint = '',
  }) {
    return showDialog<String>(
      context: context,
      barrierDismissible: true,
      builder: (ctx) => TextKeyboardDialog(
        title: title,
        initialValue: initialValue,
        hint: hint.isNotEmpty ? hint : 'Type or scan text...',
      ),
    );
  }

  @override
  State<TextKeyboardDialog> createState() => _TextKeyboardDialogState();
}

class _TextKeyboardDialogState extends State<TextKeyboardDialog> {
  late String _draft;
  bool _isCaps = true;

  final List<String> _numbersRow = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-'];
  final List<String> _row1 = ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'];
  final List<String> _row2 = ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'];
  final List<String> _row3 = ['Z', 'X', 'C', 'V', 'B', 'N', 'M'];

  @override
  void initState() {
    super.initState();
    _draft = widget.initialValue;
  }

  void _onCharPressed(String char) {
    setState(() {
      _draft += _isCaps ? char.toUpperCase() : char.toLowerCase();
    });
  }

  void _onBackspace() {
    setState(() {
      if (_draft.isNotEmpty) {
        _draft = _draft.substring(0, _draft.length - 1);
      }
    });
  }

  void _onSpace() {
    setState(() {
      _draft += ' ';
    });
  }

  void _onClear() {
    setState(() {
      _draft = '';
    });
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      elevation: 0,
      insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
      child: Container(
        width: 780,
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.gray200),
          boxShadow: const [
            BoxShadow(
              color: Color(0x33000000),
              blurRadius: 32,
              offset: Offset(0, 16),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 14, 16, 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    widget.title,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.gray900,
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

            // Input Preview Box
            Padding(
              padding: const EdgeInsets.all(14),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: AppColors.gray50,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.gray300),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        _draft.isEmpty ? widget.hint : _draft,
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: _draft.isEmpty ? AppColors.gray400 : AppColors.gray900,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (_draft.isNotEmpty)
                      IconButton(
                        icon: const Icon(Icons.clear, color: AppColors.gray400),
                        onPressed: _onClear,
                        tooltip: 'Clear',
                      ),
                  ],
                ),
              ),
            ),

            // Virtual Keyboard Keys (Landscape optimized)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              child: Column(
                children: [
                  // Row 0: Numbers
                  _buildKeyRow(_numbersRow),
                  const SizedBox(height: 6),

                  // Row 1: QWERTY
                  _buildKeyRow(_row1),
                  const SizedBox(height: 6),

                  // Row 2: ASDF
                  _buildKeyRow(_row2),
                  const SizedBox(height: 6),

                  // Row 3: Shift + ZXCVB + Backspace
                  Row(
                    children: [
                      // Shift / Caps key
                      Expanded(
                        flex: 2,
                        child: _buildSpecialKey(
                          label: _isCaps ? 'CAPS ON' : 'CAPS OFF',
                          icon: Icons.keyboard_capslock,
                          isActive: _isCaps,
                          onTap: () => setState(() => _isCaps = !_isCaps),
                        ),
                      ),
                      const SizedBox(width: 4),
                      ..._row3.map((char) => Expanded(
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 2),
                              child: _buildKeyBtn(char),
                            ),
                          )),
                      const SizedBox(width: 4),
                      // Backspace key
                      Expanded(
                        flex: 2,
                        child: _buildSpecialKey(
                          label: 'DEL',
                          icon: Icons.backspace_outlined,
                          onTap: _onBackspace,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Row 4: Space + Dot + Underscore
                  Row(
                    children: [
                      Expanded(
                        child: _buildSpecialKey(
                          label: '.',
                          onTap: () => _onCharPressed('.'),
                        ),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: _buildSpecialKey(
                          label: '/',
                          onTap: () => _onCharPressed('/'),
                        ),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        flex: 6,
                        child: _buildSpecialKey(
                          label: 'SPACE',
                          icon: Icons.space_bar,
                          onTap: _onSpace,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: _buildSpecialKey(
                          label: '#',
                          onTap: () => _onCharPressed('#'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 14),
            const Divider(height: 1),

            // Bottom Buttons
            Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.of(context).pop(),
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size(0, 48),
                      ),
                      child: const Text('Cancel'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton(
                      onPressed: () => Navigator.of(context).pop(_draft),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.indigo600,
                        foregroundColor: AppColors.white,
                        minimumSize: const Size(0, 48),
                      ),
                      child: const Text(
                        'Apply Text',
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
      children: keys.map((char) {
        return Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 2),
            child: _buildKeyBtn(char),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildKeyBtn(String char) {
    final display = _isCaps ? char.toUpperCase() : char.toLowerCase();
    return Material(
      color: AppColors.white,
      borderRadius: BorderRadius.circular(6),
      child: InkWell(
        borderRadius: BorderRadius.circular(6),
        onTap: () => _onCharPressed(char),
        child: Container(
          height: 48,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(6),
            border: Border.all(color: AppColors.gray300),
          ),
          alignment: Alignment.center,
          child: Text(
            display,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: AppColors.gray900,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSpecialKey({
    required String label,
    IconData? icon,
    bool isActive = false,
    required VoidCallback onTap,
  }) {
    return Material(
      color: isActive ? AppColors.indigo50 : AppColors.gray100,
      borderRadius: BorderRadius.circular(6),
      child: InkWell(
        borderRadius: BorderRadius.circular(6),
        onTap: onTap,
        child: Container(
          height: 48,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(6),
            border: Border.all(
              color: isActive ? AppColors.indigo500 : AppColors.gray300,
            ),
          ),
          alignment: Alignment.center,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (icon != null) ...[
                Icon(
                  icon,
                  size: 18,
                  color: isActive ? AppColors.indigo600 : AppColors.gray700,
                ),
                const SizedBox(width: 4),
              ],
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isActive ? AppColors.indigo600 : AppColors.gray700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
