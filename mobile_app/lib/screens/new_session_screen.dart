import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../config/app_theme.dart';
import '../models/category.dart';
import '../models/inventory_session.dart';
import '../services/api_service.dart';
import 'scanner_screen.dart';

class NewSessionScreen extends StatefulWidget {
  final SessionType sessionType;

  const NewSessionScreen({
    super.key,
    required this.sessionType,
  });

  @override
  State<NewSessionScreen> createState() => _NewSessionScreenState();
}

class _NewSessionScreenState extends State<NewSessionScreen> {
  List<Category> _categories = [];
  Category? _selectedCategory;
  int _daysThreshold = 30;
  bool _isLoading = false;
  bool _isCreating = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    if (widget.sessionType == SessionType.rollingCategory) {
      _loadCategories();
    }
  }

  Future<void> _loadCategories() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final apiService = context.read<ApiService>();
      final categoriesJson = await apiService.getCategories();
      setState(() {
        _categories = categoriesJson
            .map((json) => Category.fromJson(json as Map<String, dynamic>))
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Impossible de charger les catégories';
        _isLoading = false;
      });
    }
  }

  Future<void> _createSession() async {
    if (widget.sessionType == SessionType.rollingCategory &&
        _selectedCategory == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Veuillez sélectionner une catégorie'),
          backgroundColor: AppTheme.warning,
        ),
      );
      return;
    }

    setState(() {
      _isCreating = true;
    });

    try {
      final apiService = context.read<ApiService>();
      final sessionJson = await apiService.createSession(
        type: widget.sessionType.apiValue,
        categoryId: _selectedCategory?.id,
        daysThreshold:
            widget.sessionType == SessionType.rollingDate ? _daysThreshold : null,
      );

      final session = InventorySession.fromJson(sessionJson);

      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => ScannerScreen(session: session),
          ),
        );
      }
    } catch (e) {
      setState(() {
        _isCreating = false;
      });
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.sessionType.displayName),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Session type info
              _buildSessionTypeInfo(),
              const SizedBox(height: 24),

              // Options based on session type
              if (widget.sessionType == SessionType.rollingCategory)
                _buildCategorySelector(),

              if (widget.sessionType == SessionType.rollingDate)
                _buildDaysSelector(),

              const Spacer(),

              // Start button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _isCreating ? null : _createSession,
                  icon: _isCreating
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor:
                                AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        )
                      : const Icon(Icons.play_arrow),
                  label: Text(_isCreating ? 'Création...' : 'Démarrer l\'inventaire'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSessionTypeInfo() {
    IconData icon;
    String description;
    Color color;

    switch (widget.sessionType) {
      case SessionType.full:
        icon = Icons.inventory;
        description =
            'Vous allez compter tous les articles de votre stock. Cette opération peut prendre du temps.';
        color = AppTheme.primaryBlue;
        break;
      case SessionType.rollingCategory:
        icon = Icons.category;
        description =
            'Sélectionnez une catégorie pour effectuer un inventaire partiel.';
        color = AppTheme.success;
        break;
      case SessionType.rollingDate:
        icon = Icons.calendar_today;
        description =
            'Comptez les articles qui n\'ont pas été vérifiés depuis un certain nombre de jours.';
        color = AppTheme.warning;
        break;
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Text(
                description,
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCategorySelector() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Text(_error!, style: const TextStyle(color: AppTheme.danger)),
              const SizedBox(height: 8),
              TextButton.icon(
                onPressed: _loadCategories,
                icon: const Icon(Icons.refresh),
                label: const Text('Réessayer'),
              ),
            ],
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Sélectionner une catégorie',
          style: Theme.of(context).textTheme.titleMedium,
        ),
        const SizedBox(height: 12),
        Container(
          height: 300,
          child: ListView.builder(
            itemCount: _categories.length,
            itemBuilder: (context, index) {
              final category = _categories[index];
              final isSelected = _selectedCategory?.id == category.id;
              return Card(
                color: isSelected
                    ? AppTheme.primaryBlue.withOpacity(0.1)
                    : null,
                child: ListTile(
                  title: Text(category.name),
                  trailing: isSelected
                      ? const Icon(Icons.check, color: AppTheme.primaryBlue)
                      : null,
                  onTap: () {
                    setState(() {
                      _selectedCategory = category;
                    });
                  },
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildDaysSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Période sans vérification',
          style: Theme.of(context).textTheme.titleMedium,
        ),
        const SizedBox(height: 12),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Jours'),
                    Text(
                      '$_daysThreshold jours',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            color: AppTheme.primaryBlue,
                          ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Slider(
                  value: _daysThreshold.toDouble(),
                  min: 7,
                  max: 90,
                  divisions: 11,
                  label: '$_daysThreshold jours',
                  onChanged: (value) {
                    setState(() {
                      _daysThreshold = value.round();
                    });
                  },
                ),
                Text(
                  'Articles non vérifiés depuis plus de $_daysThreshold jours',
                  style: Theme.of(context).textTheme.bodySmall,
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
