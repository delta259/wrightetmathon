import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../config/app_theme.dart';
import '../models/item.dart';

class QuantityInputDialog extends StatefulWidget {
  final Item item;
  final Function(double) onConfirm;

  const QuantityInputDialog({
    super.key,
    required this.item,
    required this.onConfirm,
  });

  @override
  State<QuantityInputDialog> createState() => _QuantityInputDialogState();
}

class _QuantityInputDialogState extends State<QuantityInputDialog> {
  late TextEditingController _controller;
  double _quantity = 0;

  @override
  void initState() {
    super.initState();
    _quantity = widget.item.quantity;
    _controller = TextEditingController(text: _quantity.toString());
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _updateQuantity(double delta) {
    setState(() {
      _quantity = (_quantity + delta).clamp(0, 99999);
      _controller.text = _quantity.toString();
    });
    HapticFeedback.lightImpact();
  }

  double get _variance => _quantity - widget.item.quantity;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Item info
            Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryBlue.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.inventory_2,
                    color: AppTheme.primaryBlue,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.item.name,
                        style: Theme.of(context).textTheme.titleMedium,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        widget.item.itemNumber,
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Expected quantity
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'Stock théorique: ',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                Text(
                  '${widget.item.quantity}',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: AppTheme.primaryBlue,
                      ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Quantity input
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Minus button
                _buildQuantityButton(
                  icon: Icons.remove,
                  onPressed: () => _updateQuantity(-1),
                  onLongPress: () => _updateQuantity(-10),
                ),
                const SizedBox(width: 16),

                // Quantity field
                SizedBox(
                  width: 120,
                  child: TextField(
                    controller: _controller,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                    ),
                    keyboardType:
                        const TextInputType.numberWithOptions(decimal: true),
                    inputFormatters: [
                      FilteringTextInputFormatter.allow(RegExp(r'[\d.]')),
                    ],
                    decoration: const InputDecoration(
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.zero,
                    ),
                    onChanged: (value) {
                      setState(() {
                        _quantity = double.tryParse(value) ?? 0;
                      });
                    },
                  ),
                ),

                const SizedBox(width: 16),

                // Plus button
                _buildQuantityButton(
                  icon: Icons.add,
                  onPressed: () => _updateQuantity(1),
                  onLongPress: () => _updateQuantity(10),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Variance indicator
            if (_variance != 0)
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: _variance > 0
                      ? AppTheme.success.withOpacity(0.1)
                      : AppTheme.danger.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  'Écart: ${_variance > 0 ? '+' : ''}${_variance.toStringAsFixed(0)}',
                  style: TextStyle(
                    color: _variance > 0 ? AppTheme.success : AppTheme.danger,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            const SizedBox(height: 24),

            // Quick quantity buttons
            Wrap(
              spacing: 8,
              runSpacing: 8,
              alignment: WrapAlignment.center,
              children: [0, 1, 5, 10, 20, 50].map((qty) {
                return _buildQuickQuantityButton(qty.toDouble());
              }).toList(),
            ),
            const SizedBox(height: 24),

            // Action buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(0, 48),
                    ),
                    child: const Text('Annuler', maxLines: 1),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(context);
                      widget.onConfirm(_quantity);
                    },
                    style: ElevatedButton.styleFrom(
                      minimumSize: const Size(0, 48),
                    ),
                    child: const Text('Valider', maxLines: 1),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuantityButton({
    required IconData icon,
    required VoidCallback onPressed,
    required VoidCallback onLongPress,
  }) {
    return GestureDetector(
      onLongPress: onLongPress,
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          padding: EdgeInsets.zero,
          minimumSize: const Size(56, 56),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: Icon(icon, size: 28),
      ),
    );
  }

  Widget _buildQuickQuantityButton(double qty) {
    final isSelected = _quantity == qty;
    return OutlinedButton(
      onPressed: () {
        setState(() {
          _quantity = qty;
          _controller.text = qty.toString();
        });
        HapticFeedback.selectionClick();
      },
      style: OutlinedButton.styleFrom(
        backgroundColor:
            isSelected ? AppTheme.primaryBlue.withOpacity(0.1) : null,
        side: BorderSide(
          color: isSelected ? AppTheme.primaryBlue : AppTheme.lightBorder,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      ),
      child: Text(
        qty.toStringAsFixed(0),
        style: TextStyle(
          color: isSelected ? AppTheme.primaryBlue : null,
        ),
      ),
    );
  }
}
