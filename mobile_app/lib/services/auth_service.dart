import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import 'api_service.dart';

/// Authentication service handling login, logout, and token management
/// Uses SharedPreferences instead of FlutterSecureStorage for web compatibility
/// (window.crypto.subtle requires HTTPS, SharedPreferences uses localStorage on web)
class AuthService {
  final ApiService _apiService;
  SharedPreferences? _prefs;

  static const String _userKey = 'wm_user';
  static const String _tokenKey = 'wm_token';

  User? _currentUser;

  AuthService({required ApiService apiService}) : _apiService = apiService;

  /// Get current user
  User? get currentUser => _currentUser;

  /// Check if user is logged in
  bool get isLoggedIn => _currentUser != null && !_currentUser!.isTokenExpired;

  /// Ensure SharedPreferences is initialized
  Future<SharedPreferences> _getPrefs() async {
    _prefs ??= await SharedPreferences.getInstance();
    return _prefs!;
  }

  /// Initialize auth state from storage
  Future<User?> initialize() async {
    try {
      final prefs = await _getPrefs();
      final userJson = prefs.getString(_userKey);
      if (userJson != null) {
        final userData = json.decode(userJson) as Map<String, dynamic>;
        _currentUser = User.fromJson(userData);

        if (!_currentUser!.isTokenExpired) {
          _apiService.setToken(_currentUser!.token);
          return _currentUser;
        } else {
          // Token expired, clear storage
          await logout();
        }
      }
    } catch (e) {
      await logout();
    }
    return null;
  }

  /// Login with username and password
  Future<User> login(String username, String password) async {
    final response = await _apiService.login(
      username,
      password,
      deviceInfo: 'Flutter Mobile App',
    );

    if (response['success'] != true) {
      throw Exception(response['error'] ?? 'Login failed');
    }

    _currentUser = User.fromJson(response);
    _apiService.setToken(_currentUser!.token);

    // Save to storage
    final prefs = await _getPrefs();
    await prefs.setString(_userKey, json.encode(_currentUser!.toJson()));
    await prefs.setString(_tokenKey, _currentUser!.token);

    return _currentUser!;
  }

  /// Logout and clear session
  Future<void> logout() async {
    try {
      if (_currentUser != null) {
        await _apiService.logout();
      }
    } catch (e) {
      // Ignore errors during logout
    }

    _currentUser = null;
    _apiService.clearToken();

    final prefs = await _getPrefs();
    await prefs.remove(_userKey);
    await prefs.remove(_tokenKey);
  }

  /// Refresh token if needed
  Future<bool> refreshTokenIfNeeded() async {
    if (_currentUser == null) return false;

    // Token expires in less than 1 hour - refresh
    final expiresIn = _currentUser!.expiresAt.difference(DateTime.now());
    if (expiresIn.inHours < 1) {
      try {
        // Re-login to get new token
        // Note: In a real app, you'd have a refresh token endpoint
        await logout();
        return false;
      } catch (e) {
        return false;
      }
    }

    return true;
  }
}
