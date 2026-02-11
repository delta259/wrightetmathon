/// API Configuration
///
/// Configure the base URL to point to your Wright et Mathon POS server.
/// For development, use your local IP address or ngrok URL.
class ApiConfig {
  // Base URL for the API - CHANGE THIS to your server address
  // Examples:
  // - Local development: 'http://192.168.1.100/wrightetmathon/index.php'
  // - Production: 'https://your-domain.com/wrightetmathon/index.php'
  static const String baseUrl = 'http://192.168.1.19/wrightetmathon/index.php';

  // API endpoints
  static const String login = '/api_mobile/login';
  static const String logout = '/api_mobile/logout';
  static const String ping = '/api_mobile/ping';
  static const String categories = '/api_mobile/categories';
  static const String items = '/api_mobile/items';
  static const String itemById = '/api_mobile/item';
  static const String itemByBarcode = '/api_mobile/item_by_barcode';
  static const String activeSession = '/api_mobile/active_session';
  static const String sessions = '/api_mobile/sessions';
  static const String session = '/api_mobile/session';
  static const String sync = '/api_mobile/sync';

  // Timeouts
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);

  // Retry configuration
  static const int maxRetries = 3;
  static const Duration retryDelay = Duration(seconds: 2);
}
