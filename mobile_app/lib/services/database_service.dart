import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:path/path.dart';
import 'package:sqflite/sqflite.dart';

/// Local SQLite database service for offline storage
/// On web, uses in-memory storage (no persistence)
class DatabaseService {
  static Database? _database;

  // In-memory storage for web
  final Map<String, List<Map<String, dynamic>>> _webStorage = {
    'categories': [],
    'items': [],
    'pending_scans': [],
  };
  int _webAutoIncrement = 1;

  Future<Database> get database async {
    if (kIsWeb) {
      throw UnsupportedError('SQLite not supported on web');
    }
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<void> initialize() async {
    if (!kIsWeb) {
      await database;
    }
    // On web, nothing to initialize
  }

  Future<Database> _initDatabase() async {
    final path = join(await getDatabasesPath(), 'wm_inventory.db');

    return openDatabase(
      path,
      version: 1,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // Categories cache
    await db.execute('''
      CREATE TABLE categories (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    // Items cache
    await db.execute('''
      CREATE TABLE items (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        item_number TEXT NOT NULL,
        quantity REAL NOT NULL,
        category_id INTEGER,
        category_name TEXT,
        cost_price REAL,
        unit_price REAL,
        last_inventory_date TEXT,
        updated_at TEXT NOT NULL
      )
    ''');

    // Pending scans (for offline mode)
    await db.execute('''
      CREATE TABLE pending_scans (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id INTEGER NOT NULL,
        item_id INTEGER NOT NULL,
        expected_quantity REAL NOT NULL,
        counted_quantity REAL NOT NULL,
        scanned_at TEXT NOT NULL,
        synced INTEGER DEFAULT 0
      )
    ''');

    // Create indexes
    await db.execute(
        'CREATE INDEX idx_items_number ON items(item_number)');
    await db.execute(
        'CREATE INDEX idx_pending_synced ON pending_scans(synced)');
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    // Handle database migrations here
  }

  // ==================== Categories ====================

  Future<void> saveCategories(List<Map<String, dynamic>> categories) async {
    if (kIsWeb) {
      final now = DateTime.now().toIso8601String();
      _webStorage['categories'] = categories.map((c) => {
        'id': c['id'],
        'name': c['name'],
        'updated_at': now,
      }).toList();
      return;
    }

    final db = await database;
    final batch = db.batch();

    // Clear existing
    batch.delete('categories');

    // Insert new
    final now = DateTime.now().toIso8601String();
    for (final category in categories) {
      batch.insert('categories', {
        'id': category['id'],
        'name': category['name'],
        'updated_at': now,
      });
    }

    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getCategories() async {
    if (kIsWeb) {
      return List.from(_webStorage['categories']!);
    }
    final db = await database;
    return db.query('categories', orderBy: 'name ASC');
  }

  // ==================== Items ====================

  Future<void> saveItems(List<Map<String, dynamic>> items) async {
    if (kIsWeb) {
      final now = DateTime.now().toIso8601String();
      for (final item in items) {
        final existing = _webStorage['items']!.indexWhere((i) => i['id'] == item['id']);
        final newItem = {
          'id': item['id'],
          'name': item['name'],
          'item_number': item['item_number'],
          'quantity': item['quantity'],
          'category_id': item['category_id'],
          'category_name': item['category_name'],
          'cost_price': item['cost_price'],
          'unit_price': item['unit_price'],
          'last_inventory_date': item['last_inventory_date'],
          'updated_at': now,
        };
        if (existing >= 0) {
          _webStorage['items']![existing] = newItem;
        } else {
          _webStorage['items']!.add(newItem);
        }
      }
      return;
    }

    final db = await database;
    final batch = db.batch();

    final now = DateTime.now().toIso8601String();
    for (final item in items) {
      batch.insert(
        'items',
        {
          'id': item['id'],
          'name': item['name'],
          'item_number': item['item_number'],
          'quantity': item['quantity'],
          'category_id': item['category_id'],
          'category_name': item['category_name'],
          'cost_price': item['cost_price'],
          'unit_price': item['unit_price'],
          'last_inventory_date': item['last_inventory_date'],
          'updated_at': now,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }

    await batch.commit(noResult: true);
  }

  Future<Map<String, dynamic>?> getItemByBarcode(String barcode) async {
    if (kIsWeb) {
      try {
        return _webStorage['items']!.firstWhere(
          (i) => i['item_number'] == barcode,
        );
      } catch (e) {
        return null;
      }
    }

    final db = await database;
    final results = await db.query(
      'items',
      where: 'item_number = ?',
      whereArgs: [barcode],
      limit: 1,
    );

    if (results.isNotEmpty) {
      return results.first;
    }
    return null;
  }

  Future<List<Map<String, dynamic>>> searchItems(String query) async {
    if (kIsWeb) {
      final lowerQuery = query.toLowerCase();
      return _webStorage['items']!.where((i) =>
        (i['name'] as String).toLowerCase().contains(lowerQuery) ||
        (i['item_number'] as String).toLowerCase().contains(lowerQuery)
      ).take(50).toList();
    }

    final db = await database;
    return db.query(
      'items',
      where: 'name LIKE ? OR item_number LIKE ?',
      whereArgs: ['%$query%', '%$query%'],
      orderBy: 'name ASC',
      limit: 50,
    );
  }

  // ==================== Pending Scans ====================

  Future<int> savePendingScan({
    required int sessionId,
    required int itemId,
    required double expectedQuantity,
    required double countedQuantity,
  }) async {
    if (kIsWeb) {
      final id = _webAutoIncrement++;
      _webStorage['pending_scans']!.add({
        'id': id,
        'session_id': sessionId,
        'item_id': itemId,
        'expected_quantity': expectedQuantity,
        'counted_quantity': countedQuantity,
        'scanned_at': DateTime.now().toIso8601String(),
        'synced': 0,
      });
      return id;
    }

    final db = await database;
    return db.insert('pending_scans', {
      'session_id': sessionId,
      'item_id': itemId,
      'expected_quantity': expectedQuantity,
      'counted_quantity': countedQuantity,
      'scanned_at': DateTime.now().toIso8601String(),
      'synced': 0,
    });
  }

  Future<List<Map<String, dynamic>>> getPendingScans(int sessionId) async {
    if (kIsWeb) {
      return _webStorage['pending_scans']!.where((s) =>
        s['session_id'] == sessionId && s['synced'] == 0
      ).toList();
    }

    final db = await database;
    return db.query(
      'pending_scans',
      where: 'session_id = ? AND synced = 0',
      whereArgs: [sessionId],
      orderBy: 'scanned_at ASC',
    );
  }

  Future<void> markScansAsSynced(List<int> ids) async {
    if (kIsWeb) {
      for (final scan in _webStorage['pending_scans']!) {
        if (ids.contains(scan['id'])) {
          scan['synced'] = 1;
        }
      }
      return;
    }

    final db = await database;
    await db.update(
      'pending_scans',
      {'synced': 1},
      where: 'id IN (${ids.join(',')})',
    );
  }

  Future<int> getPendingScansCount() async {
    if (kIsWeb) {
      return _webStorage['pending_scans']!.where((s) => s['synced'] == 0).length;
    }

    final db = await database;
    final result = await db.rawQuery(
      'SELECT COUNT(*) as count FROM pending_scans WHERE synced = 0',
    );
    return result.first['count'] as int;
  }

  // ==================== Cleanup ====================

  Future<void> clearCache() async {
    if (kIsWeb) {
      _webStorage['categories']!.clear();
      _webStorage['items']!.clear();
      return;
    }

    final db = await database;
    await db.delete('categories');
    await db.delete('items');
  }

  Future<void> clearSyncedScans() async {
    if (kIsWeb) {
      _webStorage['pending_scans']!.removeWhere((s) => s['synced'] == 1);
      return;
    }

    final db = await database;
    await db.delete('pending_scans', where: 'synced = 1');
  }
}
