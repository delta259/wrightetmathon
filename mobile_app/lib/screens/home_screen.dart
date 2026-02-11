import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../blocs/auth/auth_bloc.dart';
import '../config/app_theme.dart';
import '../models/inventory_session.dart';
import '../services/api_service.dart';
import 'login_screen.dart';
import 'scanner_screen.dart';
import 'history_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  InventorySession? _activeSession;
  Map<String, dynamic>? _summary;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadActiveSession();
  }

  Future<void> _loadActiveSession() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final apiService = context.read<ApiService>();
      final response = await apiService.getActiveSession();

      if (response != null) {
        setState(() {
          _activeSession = InventorySession.fromJson(response);
          _isLoading = false;
        });
        // Load summary separately
        try {
          final details = await apiService.getSession(_activeSession!.id);
          setState(() {
            _summary = details['summary'] as Map<String, dynamic>?;
          });
        } catch (_) {}
      } else {
        setState(() {
          _activeSession = null;
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _error = 'Impossible de contacter le serveur';
      });
    }
  }

  void _openScanner() {
    if (_activeSession == null) return;
    Navigator.of(context)
        .push(
      MaterialPageRoute(
        builder: (_) => ScannerScreen(session: _activeSession!),
      ),
    )
        .then((_) {
      // Reload on return from scanner
      _loadActiveSession();
    });
  }

  void _openHistory() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const HistoryScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthUnauthenticated) {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => const LoginScreen()),
          );
        }
      },
      child: Scaffold(
        appBar: AppBar(
          title: const Text('W&M Inventaire'),
          actions: [
            IconButton(
              icon: const Icon(Icons.refresh),
              onPressed: _loadActiveSession,
            ),
            IconButton(
              icon: const Icon(Icons.logout),
              onPressed: () => _showLogoutDialog(context),
            ),
          ],
        ),
        body: SafeArea(
          child: RefreshIndicator(
            onRefresh: _loadActiveSession,
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                _buildWelcomeCard(context),
                const SizedBox(height: 24),
                _buildActiveSessionCard(context),
                const SizedBox(height: 24),
                _buildQuickActions(context),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildWelcomeCard(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, state) {
        if (state is AuthAuthenticated) {
          return Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      color: AppTheme.primaryBlue.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.person,
                      color: AppTheme.primaryBlue,
                      size: 28,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Bonjour, ${state.user.firstName}',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        Text(
                          state.user.branchName,
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        }
        return const SizedBox.shrink();
      },
    );
  }

  Widget _buildActiveSessionCard(BuildContext context) {
    if (_isLoading) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            children: [
              const CircularProgressIndicator(),
              const SizedBox(height: 16),
              Text(
                'Chargement de la session...',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ],
          ),
        ),
      );
    }

    if (_error != null) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              const Icon(Icons.cloud_off, size: 48, color: AppTheme.danger),
              const SizedBox(height: 12),
              Text(_error!, style: Theme.of(context).textTheme.bodyMedium),
              const SizedBox(height: 12),
              OutlinedButton.icon(
                onPressed: _loadActiveSession,
                icon: const Icon(Icons.refresh),
                label: const Text('Réessayer'),
              ),
            ],
          ),
        ),
      );
    }

    if (_activeSession == null) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: Colors.grey.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(
                  Icons.inventory_2_outlined,
                  size: 32,
                  color: Colors.grey[400],
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Aucune session en cours',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              Text(
                'Créez une session depuis le site Wright et Mathon pour commencer le comptage.',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
          ),
        ),
      );
    }

    // Session active
    final session = _activeSession!;
    final progress = session.totalItems > 0
        ? session.itemsCounted / session.totalItems
        : 0.0;
    final progressPct = (progress * 100).toStringAsFixed(0);

    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header with status
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: AppTheme.primaryBlue.withOpacity(0.1),
            ),
            child: Row(
              children: [
                Container(
                  width: 10,
                  height: 10,
                  decoration: const BoxDecoration(
                    color: AppTheme.success,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  'Session en cours',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: AppTheme.primaryBlue,
                        fontWeight: FontWeight.bold,
                      ),
                ),
                const Spacer(),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.success.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'En cours',
                    style: TextStyle(
                      color: AppTheme.success,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Session type & category
                Row(
                  children: [
                    Icon(
                      _getSessionIcon(session.type),
                      color: AppTheme.primaryBlue,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      session.type.displayName,
                      style: Theme.of(context).textTheme.titleSmall,
                    ),
                    if (session.categoryName != null) ...[
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppTheme.secondary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          session.categoryName!,
                          style: TextStyle(
                            color: AppTheme.secondary,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 16),

                // Progress bar
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '${session.itemsCounted} / ${session.totalItems} articles',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    Text(
                      '$progressPct%',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            color: AppTheme.primaryBlue,
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                  ],
                ),
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
                const SizedBox(height: 16),

                // Summary stats (if loaded)
                if (_summary != null) ...[
                  Row(
                    children: [
                      _StatChip(
                        label: 'Ecarts',
                        value: '${_summary!['items_with_variance'] ?? 0}',
                        color: AppTheme.warning,
                      ),
                      const SizedBox(width: 8),
                      _StatChip(
                        label: 'Surplus',
                        value: '${_summary!['items_over'] ?? 0}',
                        color: AppTheme.success,
                      ),
                      const SizedBox(width: 8),
                      _StatChip(
                        label: 'Manquants',
                        value: '${_summary!['items_under'] ?? 0}',
                        color: AppTheme.danger,
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                ],

                // Date info
                Row(
                  children: [
                    Icon(Icons.access_time, size: 14, color: Colors.grey[500]),
                    const SizedBox(width: 4),
                    Text(
                      'Démarré le ${_formatDate(session.startedAt)}',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
                if (session.notes != null && session.notes!.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(Icons.note, size: 14, color: Colors.grey[500]),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          session.notes!,
                          style: Theme.of(context).textTheme.bodySmall,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: 16),

                // Action button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _openScanner,
                    icon: const Icon(Icons.qr_code_scanner),
                    label: const Text('Ouvrir le scanner'),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _QuickActionCard(
            icon: Icons.qr_code_scanner,
            label: 'Scanner',
            color: AppTheme.primaryBlue,
            onTap: () {
              if (_activeSession != null) {
                _openScanner();
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Aucune session en cours'),
                  ),
                );
              }
            },
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _QuickActionCard(
            icon: Icons.history,
            label: 'Historique',
            color: AppTheme.secondary,
            onTap: _openHistory,
          ),
        ),
      ],
    );
  }

  IconData _getSessionIcon(SessionType type) {
    switch (type) {
      case SessionType.full:
        return Icons.inventory;
      case SessionType.partial:
        return Icons.filter_list;
      case SessionType.rolling:
        return Icons.autorenew;
      case SessionType.rollingCategory:
        return Icons.category;
      case SessionType.rollingDate:
        return Icons.calendar_today;
    }
  }

  String _formatDate(DateTime date) {
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year} '
        '${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
  }

  void _showLogoutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text('Voulez-vous vraiment vous déconnecter ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              context.read<AuthBloc>().add(AuthLogoutRequested());
            },
            child: const Text('Déconnexion'),
          ),
        ],
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  final String label;
  final String value;
  final Color color;

  const _StatChip({
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                color: color.withOpacity(0.8),
                fontSize: 11,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _QuickActionCard({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16),
          child: Column(
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
              const SizedBox(height: 8),
              Text(
                label,
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
