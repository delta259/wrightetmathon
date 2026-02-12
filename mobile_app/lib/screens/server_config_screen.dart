import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../config/api_config.dart';
import '../config/app_theme.dart';
import '../services/server_config_service.dart';

/// Screen for configuring the server URL.
/// Shown on first launch or accessible from login screen settings.
class ServerConfigScreen extends StatefulWidget {
  /// If true, navigates to login after save. If false, just pops back.
  final bool isInitialSetup;

  const ServerConfigScreen({super.key, this.isInitialSetup = false});

  @override
  State<ServerConfigScreen> createState() => _ServerConfigScreenState();
}

class _ServerConfigScreenState extends State<ServerConfigScreen> {
  final _ipController = TextEditingController();
  bool _isTesting = false;
  bool? _testSuccess;
  String? _testMessage;

  @override
  void initState() {
    super.initState();
    final savedIp = ServerConfigService.getServerIp();
    if (savedIp != null) {
      _ipController.text = savedIp;
    }
  }

  @override
  void dispose() {
    _ipController.dispose();
    super.dispose();
  }

  /// Build the full URL from the IP input
  String _buildUrl(String ip) {
    String normalized = ip.trim();
    normalized = normalized.replaceFirst(RegExp(r'^https?://'), '');
    normalized = normalized.split('/').first;
    return 'http://$normalized/wrightetmathon/index.php';
  }

  Future<void> _testConnection() async {
    final ip = _ipController.text.trim();
    if (ip.isEmpty) {
      setState(() {
        _testSuccess = false;
        _testMessage = 'Veuillez saisir une adresse IP';
      });
      return;
    }

    setState(() {
      _isTesting = true;
      _testSuccess = null;
      _testMessage = null;
    });

    try {
      final dio = Dio();
      dio.options.connectTimeout = const Duration(seconds: 10);
      dio.options.receiveTimeout = const Duration(seconds: 10);

      final testUrl = _buildUrl(ip);
      final response = await dio.get('$testUrl${ApiConfig.ping}');
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data is Map
            ? response.data
            : {};
        if (data['status'] == 'ok') {
          setState(() {
            _testSuccess = true;
            _testMessage = 'Connexion OK - ${data['version'] ?? ''}';
          });
        } else {
          setState(() {
            _testSuccess = false;
            _testMessage = 'Réponse inattendue du serveur';
          });
        }
      } else {
        setState(() {
          _testSuccess = false;
          _testMessage = 'Erreur HTTP ${response.statusCode}';
        });
      }
    } on DioException catch (e) {
      String msg;
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout) {
        msg = 'Délai de connexion dépassé';
      } else if (e.type == DioExceptionType.connectionError) {
        msg = 'Impossible de se connecter au serveur';
      } else {
        msg = 'Erreur : ${e.message ?? e.type.name}';
      }
      setState(() {
        _testSuccess = false;
        _testMessage = msg;
      });
    } catch (e) {
      setState(() {
        _testSuccess = false;
        _testMessage = 'Erreur : $e';
      });
    } finally {
      setState(() {
        _isTesting = false;
      });
    }
  }

  Future<void> _save() async {
    final ip = _ipController.text.trim();
    if (ip.isEmpty || _testSuccess != true) return;

    await ServerConfigService.saveServerIp(ip);
    ApiConfig.setBaseUrl(ServerConfigService.getServerUrl()!);

    if (!mounted) return;

    if (widget.isInitialSetup) {
      // Go to login - the app will be restarted via main
      Navigator.of(context).pushNamedAndRemoveUntil('/', (_) => false);
    } else {
      Navigator.of(context).pop(true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              AppTheme.primaryBlue,
              AppTheme.secondary,
            ],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Card(
                elevation: 8,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Icon
                      Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          color: AppTheme.primaryBlue.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(
                          Icons.dns_outlined,
                          size: 40,
                          color: AppTheme.primaryBlue,
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Title
                      Text(
                        'Configuration Serveur',
                        style: Theme.of(context)
                            .textTheme
                            .headlineMedium
                            ?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Saisissez l\'adresse IP de votre serveur POS',
                        style: Theme.of(context).textTheme.bodyMedium,
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 32),

                      // IP field
                      TextField(
                        controller: _ipController,
                        decoration: const InputDecoration(
                          labelText: 'Adresse IP du serveur',
                          hintText: '192.168.1.x',
                          prefixIcon: Icon(Icons.router_outlined),
                        ),
                        keyboardType: TextInputType.url,
                        autocorrect: false,
                        onChanged: (_) {
                          if (_testSuccess != null) {
                            setState(() {
                              _testSuccess = null;
                              _testMessage = null;
                            });
                          }
                        },
                      ),
                      const SizedBox(height: 16),

                      // Test result indicator
                      if (_testMessage != null)
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 12),
                          decoration: BoxDecoration(
                            color: _testSuccess == true
                                ? AppTheme.success.withOpacity(0.1)
                                : AppTheme.danger.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: _testSuccess == true
                                  ? AppTheme.success
                                  : AppTheme.danger,
                            ),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                _testSuccess == true
                                    ? Icons.check_circle
                                    : Icons.error,
                                color: _testSuccess == true
                                    ? AppTheme.success
                                    : AppTheme.danger,
                                size: 20,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  _testMessage!,
                                  style: TextStyle(
                                    color: _testSuccess == true
                                        ? AppTheme.success
                                        : AppTheme.danger,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      const SizedBox(height: 24),

                      // Test button
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: _isTesting ? null : _testConnection,
                          icon: _isTesting
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                      strokeWidth: 2),
                                )
                              : const Icon(Icons.wifi_find),
                          label: Text(
                              _isTesting ? 'Test en cours...' : 'Tester la connexion'),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Save button
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: _testSuccess == true ? _save : null,
                          icon: const Icon(Icons.save),
                          label: const Text('Enregistrer'),
                        ),
                      ),

                      // Back link if not initial setup
                      if (!widget.isInitialSetup) ...[
                        const SizedBox(height: 16),
                        TextButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text('Annuler'),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
