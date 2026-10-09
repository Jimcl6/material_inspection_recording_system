enum ScanType {
  employeeBadge,
  itemCode,
  lotNumber,
  rawText,
}

class ScanPayloadParser {
  static ParsedScan parse(String input) => ParsedScan.parse(input);
}

class ParsedScan {
  final String rawText;
  final ScanType type;
  final String value;
  final Map<String, dynamic> metadata;
  final DateTime timestamp;

  ParsedScan({
    required this.rawText,
    required this.type,
    required this.value,
    this.metadata = const {},
    DateTime? timestamp,
  }) : timestamp = timestamp ?? DateTime.now();

  static ParsedScan parse(String input) {
    final clean = input.trim();

    // 1. Employee Badge JSON format: {"employee_id": "EMP-001", "name": "...", ...}
    if (clean.startsWith('{') && clean.endsWith('}')) {
      return ParsedScan(
        rawText: clean,
        type: ScanType.employeeBadge,
        value: clean,
        metadata: {'format': 'json_badge'},
      );
    }

    // 2. Employee Badge format: starts with EMP-, BADGE-, ID-
    final badgeRegex = RegExp(r'^(EMP|ID|BADGE)[-_]?(\w+)$', caseSensitive: false);
    final badgeMatch = badgeRegex.firstMatch(clean);
    if (badgeMatch != null) {
      return ParsedScan(
        rawText: clean,
        type: ScanType.employeeBadge,
        value: clean,
        metadata: {'code': badgeMatch.group(2)},
      );
    }

    // 3. Item Code pattern (e.g. "25-431", "CSB29046P3", or standard manufacturing code)
    final itemCodeRegex = RegExp(r'(\b\d{2,3}-\d{2,4}\b|[A-Z]{2,4}\d{4,8}[A-Z\d]*)', caseSensitive: false);
    final itemMatch = itemCodeRegex.firstMatch(clean);
    if (itemMatch != null) {
      return ParsedScan(
        rawText: clean,
        type: ScanType.itemCode,
        value: itemMatch.group(0) ?? clean,
        metadata: {'matched_pattern': 'item_code'},
      );
    }

    // 4. Lot number pattern (e.g., LOT-20261009, 20261009-A)
    final lotRegex = RegExp(r'^(LOT|BAT)[-_]?([\w-]+)$', caseSensitive: false);
    final lotMatch = lotRegex.firstMatch(clean);
    if (lotMatch != null) {
      return ParsedScan(
        rawText: clean,
        type: ScanType.lotNumber,
        value: clean,
        metadata: {'lot': lotMatch.group(2)},
      );
    }

    // Fallback: general raw text
    return ParsedScan(
      rawText: clean,
      type: ScanType.rawText,
      value: clean,
    );
  }
}
