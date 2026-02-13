import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../config/app_theme.dart';
import '../models/inventory_session.dart';
import '../services/api_service.dart';
import 'scanner_screen.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  final ApiService _apiService = ApiService(baseUrl: ApiConfig.baseUrl);
  List<dynamic> _sessions = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _initAndLoad();
  }

  Future<void> _initAndLoad() async {
    // Get token from shared preferences
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('wm_token');
    if (token != null) {
      _apiService.setToken(token);
    }
    await _loadSessions();
  }

  Future<void> _loadSessions() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final sessions = await _apiService.getSessions();
      setState(() {
        _sessions = sessions;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Erreur de chargement: $e';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadSessions,
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: AppTheme.danger),
            const SizedBox(height: 16),
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadSessions,
              child: const Text('Réessayer'),
            ),
          ],
        ),
      );
    }

    if (_sessions.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.history, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'Aucune session d\'inventaire',
              style: TextStyle(color: Colors.grey[600], fontSize: 16),
            ),
            const SizedBox(height: 8),
            Text(
              'Créez votre première session depuis l\'accueil',
              style: TextStyle(color: Colors.grey[500], fontSize: 14),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadSessions,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _sessions.length,
        itemBuilder: (context, index) {
          final session = _sessions[index];
          return _SessionCard(
            session: session,
            onTap: () => _openSession(session),
          );
        },
      ),
    );
  }

  void _openSession(dynamic session) {
    final status = session['status'] as String?;

    if (status == 'in_progress') {
      // Continue session - create InventorySession object
      final inventorySession = InventorySession(
        id: session['id'] as int,
        type: _getSessionType(session['type'] as String?),
        categoryId: session['category_id'] as int?,
        categoryName: session['category_name'] as String?,
        status: SessionStatus.inProgress,
        totalItems: session['total_items'] as int? ?? 0,
        itemsCounted: session['items_counted'] as int? ?? 0,
        startedAt: DateTime.tryParse(session['started_at'] ?? '') ?? DateTime.now(),
      );

      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => ScannerScreen(session: inventorySession),
        ),
      );
    } else {
      // Show session details
      _showSessionDetails(session);
    }
  }

  SessionType _getSessionType(String? type) {
    switch (type) {
      case 'full':
        return SessionType.full;
      case 'rolling_category':
        return SessionType.rollingCategory;
      case 'rolling_date':
        return SessionType.rollingDate;
      default:
        return SessionType.full;
    }
  }

  void _showSessionDetails(dynamic session) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Session #${session['id']}',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 16),
            _DetailRow('Type', _getSessionTypeName(session['type'])),
            _DetailRow('Statut', _getStatusName(session['status'])),
            _DetailRow('Articles comptés', '${session['items_counted']} / ${session['total_items']}'),
            _DetailRow('Démarré le', _formatDate(session['started_at'])),
            if (session['completed_at'] != null)
              _DetailRow('Terminé le', _formatDate(session['completed_at'])),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  String _getSessionTypeName(String? type) {
    switch (type) {
      case 'full':
        return 'Inventaire complet';
      case 'rolling_category':
        return 'Par catégorie';
      case 'rolling_date':
        return 'Par date';
      default:
        return type ?? 'Inconnu';
    }
  }

  String _getStatusName(String? status) {
    switch (status) {
      case 'in_progress':
        return 'En cours';
      case 'completed':
        return 'Terminé';
      case 'cancelled':
        return 'Annulé';
      default:
        return status ?? 'Inconnu';
    }
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return '-';
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year} ${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return dateStr;
    }
  }
}

class _SessionCard extends StatelessWidget {
  final dynamic session;
  final VoidCallback onTap;

  const _SessionCard({required this.session, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final status = session['status'] as String?;
    final isInProgress = status == 'in_progress';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: _getStatusColor(status).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  _getTypeIcon(session['type']),
                  color: _getStatusColor(status),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _getTypeName(session['type']),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${session['items_counted']} / ${session['total_items']} articles',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                    Text(
                      _formatDate(session['started_at']),
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey[500],
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                children: [
                  _StatusBadge(status: status),
                  if (isInProgress) ...[
                    const SizedBox(height: 8),
                    const Icon(Icons.chevron_right),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _getTypeIcon(String? type) {
    switch (type) {
      case 'full':
        return Icons.inventory;
      case 'rolling_category':
        return Icons.category;
      case 'rolling_date':
        return Icons.calendar_today;
      default:
        return Icons.inventory;
    }
  }

  String _getTypeName(String? type) {
    switch (type) {
      case 'full':
        return 'Inventaire complet';
      case 'rolling_category':
        return 'Par catégorie';
      case 'rolling_date':
        return 'Par date';
      default:
        return 'Inventaire';
    }
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'in_progress':
        return AppTheme.warning;
      case 'completed':
        return AppTheme.success;
      case 'cancelled':
        return AppTheme.danger;
      default:
        return Colors.grey;
    }
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return '';
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
    } catch (e) {
      return dateStr;
    }
  }
}

class _StatusBadge extends StatelessWidget {
  final String? status;

  const _StatusBadge({this.status});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: _getColor().withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        _getText(),
        style: TextStyle(
          color: _getColor(),
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }

  Color _getColor() {
    switch (status) {
      case 'in_progress':
        return AppTheme.warning;
      case 'completed':
        return AppTheme.success;
      case 'cancelled':
        return AppTheme.danger;
      default:
        return Colors.grey;
    }
  }

  String _getText() {
    switch (status) {
      case 'in_progress':
        return 'En cours';
      case 'completed':
        return 'Terminé';
      case 'cancelled':
        return 'Annulé';
      default:
        return status ?? 'Inconnu';
    }
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey[600])),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}
