import 'dart:async';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'api_service.dart';
import 'database_service.dart';

/// Service for handling offline data synchronization
class SyncService {
  final ApiService _apiService;
  final DatabaseService _databaseService;
  final Connectivity _connectivity = Connectivity();

  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;
  bool _isSyncing = false;

  SyncService({
    required ApiService apiService,
    required DatabaseService databaseService,
  })  : _apiService = apiService,
        _databaseService = databaseService;

  /// Start listening for connectivity changes
  void startListening() {
    _connectivitySubscription = _connectivity.onConnectivityChanged.listen(
      (results) {
        if (_hasConnection(results)) {
          syncPendingData();
        }
      },
    );
  }

  /// Stop listening for connectivity changes
  void stopListening() {
    _connectivitySubscription?.cancel();
    _connectivitySubscription = null;
  }

  bool _hasConnection(List<ConnectivityResult> results) {
    return results.any((r) =>
        r == ConnectivityResult.wifi ||
        r == ConnectivityResult.mobile ||
        r == ConnectivityResult.ethernet);
  }

  /// Check if device is online
  Future<bool> isOnline() async {
    final results = await _connectivity.checkConnectivity();
    if (!_hasConnection(results)) return false;

    // Also verify API is reachable
    try {
      return await _apiService.ping();
    } catch (e) {
      return false;
    }
  }

  /// Sync all pending data
  Future<SyncResult> syncPendingData() async {
    if (_isSyncing) {
      return SyncResult(synced: 0, failed: 0, message: 'Sync already in progress');
    }

    _isSyncing = true;
    int synced = 0;
    int failed = 0;

    try {
      final isConnected = await isOnline();
      if (!isConnected) {
        return SyncResult(synced: 0, failed: 0, message: 'No connection');
      }

      // Get all pending scans grouped by session
      final pendingCount = await _databaseService.getPendingScansCount();
      if (pendingCount == 0) {
        return SyncResult(synced: 0, failed: 0, message: 'Nothing to sync');
      }

      // For now, we'll sync per session (you might want to batch this)
      // This is a simplified implementation
      final db = await _databaseService.database;
      final sessions = await db.rawQuery(
        'SELECT DISTINCT session_id FROM pending_scans WHERE synced = 0',
      );

      for (final session in sessions) {
        final sessionId = session['session_id'] as int;
        final scans = await _databaseService.getPendingScans(sessionId);

        if (scans.isEmpty) continue;

        try {
          final items = scans.map((scan) => {
            'item_id': scan['item_id'],
            'expected_quantity': scan['expected_quantity'],
            'counted_quantity': scan['counted_quantity'],
          }).toList();

          final result = await _apiService.syncOfflineItems(sessionId, items);

          if (result['success'] == true) {
            final ids = scans.map((s) => s['id'] as int).toList();
            await _databaseService.markScansAsSynced(ids);
            synced += scans.length;
          } else {
            failed += scans.length;
          }
        } catch (e) {
          failed += scans.length;
        }
      }

      return SyncResult(
        synced: synced,
        failed: failed,
        message: 'Sync completed',
      );
    } finally {
      _isSyncing = false;
    }
  }

  /// Sync categories from server to local cache
  Future<void> syncCategories() async {
    try {
      final categories = await _apiService.getCategories();
      await _databaseService.saveCategories(
        categories.cast<Map<String, dynamic>>(),
      );
    } catch (e) {
      // Use cached data
    }
  }

  /// Sync items from server to local cache
  Future<void> syncItems({
    String type = 'full',
    int? categoryId,
    int? days,
  }) async {
    try {
      final response = await _apiService.getItems(
        type: type,
        categoryId: categoryId,
        days: days,
        limit: 10000, // Get all items
      );
      final items = response['items'] as List;
      await _databaseService.saveItems(items.cast<Map<String, dynamic>>());
    } catch (e) {
      // Use cached data
    }
  }
}

/// Result of a sync operation
class SyncResult {
  final int synced;
  final int failed;
  final String message;

  SyncResult({
    required this.synced,
    required this.failed,
    required this.message,
  });

  bool get hasErrors => failed > 0;
  bool get isSuccess => synced > 0 && failed == 0;
}
