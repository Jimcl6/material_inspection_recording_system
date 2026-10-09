import 'package:flutter_test/flutter_test.dart';
import 'package:mirs_mobile/core/scanner/scan_payload_parser.dart';

void main() {
  group('ScanPayloadParser Tests', () {
    test('parses standard manufacturing item code correctly', () {
      final scan = ScanPayloadParser.parse('25-431');
      expect(scan.type, equals(ScanType.itemCode));
      expect(scan.value, equals('25-431'));
    });

    test('parses employee badge formats correctly', () {
      final scan = ScanPayloadParser.parse('EMP-1004');
      expect(scan.type, equals(ScanType.employeeBadge));
      expect(scan.value, equals('EMP-1004'));
    });

    test('parses production lot numbers correctly', () {
      final scan = ScanPayloadParser.parse('LOT-20261009');
      expect(scan.type, equals(ScanType.lotNumber));
      expect(scan.value, equals('LOT-20261009'));
    });
  });
}
