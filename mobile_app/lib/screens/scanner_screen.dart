import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../config/app_theme.dart';
import '../models/inventory_session.dart';
import '../models/item.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import '../widgets/quantity_input_dialog.dart';
import '../widgets/web_barcode_scanner.dart';

class ScannerScreen extends StatefulWidget {
  final InventorySession session;

  const ScannerScreen({
    super.key,
    required this.session,
  });

  @override
  State<ScannerScreen> createState() => _ScannerScreenState();
}

class _ScannerScreenState extends State<ScannerScreen>
    with SingleTickerProviderStateMixin {
  late InventorySession _session;
  // Only create native scanner controller on non-web platforms
  MobileScannerController? _scannerController;
  final TextEditingController _searchController = TextEditingController();

  List<SessionItem> _scannedItems = [];
  bool _isProcessing = false;
  bool _showScanner = true;
  String? _lastScannedCode;

  // List view filters & sorting
  String _sortBy = 'name'; // 'name' or 'reference'
  bool _showCountedOnly = false; // false = non comptés, true = comptés

  @override
  void initState() {
    super.initState();
    _session = widget.session;
    if (!kIsWeb) {
      _scannerController = MobileScannerController();
    }
    _loadSessionItems();
  }

  @override
  void dispose() {
    _scannerController?.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadSessionItems() async {
    try {
      final apiService = context.read<ApiService>();
      final response = await apiService.getSession(_session.id);

      // Update session data (items_counted, etc.)
      final sessionJson = response['session'] as Map<String, dynamic>;
      _session = InventorySession.fromJson(sessionJson);

      final items = (response['items'] as List)
          .map((json) => SessionItem.fromJson(json as Map<String, dynamic>))
          .toList();
      setState(() {
        _scannedItems = items;
      });
    } catch (e) {
      // Load from local cache if offline
    }
  }

  void _onBarcodeDetected(BarcodeCapture capture) async {
    if (_isProcessing) return;

    final barcode = capture.barcodes.firstOrNull?.rawValue;
    if (barcode == null || barcode == _lastScannedCode) return;

    setState(() {
      _isProcessing = true;
      _lastScannedCode = barcode;
    });

    // Vibration feedback
    HapticFeedback.mediumImpact();

    await _processBarcode(barcode);

    // Reset after a delay to allow scanning the same code again
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        setState(() {
          _lastScannedCode = null;
          _isProcessing = false;
        });
      }
    });
  }

  Future<void> _processBarcode(String barcode) async {
    // Show loading spinner while looking up the item
    if (mounted) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => AlertDialog(
          content: Row(
            children: [
              const CircularProgressIndicator(),
              const SizedBox(width: 20),
              Expanded(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Recherche en cours...', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Text(barcode, style: TextStyle(color: Colors.grey[600], fontFamily: 'monospace')),
                  ],
                ),
              ),
            ],
          ),
        ),
      );
    }

    try {
      final apiService = context.read<ApiService>();
      final itemJson = await apiService.getItemByBarcode(barcode);

      // Close loading spinner
      if (mounted) Navigator.of(context).pop();

      if (itemJson == null) {
        _showNotFoundDialog(barcode);
        return;
      }

      final item = Item.fromJson(itemJson);
      _showQuantityDialog(item);
    } catch (e) {
      // Close loading spinner
      if (mounted) Navigator.of(context).pop();

      // Try offline lookup
      final databaseService = context.read<DatabaseService>();
      final cachedItem = await databaseService.getItemByBarcode(barcode);

      if (cachedItem != null) {
        final item = Item.fromJson(cachedItem);
        _showQuantityDialog(item);
      } else {
        _showNotFoundDialog(barcode);
      }
    }
  }

  void _showNotFoundDialog(String barcode) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.warning, color: AppTheme.warning),
            SizedBox(width: 8),
            Text('Article non trouvé'),
          ],
        ),
        content: Text('Aucun article avec le code-barres:\n$barcode'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    ).then((_) {
      // Re-show scanner overlay on web after dialog closes
      if (kIsWeb) WebBarcodeScanner.showOverlay();
    });
  }

  void _showQuantityDialog(Item item) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => QuantityInputDialog(
        item: item,
        onConfirm: (quantity) => _saveItemCount(item, quantity),
      ),
    ).then((_) {
      // Re-show scanner overlay on web after quantity dialog closes
      if (kIsWeb) WebBarcodeScanner.showOverlay();
    });
  }

  Future<void> _saveItemCount(Item item, double quantity) async {
    try {
      final apiService = context.read<ApiService>();
      await apiService.addSessionItem(_session.id, item.id, quantity);

      // Reload session items
      await _loadSessionItems();

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${item.name} : $quantity unités'),
            backgroundColor: AppTheme.success,
            duration: const Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      // Save offline
      final databaseService = context.read<DatabaseService>();
      await databaseService.savePendingScan(
        sessionId: _session.id,
        itemId: item.id,
        expectedQuantity: item.quantity,
        countedQuantity: quantity,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${item.name} enregistré (hors ligne)'),
            backgroundColor: AppTheme.warning,
          ),
        );
      }
    }
  }

  void _showManualSearch() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => _ManualSearchSheet(
        sessionId: _session.id,
        onItemSelected: (item) {
          Navigator.pop(context);
          _showQuantityDialog(item);
        },
      ),
    );
  }

  void _completeSession() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Terminer l\'inventaire'),
        content: Text(
          'Vous avez scanné ${_scannedItems.length} articles.\n'
          'Voulez-vous vraiment terminer et appliquer les ajustements ?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Terminer'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        final apiService = context.read<ApiService>();
        await apiService.completeSession(_session.id);

        if (mounted) {
          Navigator.of(context).popUntil((route) => route.isFirst);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Inventaire terminé avec succès'),
              backgroundColor: AppTheme.success,
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Erreur: ${e.toString()}'),
              backgroundColor: AppTheme.danger,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_session.type.displayName),
        actions: [
          IconButton(
            icon: Icon(_showScanner ? Icons.list : Icons.qr_code_scanner),
            onPressed: () {
              setState(() {
                _showScanner = !_showScanner;
              });
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Progress indicator
          _buildProgressBar(),

          // Scanner or list view
          Expanded(
            child: _showScanner ? _buildScannerView() : _buildListView(),
          ),
        ],
      ),
      floatingActionButton: (kIsWeb && _showScanner) ? null : FloatingActionButton.extended(
        onPressed: _showManualSearch,
        icon: const Icon(Icons.search),
        label: const Text('Rechercher'),
      ),
    );
  }

  Widget _buildProgressBar() {
    final counted = _session.itemsCounted;
    final total = _session.totalItems;
    final progress = total > 0 ? counted / total : 0.0;

    return Container(
      padding: const EdgeInsets.all(16),
      color: Theme.of(context).cardColor,
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '$counted / $total articles comptés',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              if (total > 0)
                Text(
                  '${(progress * 100).toStringAsFixed(0)}%',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: AppTheme.primaryBlue,
                      ),
                ),
            ],
          ),
          if (total > 0) ...[
            const SizedBox(height: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: progress,
                minHeight: 8,
                backgroundColor: AppTheme.lightBorder,
                valueColor: AlwaysStoppedAnimation<Color>(
                  progress >= 1.0 ? AppTheme.success : AppTheme.primaryBlue,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  /// Handle barcode detected from web scanner
  void _onWebBarcodeDetected(String barcode) {
    if (_isProcessing) return;
    if (barcode == _lastScannedCode) return;

    setState(() {
      _isProcessing = true;
      _lastScannedCode = barcode;
    });

    HapticFeedback.mediumImpact();

    // On web: hide scanner overlay to show Flutter quantity dialog
    if (kIsWeb) {
      WebBarcodeScanner.hideOverlay();
    }

    _processBarcode(barcode);

    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        setState(() {
          _lastScannedCode = null;
          _isProcessing = false;
        });
      }
    });
  }

  /// Handle search request from web scanner overlay
  void _onWebSearchRequested() {
    if (kIsWeb) WebBarcodeScanner.hideOverlay();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => _ManualSearchSheet(
        sessionId: _session.id,
        onItemSelected: (item) {
          Navigator.pop(context, item);
        },
      ),
    ).then((result) {
      if (result != null && result is Item) {
        _showQuantityDialog(result);
      } else {
        // Dismissed without selection — resume scanner
        if (kIsWeb) WebBarcodeScanner.showOverlay();
      }
    });
  }

  Widget _buildScannerView() {
    // On web, use html5-qrcode based scanner
    if (kIsWeb) {
      return WebBarcodeScanner(
        onDetect: _onWebBarcodeDetected,
        onSearch: _onWebSearchRequested,
      );
    }

    // On native, use mobile_scanner
    return Stack(
      children: [
        MobileScanner(
          controller: _scannerController!,
          onDetect: _onBarcodeDetected,
        ),
        // Scan overlay
        Center(
          child: Container(
            width: 250,
            height: 250,
            decoration: BoxDecoration(
              border: Border.all(
                color: _isProcessing ? AppTheme.success : Colors.white,
                width: 3,
              ),
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
        // Instructions
        Positioned(
          bottom: 100,
          left: 0,
          right: 0,
          child: Text(
            _isProcessing
                ? 'Traitement en cours...'
                : 'Pointez vers un code-barres',
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 16,
              shadows: [
                Shadow(color: Colors.black, blurRadius: 4),
              ],
            ),
          ),
        ),
      ],
    );
  }

  List<SessionItem> get _filteredAndSortedItems {
    // Filter
    var items = _scannedItems.where((item) {
      if (_showCountedOnly) {
        return item.isCounted;
      } else {
        return !item.isCounted;
      }
    }).toList();

    // Sort
    if (_sortBy == 'reference') {
      items.sort((a, b) => a.itemNumber.compareTo(b.itemNumber));
    } else {
      items.sort((a, b) => a.itemName.compareTo(b.itemName));
    }

    return items;
  }

  Widget _buildListView() {
    if (_scannedItems.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.inventory_2_outlined,
              size: 64,
              color: Theme.of(context).hintColor,
            ),
            const SizedBox(height: 16),
            Text(
              'Aucun article',
              style: Theme.of(context).textTheme.titleMedium,
            ),
          ],
        ),
      );
    }

    final filteredItems = _filteredAndSortedItems;
    final countedCount =
        _scannedItems.where((item) => item.isCounted).length;
    final uncountedCount = _scannedItems.length - countedCount;

    return Column(
      children: [
        // Toolbar: sort + filter
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            border: Border(
              bottom: BorderSide(color: AppTheme.lightBorder),
            ),
          ),
          child: Row(
            children: [
              // Sort buttons
              const Icon(Icons.sort, size: 18, color: Colors.grey),
              const SizedBox(width: 4),
              _SortChip(
                label: 'Libellé',
                isActive: _sortBy == 'name',
                onTap: () => setState(() => _sortBy = 'name'),
              ),
              const SizedBox(width: 6),
              _SortChip(
                label: 'Référence',
                isActive: _sortBy == 'reference',
                onTap: () => setState(() => _sortBy = 'reference'),
              ),
              const Spacer(),
              // Counted toggle
              GestureDetector(
                onTap: () =>
                    setState(() => _showCountedOnly = !_showCountedOnly),
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: _showCountedOnly
                        ? AppTheme.success.withOpacity(0.1)
                        : AppTheme.warning.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: _showCountedOnly
                          ? AppTheme.success.withOpacity(0.4)
                          : AppTheme.warning.withOpacity(0.4),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        _showCountedOnly
                            ? Icons.check_circle
                            : Icons.radio_button_unchecked,
                        size: 14,
                        color: _showCountedOnly
                            ? AppTheme.success
                            : AppTheme.warning,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        _showCountedOnly
                            ? 'Comptés ($countedCount)'
                            : 'Non comptés ($uncountedCount)',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: _showCountedOnly
                              ? AppTheme.success
                              : AppTheme.warning,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
        // Items list
        Expanded(
          child: filteredItems.isEmpty
              ? Center(
                  child: Text(
                    _showCountedOnly
                        ? 'Aucun article compté'
                        : 'Tous les articles ont été comptés',
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(8),
                  itemCount: filteredItems.length,
                  itemBuilder: (context, index) {
                    final item = filteredItems[index];
                    return _buildItemTile(item);
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildItemTile(SessionItem item) {
    return Card(
      child: ListTile(
        leading: item.isCounted
            ? const Icon(Icons.check_circle,
                color: AppTheme.success, size: 24)
            : Icon(Icons.radio_button_unchecked,
                color: Colors.grey[400], size: 24),
        title: Text(
          item.itemName,
          style: TextStyle(
            fontSize: 13,
            color: item.isCounted ? Colors.grey[500] : null,
          ),
        ),
        subtitle: Text(item.itemNumber, style: const TextStyle(fontSize: 11)),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            if (item.isCounted) ...[
              Text(
                '${item.countedQuantity.toStringAsFixed(0)}',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              if (item.hasVariance)
                Text(
                  '${item.variance > 0 ? '+' : ''}${item.variance.toStringAsFixed(0)}',
                  style: TextStyle(
                    color: item.isOver ? AppTheme.success : AppTheme.danger,
                    fontSize: 12,
                  ),
                ),
            ] else
              Text(
                'Stock: ${item.expectedQuantity.toStringAsFixed(0)}',
                style: TextStyle(color: Colors.grey[500], fontSize: 13),
              ),
          ],
        ),
      ),
    );
  }
}

class _SortChip extends StatelessWidget {
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const _SortChip({
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: isActive
              ? AppTheme.primaryBlue.withOpacity(0.1)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isActive
                ? AppTheme.primaryBlue.withOpacity(0.4)
                : Colors.grey[300]!,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
            color: isActive ? AppTheme.primaryBlue : Colors.grey[600],
          ),
        ),
      ),
    );
  }
}

class _ManualSearchSheet extends StatefulWidget {
  final int sessionId;
  final Function(Item) onItemSelected;

  const _ManualSearchSheet({
    required this.sessionId,
    required this.onItemSelected,
  });

  @override
  State<_ManualSearchSheet> createState() => _ManualSearchSheetState();
}

class _ManualSearchSheetState extends State<_ManualSearchSheet> {
  final TextEditingController _controller = TextEditingController();
  final FocusNode _searchFocus = FocusNode();
  List<Item> _results = [];
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    // iOS Safari: autofocus doesn't trigger keyboard, try requestFocus after render
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Future.delayed(const Duration(milliseconds: 400), () {
        if (mounted) _searchFocus.requestFocus();
      });
    });
  }

  @override
  void dispose() {
    _searchFocus.dispose();
    _controller.dispose();
    super.dispose();
  }

  Future<void> _search(String query) async {
    if (query.length < 2) {
      setState(() {
        _results = [];
      });
      return;
    }

    setState(() {
      _isSearching = true;
    });

    try {
      final apiService = context.read<ApiService>();
      final response = await apiService.getItems(
        search: query,
        sessionId: widget.sessionId,
        limit: 20,
      );
      final items = (response['items'] as List)
          .map((json) => Item.fromJson(json as Map<String, dynamic>))
          .toList();
      setState(() {
        _results = items;
        _isSearching = false;
      });
    } catch (e) {
      // Search offline
      final databaseService = context.read<DatabaseService>();
      final cached = await databaseService.searchItems(query);
      setState(() {
        _results = cached.map((json) => Item.fromJson(json)).toList();
        _isSearching = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.9,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      expand: false,
      builder: (context, scrollController) => Column(
        children: [
          // Handle
          Container(
            margin: const EdgeInsets.only(top: 8),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          // Search field
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _controller,
              focusNode: _searchFocus,
              autofocus: true,
              decoration: InputDecoration(
                hintText: 'Rechercher par nom ou code...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _controller.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _controller.clear();
                          setState(() {
                            _results = [];
                          });
                        },
                      )
                    : null,
              ),
              onChanged: _search,
            ),
          ),
          // Results
          Expanded(
            child: _isSearching
                ? const Center(child: CircularProgressIndicator())
                : _results.isEmpty
                    ? Center(
                        child: Text(
                          _controller.text.isEmpty
                              ? 'Commencez à taper pour rechercher'
                              : 'Aucun résultat',
                          style: Theme.of(context).textTheme.bodyMedium,
                        ),
                      )
                    : ListView.builder(
                        controller: scrollController,
                        itemCount: _results.length,
                        itemBuilder: (context, index) {
                          final item = _results[index];
                          final isCounted = item.counted == true;
                          return ListTile(
                            leading: isCounted
                                ? const Icon(Icons.check_circle,
                                    color: AppTheme.success, size: 28)
                                : Icon(Icons.radio_button_unchecked,
                                    color: Colors.grey[400], size: 28),
                            title: Text(
                              item.name,
                              style: TextStyle(
                                color: isCounted ? Colors.grey[500] : null,
                                decoration: isCounted
                                    ? TextDecoration.lineThrough
                                    : null,
                              ),
                            ),
                            subtitle: Row(
                              children: [
                                Text(item.itemNumber),
                                if (isCounted) ...[
                                  const SizedBox(width: 8),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 6, vertical: 1),
                                    decoration: BoxDecoration(
                                      color: AppTheme.success.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      'Compté: ${item.countedQuantity?.toStringAsFixed(0)}',
                                      style: const TextStyle(
                                        color: AppTheme.success,
                                        fontSize: 11,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                            trailing: Text(
                              'Stock: ${item.quantity.toStringAsFixed(0)}',
                              style: Theme.of(context)
                                  .textTheme
                                  .bodyMedium
                                  ?.copyWith(
                                    color:
                                        isCounted ? Colors.grey[400] : null,
                                  ),
                            ),
                            onTap: () => widget.onItemSelected(item),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
