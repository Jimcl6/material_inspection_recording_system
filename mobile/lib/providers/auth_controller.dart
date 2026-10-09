import 'package:flutter/material.dart';
import '../core/network/api_client.dart';
import '../core/storage/app_storage.dart';
import '../models/user_model.dart';

class AuthController with ChangeNotifier {
  final AppStorage storage;
  final ApiClient apiClient;

  UserModel? _currentUser;
  bool _isLoading = false;
  String? _errorMessage;

  AuthController({required this.storage, required this.apiClient}) {
    _loadCachedUser();
  }

  UserModel? get currentUser => _currentUser;
  bool get isAuthenticated => _currentUser != null && storage.getAuthToken() != null;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  void _loadCachedUser() {
    final cached = storage.getCachedUser();
    if (cached != null) {
      _currentUser = UserModel.fromJson(cached, cached['permissions'] as Map<String, dynamic>?);
    }
  }

  Future<bool> checkAuthStatus() async {
    final token = storage.getAuthToken();
    if (token == null) {
      _currentUser = null;
      notifyListeners();
      return false;
    }

    _isLoading = true;
    notifyListeners();

    final response = await apiClient.get('me');
    _isLoading = false;

    if (response.isSuccess && response.data != null) {
      final userData = response.data!['user'] as Map<String, dynamic>;
      final permsData = response.data!['permissions'] as Map<String, dynamic>?;
      _currentUser = UserModel.fromJson(userData, permsData);
      await storage.setCachedUser(response.data!);
      _errorMessage = null;
      notifyListeners();
      return true;
    } else {
      // Token is invalid/expired
      await storage.clearSession();
      _currentUser = null;
      _errorMessage = response.message;
      notifyListeners();
      return false;
    }
  }

  Future<bool> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final response = await apiClient.post(
      'login',
      body: {
        'email': email.trim(),
        'password': password,
        'device_name': deviceName ?? 'Samsung Galaxy Tab A9+ (MIRS)',
      },
    );

    _isLoading = false;

    if (response.isSuccess && response.data != null) {
      final token = response.data!['token'] as String;
      final userData = response.data!['user'] as Map<String, dynamic>;
      final permsData = response.data!['permissions'] as Map<String, dynamic>?;

      await storage.setAuthToken(token);
      await storage.setRememberedEmail(email.trim());

      _currentUser = UserModel.fromJson(userData, permsData);
      await storage.setCachedUser(response.data!);
      _errorMessage = null;

      notifyListeners();
      return true;
    } else {
      _errorMessage = response.message ?? 'Login failed. Please check your credentials.';
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await apiClient.post('logout');
    } catch (_) {
      // Ignore network errors on logout
    }

    await storage.clearSession();
    _currentUser = null;
    _isLoading = false;
    _errorMessage = null;
    notifyListeners();
  }
}
