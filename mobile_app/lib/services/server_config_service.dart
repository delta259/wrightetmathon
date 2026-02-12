import 'package:shared_preferences/shared_preferences.dart';

/// Service for managing server URL configuration.
/// The user provides only the server IP/hostname, and the full URL
/// is constructed automatically: http://{ip}/wrightetmathon/index.php
class ServerConfigService {
  static const String _serverIpKey = 'wm_server_ip';
  static const String _basePath = '/wrightetmathon/index.php';
  static SharedPreferences? _prefs;

  /// Initialize SharedPreferences instance
  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  /// Get the saved server IP/hostname, or null if not configured
  static String? getServerIp() {
    return _prefs?.getString(_serverIpKey);
  }

  /// Get the full server URL built from the saved IP
  /// Returns null if no IP is configured
  static String? getServerUrl() {
    final ip = getServerIp();
    if (ip == null || ip.isEmpty) return null;
    return 'http://$ip$_basePath';
  }

  /// Save the server IP/hostname
  static Future<bool> saveServerIp(String ip) async {
    if (_prefs == null) return false;
    String normalized = ip.trim();
    // Remove http:// or https:// prefix if user typed it
    normalized = normalized.replaceFirst(RegExp(r'^https?://'), '');
    // Remove trailing slash or path
    normalized = normalized.split('/').first;
    return _prefs!.setString(_serverIpKey, normalized);
  }

  /// Check if a server IP has been configured
  static bool isConfigured() {
    final ip = getServerIp();
    return ip != null && ip.isNotEmpty;
  }

  /// Clear the saved server IP
  static Future<bool> clearServerUrl() async {
    if (_prefs == null) return false;
    return _prefs!.remove(_serverIpKey);
  }
}
