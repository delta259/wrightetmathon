import 'package:equatable/equatable.dart';

/// Item model representing a product in inventory
class Item extends Equatable {
  final int id;
  final String name;
  final String itemNumber; // Barcode/UPC
  final double quantity;
  final int? categoryId;
  final String? categoryName;
  final double? costPrice;
  final double? unitPrice;
  final DateTime? lastInventoryDate;
  final bool? counted;
  final double? countedQuantity;

  const Item({
    required this.id,
    required this.name,
    required this.itemNumber,
    required this.quantity,
    this.categoryId,
    this.categoryName,
    this.costPrice,
    this.unitPrice,
    this.lastInventoryDate,
    this.counted,
    this.countedQuantity,
  });

  factory Item.fromJson(Map<String, dynamic> json) {
    return Item(
      id: json['id'] as int,
      name: json['name'] as String,
      itemNumber: json['item_number'] as String,
      quantity: (json['quantity'] as num).toDouble(),
      categoryId: json['category_id'] as int?,
      categoryName: json['category_name'] as String?,
      costPrice: json['cost_price'] != null
          ? (json['cost_price'] as num).toDouble()
          : null,
      unitPrice: json['unit_price'] != null
          ? (json['unit_price'] as num).toDouble()
          : null,
      lastInventoryDate: json['last_inventory_date'] != null
          ? DateTime.tryParse(json['last_inventory_date'] as String)
          : null,
      counted: json['counted'] as bool?,
      countedQuantity: json['counted_quantity'] != null
          ? (json['counted_quantity'] as num).toDouble()
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'item_number': itemNumber,
      'quantity': quantity,
      'category_id': categoryId,
      'category_name': categoryName,
      'cost_price': costPrice,
      'unit_price': unitPrice,
      'last_inventory_date': lastInventoryDate?.toIso8601String(),
    };
  }

  Item copyWith({
    int? id,
    String? name,
    String? itemNumber,
    double? quantity,
    int? categoryId,
    String? categoryName,
    double? costPrice,
    double? unitPrice,
    DateTime? lastInventoryDate,
  }) {
    return Item(
      id: id ?? this.id,
      name: name ?? this.name,
      itemNumber: itemNumber ?? this.itemNumber,
      quantity: quantity ?? this.quantity,
      categoryId: categoryId ?? this.categoryId,
      categoryName: categoryName ?? this.categoryName,
      costPrice: costPrice ?? this.costPrice,
      unitPrice: unitPrice ?? this.unitPrice,
      lastInventoryDate: lastInventoryDate ?? this.lastInventoryDate,
    );
  }

  @override
  List<Object?> get props => [id, itemNumber];
}
