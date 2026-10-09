import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'core/network/api_client.dart';
import 'core/scanner/scanner_event_bus.dart';
import 'core/storage/app_storage.dart';
import 'core/theme/app_theme.dart';
import 'providers/auth_controller.dart';
import 'providers/server_config_controller.dart';
import 'screens/login_screen.dart';
import 'screens/tablet_shell_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Force Landscape Orientation for Shop Floor Tablets
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.landscapeLeft,
    DeviceOrientation.landscapeRight,
  ]);

  // Initialize Storage & Core Services
  final storage = await AppStorage.init();
  final apiClient = ApiClient(storage);
  final serverController = ServerConfigController(storage: storage, apiClient: apiClient);
  final authController = AuthController(storage: storage, apiClient: apiClient);

  // Initialize Global Scanner Bus
  ScannerEventBus.instance;

  // Background ping test for server
  serverController.testConnection();

  // Check auth session
  await authController.checkAuthStatus();

  runApp(MirsApp(
    storage: storage,
    apiClient: apiClient,
    serverController: serverController,
    authController: authController,
  ));
}

class MirsApp extends StatelessWidget {
  final AppStorage storage;
  final ApiClient apiClient;
  final ServerConfigController serverController;
  final AuthController authController;

  const MirsApp({
    super.key,
    required this.storage,
    required this.apiClient,
    required this.serverController,
    required this.authController,
  });

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'MIRS Tablet',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      home: AnimatedBuilder(
        animation: authController,
        builder: (context, _) {
          if (authController.isAuthenticated) {
            return TabletShellScreen(
              authController: authController,
              configController: serverController,
            );
          } else {
            return LoginScreen(
              authController: authController,
              configController: serverController,
              onLoginSuccess: () {},
            );
          }
        },
      ),
    );
  }
}
