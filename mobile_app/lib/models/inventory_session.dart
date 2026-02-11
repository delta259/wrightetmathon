import 'package:equatable/equatable.dart';

/// Inventory session type enumeration
enum SessionType {
  full,
  partial,
  rolling,
  rollingCategory,
  rollingDate,
}

extension SessionTypeExtension on SessionType {
  String get apiValue {
    switch (this) {
      case SessionType.full:
        return 'full';
      case SessionType.partial:
        return 'partial';
      case SessionType.rolling:
        return 'rolling';
      case SessionType.rollingCategory:
        return 'rolling_category';
      case SessionType.rollingDate:
        return 'rolling_date';
    }
  }

  String get displayName {
    switch (this) {
      case SessionType.full:
        return 'Inventaire complet';
      case SessionType.partial:
        return 'Inventaire partiel';
      case SessionType.rolling:
        return 'Inventaire tournant';
      case SessionType.rollingCategory:
        return 'Par catégorie';
      case SessionType.rollingDate:
        return 'Par date';
    }
  }

  static SessionType fromString(String value) {
    switch (value) {
      case 'full':
        return SessionType.full;
      case 'partial':
        return SessionType.partial;
      case 'rolling':
        return SessionType.rolling;
      case 'rolling_category':
        return SessionType.rollingCategory;
      case 'rolling_date':
        return SessionType.rollingDate;
      default:
        return SessionType.full;
    }
  }
}

/// Inventory session status
enum SessionStatus {
  inProgress,
  completed,
  cancelled,
}

extension SessionStatusExtension on SessionStatus {
  String get apiValue {
    switch (this) {
      case SessionStatus.inProgress:
        return 'in_progress';
      case SessionStatus.completed:
        return 'completed';
      case SessionStatus.cancelled:
        return 'cancelled';
    }
  }

  static SessionStatus fromString(String value) {
    switch (value) {
      case 'in_progress':
        return SessionStatus.inProgress;
      case 'completed':
        return SessionStatus.completed;
      case 'cancelled':
        return SessionStatus.cancelled;
      default:
        return SessionStatus.inProgress;
    }
  }
}

/// Inventory session model
class InventorySession extends Equatable {
  final int id;
  final SessionType type;
  final int? categoryId;
  final String? categoryName;
  final SessionStatus status;
  final int totalItems;
  final int itemsCounted;
  final DateTime startedAt;
  final DateTime? completedAt;
  final String? notes;

  const InventorySession({
    required this.id,
    required this.type,
    this.categoryId,
    this.categoryName,
    required this.status,
    required this.totalItems,
    required this.itemsCounted,
    required this.startedAt,
    this.completedAt,
    this.notes,
  });

  double get progressPercentage {
    if (totalItems == 0) return 0;
    return (itemsCounted / totalItems) * 100;
  }

  bool get isComplete => status == SessionStatus.completed;

  factory InventorySession.fromJson(Map<String, dynamic> json) {
    return InventorySession(
      id: json['id'] as int,
      type: SessionTypeExtension.fromString(json['type'] as String),
      categoryId: json['category_id'] as int?,
      categoryName: json['category_name'] as String?,
      status: SessionStatusExtension.fromString(json['status'] as String),
      totalItems: json['total_items'] as int,
      itemsCounted: json['items_counted'] as int,
      startedAt: DateTime.parse(json['started_at'] as String),
      completedAt: json['completed_at'] != null
          ? DateTime.parse(json['completed_at'] as String)
          : null,
      notes: json['notes'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type.apiValue,
      'category_id': categoryId,
      'category_name': categoryName,
      'status': status.apiValue,
      'total_items': totalItems,
      'items_counted': itemsCounted,
      'started_at': startedAt.toIso8601String(),
      'completed_at': completedAt?.toIso8601String(),
      'notes': notes,
    };
  }

  @override
  List<Object?> get props => [id];
}

/// Scanned item in a session
class SessionItem extends Equatable {
  final int id;
  final int itemId;
  final String itemName;
  final String itemNumber;
  final double expectedQuantity;
  final double countedQuantity;
  final double variance;
  final DateTime scannedAt;
  final DateTime? countedAt;
  final bool synced;

  const SessionItem({
    required this.id,
    required this.itemId,
    required this.itemName,
    required this.itemNumber,
    required this.expectedQuantity,
    required this.countedQuantity,
    required this.variance,
    required this.scannedAt,
    this.countedAt,
    this.synced = true,
  });

  bool get isCounted => countedAt != null;
  bool get hasVariance => variance != 0;
  bool get isOver => variance > 0;
  bool get isUnder => variance < 0;

  factory SessionItem.fromJson(Map<String, dynamic> json) {
    return SessionItem(
      id: json['id'] as int,
      itemId: json['item_id'] as int,
      itemName: json['item_name'] as String,
      itemNumber: json['item_number'] as String,
      expectedQuantity: (json['expected_quantity'] as num).toDouble(),
      countedQuantity: (json['counted_quantity'] as num).toDouble(),
      variance: (json['variance'] as num).toDouble(),
      scannedAt: DateTime.parse(json['scanned_at'] as String),
      countedAt: json['counted_at'] != null
          ? DateTime.tryParse(json['counted_at'] as String)
          : null,
      synced: json['synced'] as bool? ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'item_id': itemId,
      'item_name': itemName,
      'item_number': itemNumber,
      'expected_quantity': expectedQuantity,
      'counted_quantity': countedQuantity,
      'variance': variance,
      'scanned_at': scannedAt.toIso8601String(),
      'synced': synced,
    };
  }

  @override
  List<Object?> get props => [id, itemId];
}
