import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'config/api_config.dart';
import 'config/app_theme.dart';
import 'services/api_service.dart';
import 'services/auth_service.dart';
import 'services/database_service.dart';
import 'services/server_config_service.dart';
import 'services/sync_service.dart';
import 'blocs/auth/auth_bloc.dart';
import 'screens/splash_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize services
  final databaseService = DatabaseService();
  await databaseService.initialize();

  // Load server URL from persistent storage
  await ServerConfigService.init();
  final serverUrl = ServerConfigService.getServerUrl();
  if (serverUrl != null && serverUrl.isNotEmpty) {
    ApiConfig.setBaseUrl(serverUrl);
  }

  final apiService = ApiService(baseUrl: ApiConfig.baseUrl);
  final authService = AuthService(apiService: apiService);
  final syncService = SyncService(
    apiService: apiService,
    databaseService: databaseService,
  );

  runApp(
    WMInventoryApp(
      apiService: apiService,
      authService: authService,
      databaseService: databaseService,
      syncService: syncService,
    ),
  );
}

class WMInventoryApp extends StatelessWidget {
  final ApiService apiService;
  final AuthService authService;
  final DatabaseService databaseService;
  final SyncService syncService;

  const WMInventoryApp({
    super.key,
    required this.apiService,
    required this.authService,
    required this.databaseService,
    required this.syncService,
  });

  @override
  Widget build(BuildContext context) {
    return MultiRepositoryProvider(
      providers: [
        RepositoryProvider.value(value: apiService),
        RepositoryProvider.value(value: authService),
        RepositoryProvider.value(value: databaseService),
        RepositoryProvider.value(value: syncService),
      ],
      child: MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => AuthBloc(authService: authService)
              ..add(AuthCheckRequested()),
          ),
        ],
        child: MaterialApp(
          title: 'W&M Inventaire',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          darkTheme: AppTheme.darkTheme,
          themeMode: ThemeMode.system,
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          supportedLocales: const [
            Locale('fr', 'FR'),
            Locale('en', 'US'),
          ],
          locale: const Locale('fr', 'FR'),
          home: const SplashScreen(),
        ),
      ),
    );
  }
}
