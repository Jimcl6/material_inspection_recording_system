import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'scan_payload_parser.dart';

class ScannerEventBus with ChangeNotifier {
  static final ScannerEventBus instance = ScannerEventBus._internal();
  ScannerEventBus._internal() {
    _initListener();
  }

  final StreamController<ParsedScan> _scanStreamController =
      StreamController<ParsedScan>.broadcast();

  Stream<ParsedScan> get onScan => _scanStreamController.stream;

  final StringBuffer _buffer = StringBuffer();
  DateTime _lastKeystrokeTime = DateTime.now();
  ParsedScan? _lastScan;
  int _totalScansCount = 0;
  bool _isListenerActive = true;

  // Maximum gap in milliseconds between barcode scanner characters
  static const int barcodeCharThresholdMs = 90;

  ParsedScan? get lastScan => _lastScan;
  int get totalScansCount => _totalScansCount;
  bool get isListenerActive => _isListenerActive;

  void setListenerActive(bool active) {
    _isListenerActive = active;
    notifyListeners();
  }

  void _initListener() {
    HardwareKeyboard.instance.addHandler(_handleKeyEvent);
  }

  bool _handleKeyEvent(KeyEvent event) {
    if (!_isListenerActive) return false;

    // Only process KeyDown events
    if (event is! KeyDownEvent) return false;

    final now = DateTime.now();
    final elapsed = now.difference(_lastKeystrokeTime).inMilliseconds;
    _lastKeystrokeTime = now;

    // If too much time has passed since the last keystroke and buffer is not empty,
    // this was manual slow typing rather than a fast barcode scan burst.
    if (elapsed > barcodeCharThresholdMs && _buffer.isNotEmpty) {
      _buffer.clear();
    }

    final key = event.logicalKey;

    // Detect Enter / Return terminator from wireless scanner
    if (key == LogicalKeyboardKey.enter || key == LogicalKeyboardKey.numpadEnter) {
      if (_buffer.isNotEmpty) {
        final rawText = _buffer.toString();
        _buffer.clear();

        // Dispatch parsed scan
        _dispatchScan(rawText);
        return true; // Handled
      }
      return false;
    }

    // Extract character
    final char = event.character;
    if (char != null && char.isNotEmpty && char != '\n' && char != '\r') {
      _buffer.write(char);
    }

    return false; // Allow event to propagate to focused field if desired
  }

  void _dispatchScan(String rawText) {
    final parsed = ScanPayloadParser.parse(rawText);
    _lastScan = parsed;
    _totalScansCount++;
    _scanStreamController.add(parsed);
    notifyListeners();
  }

  // Utility to simulate or manually inject a scan during testing
  void simulateScan(String text) {
    _dispatchScan(text);
  }

  void clearHistory() {
    _lastScan = null;
    _totalScansCount = 0;
    _buffer.clear();
    notifyListeners();
  }

  @override
  void dispose() {
    HardwareKeyboard.instance.removeHandler(_handleKeyEvent);
    _scanStreamController.close();
    super.dispose();
  }
}
