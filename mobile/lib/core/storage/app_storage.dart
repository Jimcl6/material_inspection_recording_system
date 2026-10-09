import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class AppStorage {
  static const String keyServerUrl = 'mirs_server_url';
  static const String keyAuthToken = 'mirs_auth_token';
  static const String keyRememberedEmail = 'mirs_remembered_email';
  static const String keyCachedUser = 'mirs_cached_user';

  // Default address matching local development setup
  static const String defaultServerUrl = 'http://127.0.0.1:8000/api';

  final SharedPreferences _prefs;

  AppStorage(this._prefs);

  static Future<AppStorage> init() async {
    final prefs = await SharedPreferences.getInstance();
    return AppStorage(prefs);
  }

  String getServerUrl() {
    return _prefs.getString(keyServerUrl) ?? defaultServerUrl;
  }

  Future<void> setServerUrl(String url) async {
    final normalized = url.trim().endsWith('/')
        ? url.trim().substring(0, url.trim().length - 1)
        : url.trim();
    await _prefs.setString(keyServerUrl, normalized);
  }

  String? getAuthToken() {
    return _prefs.getString(keyAuthToken);
  }

  Future<void> setAuthToken(String? token) async {
    if (token == null) {
      await _prefs.remove(keyAuthToken);
    } else {
      await _prefs.setString(keyAuthToken, token);
    }
  }

  String? getRememberedEmail() {
    return _prefs.getString(keyRememberedEmail);
  }

  Future<void> setRememberedEmail(String? email) async {
    if (email == null) {
      await _prefs.remove(keyRememberedEmail);
    } else {
      await _prefs.setString(keyRememberedEmail, email);
    }
  }

  Map<String, dynamic>? getCachedUser() {
    final raw = _prefs.getString(keyCachedUser);
    if (raw == null) return null;
    try {
      return jsonDecode(raw) as Map<String, dynamic>;
    } catch (_) {
      return null;
    }
  }

  Future<void> setCachedUser(Map<String, dynamic>? user) async {
    if (user == null) {
      await _prefs.remove(keyCachedUser);
    } else {
      await _prefs.setString(keyCachedUser, jsonEncode(user));
    }
  }

  Future<void> clearSession() async {
    await _prefs.remove(keyAuthToken);
    await _prefs.remove(keyCachedUser);
  }
}
