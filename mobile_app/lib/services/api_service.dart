import 'package:dio/dio.dart';
import '../config/api_config.dart';

/// API Service for communicating with the Wright et Mathon POS server
class ApiService {
  final Dio _dio;
  String? _token;

  ApiService({required String baseUrl}) : _dio = Dio() {
    _dio.options.baseUrl = baseUrl;
    _dio.options.connectTimeout = ApiConfig.connectTimeout;
    _dio.options.receiveTimeout = ApiConfig.receiveTimeout;
    _dio.options.headers['Content-Type'] = 'application/json';

    // Add logging interceptor for debugging
    _dio.interceptors.add(LogInterceptor(
      requestBody: true,
      responseBody: true,
      error: true,
    ));

    // Add auth interceptor
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        if (_token != null) {
          options.headers['Authorization'] = 'Bearer $_token';
        }
        handler.next(options);
      },
      onError: (error, handler) {
        if (error.response?.statusCode == 401) {
          // Token expired or invalid
          _token = null;
        }
        handler.next(error);
      },
    ));
  }

  /// Set the authentication token
  void setToken(String? token) {
    _token = token;
  }

  /// Clear the authentication token
  void clearToken() {
    _token = null;
  }

  /// Check if server is reachable
  Future<bool> ping() async {
    try {
      final response = await _dio.get(ApiConfig.ping);
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  /// Login and get JWT token
  Future<Map<String, dynamic>> login(
    String username,
    String password, {
    String? deviceInfo,
  }) async {
    final response = await _dio.post(
      ApiConfig.login,
      data: {
        'username': username,
        'password': password,
        if (deviceInfo != null) 'device_info': deviceInfo,
      },
    );
    return response.data;
  }

  /// Logout and revoke token
  Future<void> logout() async {
    await _dio.post(ApiConfig.logout);
    clearToken();
  }

  /// Get all categories
  Future<List<dynamic>> getCategories() async {
    final response = await _dio.get(ApiConfig.categories);
    return response.data['categories'] as List;
  }

  /// Get items for inventory
  Future<Map<String, dynamic>> getItems({
    String type = 'full',
    int? categoryId,
    int? days,
    String? search,
    int? sessionId,
    int limit = 100,
    int offset = 0,
  }) async {
    final response = await _dio.get(
      ApiConfig.items,
      queryParameters: {
        'type': type,
        if (categoryId != null) 'category_id': categoryId,
        if (days != null) 'days': days,
        if (search != null) 'search': search,
        if (sessionId != null) 'session_id': sessionId,
        'limit': limit,
        'offset': offset,
      },
    );
    return response.data;
  }

  /// Get item by ID
  Future<Map<String, dynamic>> getItemById(int itemId) async {
    final response = await _dio.get('${ApiConfig.itemById}/$itemId');
    return response.data['item'];
  }

  /// Get item by barcode
  Future<Map<String, dynamic>?> getItemByBarcode(String barcode) async {
    try {
      final response = await _dio.get('${ApiConfig.itemByBarcode}/$barcode');
      return response.data['item'];
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        return null;
      }
      rethrow;
    }
  }

  /// Get active session for the branch
  Future<Map<String, dynamic>?> getActiveSession() async {
    final response = await _dio.get(ApiConfig.activeSession);
    return response.data['session'];
  }

  /// Get all sessions
  Future<List<dynamic>> getSessions({String? status}) async {
    final response = await _dio.get(
      ApiConfig.sessions,
      queryParameters: {
        if (status != null) 'status': status,
      },
    );
    return response.data['sessions'] as List;
  }

  /// Create new session
  Future<Map<String, dynamic>> createSession({
    required String type,
    int? categoryId,
    int? daysThreshold,
    String? notes,
  }) async {
    final response = await _dio.post(
      ApiConfig.sessions,
      data: {
        'type': type,
        if (categoryId != null) 'category_id': categoryId,
        if (daysThreshold != null) 'days_threshold': daysThreshold,
        if (notes != null) 'notes': notes,
      },
    );
    return response.data['session'];
  }

  /// Get session details
  Future<Map<String, dynamic>> getSession(int sessionId) async {
    final response = await _dio.get('${ApiConfig.session}/$sessionId');
    return response.data;
  }

  /// Add item to session
  Future<Map<String, dynamic>> addSessionItem(
    int sessionId,
    int itemId,
    double countedQuantity,
  ) async {
    final response = await _dio.post(
      '${ApiConfig.session}/$sessionId/item',
      data: {
        'item_id': itemId,
        'counted_quantity': countedQuantity,
      },
    );
    return response.data;
  }

  /// Complete session
  Future<Map<String, dynamic>> completeSession(int sessionId) async {
    final response = await _dio.post(
      '${ApiConfig.session}/$sessionId/complete',
    );
    return response.data;
  }

  /// Cancel session
  Future<Map<String, dynamic>> cancelSession(int sessionId) async {
    final response = await _dio.post(
      '${ApiConfig.session}/$sessionId/cancel',
    );
    return response.data;
  }

  /// Sync offline items
  Future<Map<String, dynamic>> syncOfflineItems(
    int sessionId,
    List<Map<String, dynamic>> items,
  ) async {
    final response = await _dio.post(
      ApiConfig.sync,
      data: {
        'session_id': sessionId,
        'items': items,
      },
    );
    return response.data;
  }
}
