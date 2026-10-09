import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../storage/app_storage.dart';

class ApiResponse<T> {
  final bool isSuccess;
  final int statusCode;
  final T? data;
  final String? message;
  final Map<String, dynamic>? rawJson;

  ApiResponse({
    required this.isSuccess,
    required this.statusCode,
    this.data,
    this.message,
    this.rawJson,
  });
}

class ApiClient {
  final AppStorage storage;
  final http.Client _client = http.Client();
  final Duration timeout = const Duration(seconds: 8);

  ApiClient(this.storage);

  Map<String, String> _buildHeaders() {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    final token = storage.getAuthToken();
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }

    return headers;
  }

  Uri _buildUri(String path, [Map<String, dynamic>? queryParams]) {
    final baseUrl = storage.getServerUrl();
    final cleanPath = path.startsWith('/') ? path.substring(1) : path;
    final fullUrl = '$baseUrl/$cleanPath';

    if (queryParams != null && queryParams.isNotEmpty) {
      final stringParams = queryParams.map(
        (key, value) => MapEntry(key, value.toString()),
      );
      return Uri.parse(fullUrl).replace(queryParameters: stringParams);
    }

    return Uri.parse(fullUrl);
  }

  Future<ApiResponse<Map<String, dynamic>>> get(
    String path, {
    Map<String, dynamic>? queryParams,
  }) async {
    try {
      final uri = _buildUri(path, queryParams);
      final response = await _client
          .get(uri, headers: _buildHeaders())
          .timeout(timeout);

      return _handleResponse(response);
    } on SocketException catch (e) {
      return ApiResponse(
        isSuccess: false,
        statusCode: 0,
        message: 'Cannot reach server at ${storage.getServerUrl()}: ${e.message}',
      );
    } on TimeoutException {
      return ApiResponse(
        isSuccess: false,
        statusCode: 408,
        message: 'Connection timed out. Please check your LAN Wi-Fi connection.',
      );
    } catch (e) {
      return ApiResponse(
        isSuccess: false,
        statusCode: 500,
        message: 'Unexpected network error: $e',
      );
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> post(
    String path, {
    Map<String, dynamic>? body,
  }) async {
    try {
      final uri = _buildUri(path);
      final response = await _client
          .post(
            uri,
            headers: _buildHeaders(),
            body: body != null ? jsonEncode(body) : null,
          )
          .timeout(timeout);

      return _handleResponse(response);
    } on SocketException catch (e) {
      return ApiResponse(
        isSuccess: false,
        statusCode: 0,
        message: 'Cannot connect to ${storage.getServerUrl()}: ${e.message}',
      );
    } on TimeoutException {
      return ApiResponse(
        isSuccess: false,
        statusCode: 408,
        message: 'Request timed out after ${timeout.inSeconds} seconds.',
      );
    } catch (e) {
      return ApiResponse(
        isSuccess: false,
        statusCode: 500,
        message: 'Network error: $e',
      );
    }
  }

  Future<ApiResponse<bool>> pingServer([String? testUrl]) async {
    try {
      final base = (testUrl != null && testUrl.isNotEmpty)
          ? (testUrl.endsWith('/') ? testUrl.substring(0, testUrl.length - 1) : testUrl)
          : storage.getServerUrl();

      // Test ping against root or api
      final uri = Uri.parse('$base/login');
      final response = await _client
          .get(uri, headers: {'Accept': 'application/json'})
          .timeout(const Duration(seconds: 4));

      // As long as the server responds (even 405 Method Not Allowed or 422), it's online!
      final reachable = response.statusCode > 0 && response.statusCode < 500;
      return ApiResponse(
        isSuccess: reachable,
        statusCode: response.statusCode,
        data: reachable,
        message: reachable ? 'Server is online and reachable' : 'Server returned HTTP ${response.statusCode}',
      );
    } catch (e) {
      return ApiResponse(
        isSuccess: false,
        statusCode: 0,
        data: false,
        message: 'Could not connect: $e',
      );
    }
  }

  ApiResponse<Map<String, dynamic>> _handleResponse(http.Response response) {
    Map<String, dynamic> jsonBody = {};
    try {
      if (response.body.isNotEmpty) {
        jsonBody = jsonDecode(response.body) as Map<String, dynamic>;
      }
    } catch (_) {
      // Non-json response
    }

    final isSuccess = response.statusCode >= 200 && response.statusCode < 300;
    String? message = jsonBody['message'] as String?;

    if (!isSuccess && message == null) {
      if (response.statusCode == 401) {
        message = 'Authentication required or token expired.';
      } else if (response.statusCode == 403) {
        message = 'Access forbidden.';
      } else if (response.statusCode == 422) {
        final errors = jsonBody['errors'];
        if (errors is Map && errors.isNotEmpty) {
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            message = firstError.first.toString();
          }
        }
        message ??= 'Validation failed.';
      } else {
        message = 'Server responded with code ${response.statusCode}.';
      }
    }

    return ApiResponse(
      isSuccess: isSuccess,
      statusCode: response.statusCode,
      data: jsonBody,
      message: message,
      rawJson: jsonBody,
    );
  }
}
