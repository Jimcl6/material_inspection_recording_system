import 'package:flutter/material.dart';
import '../core/network/api_client.dart';
import '../core/storage/app_storage.dart';

class ServerConfigController with ChangeNotifier {
  final AppStorage storage;
  final ApiClient apiClient;

  late String _serverUrl;
  bool _isChecking = false;
  bool? _isOnline;
  String _statusMessage = '';

  ServerConfigController({required this.storage, required this.apiClient}) {
    _serverUrl = storage.getServerUrl();
  }

  String get serverUrl => _serverUrl;
  bool get isChecking => _isChecking;
  bool? get isOnline => _isOnline;
  String get statusMessage => _statusMessage;

  Future<bool> testConnection([String? testUrl]) async {
    _isChecking = true;
    _statusMessage = 'Connecting to server...';
    notifyListeners();

    final target = testUrl ?? _serverUrl;
    final result = await apiClient.pingServer(target);

    _isChecking = false;
    _isOnline = result.isSuccess;
    _statusMessage = result.message ?? (result.isSuccess ? 'Connected' : 'Connection failed');
    notifyListeners();

    return result.isSuccess;
  }

  Future<void> updateServerUrl(String newUrl) async {
    _serverUrl = newUrl.trim();
    await storage.setServerUrl(_serverUrl);
    _isOnline = null;
    _statusMessage = '';
    notifyListeners();
  }
}
