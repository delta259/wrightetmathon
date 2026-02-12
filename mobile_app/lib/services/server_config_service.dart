import 'package:shared_preferences/shared_preferences.dart';

/// Service for managing server URL configuration.
/// Uses SharedPreferences to persist the server URL across app restarts.
class ServerConfigService {
  static const String _serverUrlKey = 'wm_server_url';
  static SharedPreferences? _prefs;

  /// Initialize SharedPreferences instance
  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  /// Get the saved server URL, or null if not configured
  static String? getServerUrl() {
    return _prefs?.getString(_serverUrlKey);
  }

  /// Save the server URL after normalization
  static Future<bool> saveServerUrl(String url) async {
    if (_prefs == null) return false;
    // Normalize: trim whitespace, remove trailing slash
    String normalized = url.trim();
    if (normalized.endsWith('/')) {
      normalized = normalized.substring(0, normalized.length - 1);
    }
    return _prefs!.setString(_serverUrlKey, normalized);
  }

  /// Check if a server URL has been configured
  static bool isConfigured() {
    final url = getServerUrl();
    return url != null && url.isNotEmpty;
  }

  /// Clear the saved server URL
  static Future<bool> clearServerUrl() async {
    if (_prefs == null) return false;
    return _prefs!.remove(_serverUrlKey);
  }
}
